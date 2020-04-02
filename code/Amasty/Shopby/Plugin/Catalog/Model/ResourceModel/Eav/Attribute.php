<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Shopby
 */


namespace Amasty\Shopby\Plugin\Catalog\Model\ResourceModel\Eav;

use Amasty\ShopbyBase\Model\Cache\Type;
use Amasty\ShopbyBase\Api\Data\FilterSettingRepositoryInterface;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute as EavAttributeResource;
use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Class Attribute
 * @package Amasty\Shopby\Plugin\Catalog\Model\ResourceModel\Eav
 */
class Attribute
{
    /**
     * @var FilterSettingRepositoryInterface
     */
    protected $filterSettingRepository;

    /**
     * @var \Amasty\ShopbyBase\Model\FilterSettingFactory
     */
    protected $filterSettingFactory;

    /**
     * @var \Magento\Config\Model\Config\Factory
     */
    protected $configFactory;

    /**
     * @var \Amasty\Shopby\Helper\FilterSetting
     */
    protected $filterSettingHelper;

    /**
     * @var \Amasty\Base\Model\Serializer
     */
    private $serializer;

    /**
     * @var  TypeListInterface
     */
    private $cacheTypeList;

    public function __construct(
        FilterSettingRepositoryInterface $filterSettingRepository,
        \Amasty\ShopbyBase\Model\FilterSettingFactory $filterSettingFactory,
        \Magento\Config\Model\Config\Factory $configFactory,
        \Amasty\Shopby\Helper\FilterSetting $filterSettingHelper,
        \Amasty\Base\Model\Serializer $serializer,
        TypeListInterface $typeList
    ) {
        $this->filterSettingRepository = $filterSettingRepository;
        $this->filterSettingFactory = $filterSettingFactory;
        $this->configFactory = $configFactory;
        $this->filterSettingHelper = $filterSettingHelper;
        $this->serializer = $serializer;
        $this->cacheTypeList = $typeList;
    }

    /**
     * @param array $data
     * @return array
     */
    protected function prepareData(array $data)
    {
        $multipleData = ['categories_filter', 'attributes_filter', 'attributes_options_filter'];

        foreach ($multipleData as $multiple) {
            if (array_key_exists($multiple, $data) && is_array($data[$multiple])) {
                $data[$multiple] = implode(',', array_filter($data[$multiple], [$this, 'callbackNotEmpty']));
            } elseif (!array_key_exists($multiple, $data)) {
                $data[$multiple] = '';
            }
        }

        $sliderRange = ['slider_min', 'slider_max'];

        foreach ($sliderRange as $slider) {
            if (!isset($data[$slider]) || $data[$slider] === '') {
                $data[$slider] = null;
            }
        }

        return $data;
    }

    /**
     * @param $element
     * @return bool
     */
    protected function callbackNotEmpty($element)
    {
        return $element !== '';
    }

    /**
     * @param EavAttributeResource $subject
     * @param \Closure $proceed
     * @return mixed
     * @throws \Exception
     */
    public function aroundSave(EavAttributeResource $subject, \Closure $proceed)
    {
        if (!$subject->hasData('filter_code')) {
            return $proceed();
        }

        $filterCode = \Amasty\Shopby\Helper\FilterSetting::ATTR_PREFIX . $subject->getAttributeCode();
        try {
            $filterSetting = $this->filterSettingRepository->get($filterCode, 'filter_code');
        } catch (NoSuchEntityException $e) {
            $filterSetting = $this->filterSettingFactory->create();
        }

        $data = $this->prepareData($subject->getData());
        $data['tooltip'] = $this->serializer->serialize($data['tooltip']);
        //in the case of a conflict when column 'tooltip' exists in catalog_eav_attribute
        $subject->setData('tooltip', null);
        $filterSetting->addData($data);
        $currentFilterCode = $filterSetting->getFilterCode();
        if (empty($currentFilterCode)) {
            $filterSetting->setFilterCode($filterCode);
        }

        $connection = $filterSetting->getResource()->getConnection();
        try {
            $connection->beginTransaction();
            $this->filterSettingRepository->save($filterSetting);

            foreach ($this->filterSettingHelper->getKeyValueForCategoryFilterConfig() as $dataKey => $configPath) {
                if ($subject->getData($dataKey) !== null) {
                    $configModel = $this->configFactory->create();
                    $configModel->setDataByPath($configPath, $subject->getData($dataKey));
                    $configModel->save();
                }
            }
            $result = $proceed();
            $connection->commit();
            $this->cacheTypeList->invalidate(Type::TYPE_IDENTIFIER);
        } catch (\Exception $e) {
            $connection->rollBack();
            throw $e;
        }

        return $result;
    }
}
