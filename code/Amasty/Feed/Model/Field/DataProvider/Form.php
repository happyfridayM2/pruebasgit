<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */


namespace Amasty\Feed\Model\Field\DataProvider;

use Amasty\Feed\Model\Config\Source\CustomFieldType;
use Amasty\Feed\Model\Field\ResourceModel\CollectionFactory;
use Magento\Framework\App\Request\DataPersistorInterface;
use Amasty\Feed\Block\Adminhtml\Field\Edit\Conditions;
use Amasty\Feed\Model\Field\ResourceModel\ConditionCollectionFactory as ConditionCollectionFactory;

class Form extends \Magento\Ui\DataProvider\AbstractDataProvider
{
    /**
     * @var DataPersistorInterface
     */
    private $dataPersistor;

    /**
     * @var \Amasty\Feed\Model\Field\ResourceModel\ConditionCollection
     */
    private $conditions;

    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $collectionFactory,
        DataPersistorInterface $dataPersistor,
        ConditionCollectionFactory $conditionCollFactory,
        array $meta = [],
        array $data = []
    ) {
        $this->dataPersistor = $dataPersistor;
        $this->collection = $collectionFactory->create();
        $this->conditions = $conditionCollFactory->create();

        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
    }

    /**
     * @return array
     */
    public function getData()
    {
        $result = [];
        $items = parent::getData()['items'];

        foreach ($items as $item) {
            if ($item) {
                $defaultResult = $this->conditions->addFieldToFilter('feed_field_id', $item[$this->primaryFieldName])
                    ->getLastItem()
                    ->getFieldResult();

                $item['default[result][modify]'] = '';
                $item['default[result][attribute]'] = '';

                if (isset($defaultResult['modify'])) {
                    $item['default[result][modify]'] = $defaultResult['modify'];
                }

                if (isset($defaultResult['attribute'])) {
                    $item['default[result][attribute]']= $defaultResult['attribute'];
                }

                if ($item['default[result][attribute]'] === '') {
                    $item['default[result][custom_text]'] = $item['default[result][modify]'];
                    $item['default[result][modify]'] = '';
                    $item['default[result][entity_type]'] = CustomFieldType::CUSTOM_TEXT;
                } else {
                    $item['default[result][entity_type]'] = CustomFieldType::ATTRIBUTE;
                }

                $result[$item[$this->primaryFieldName]] = $item;
            }
        }
        $this->restoreUnsavedData($result);

        return $result;
    }

    /**
     * Try to get unsaved data if error was occurred.
     *
     * @param array $result
     */
    private function restoreUnsavedData(&$result)
    {
        $tempData = $this->dataPersistor->get(Conditions::FORM_NAMESPACE);

        if ($tempData) {
            /** @var \Amasty\Feed\Model\Field $tempModel */
            $tempModel = $this->collection->getNewEmptyItem();

            $tempData['default[result][modify]'] = '';
            $tempData['default[result][attribute]'] = '';

            if (isset($tempData['default']['result']['modify'])) {
                $tempData['default[result][modify]'] = $tempData['default']['result']['modify'];
            }

            if (isset($tempData['default']['result']['attribute'])) {
                $tempData['default[result][attribute]'] = $tempData['default']['result']['attribute'];
            }

            $tempModel->setData($tempData);
            $result[$tempModel->getId()] = $tempModel->getData();

            $this->dataPersistor->clear(Conditions::FORM_NAMESPACE);
        }
    }
}
