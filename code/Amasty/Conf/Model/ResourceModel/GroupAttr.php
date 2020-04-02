<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Conf
 */


namespace Amasty\Conf\Model\ResourceModel;

class GroupAttr extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    const IS_ENABLED = 1;
    const IS_DISABLED = 0;

    /**
     * @var \Amasty\Conf\Model\GroupAttrOptionFactory
     */
    private $option;

    /**
     * @var \Magento\Framework\Json\EncoderInterface
     */
    private $jsonEncoder;

    /**
     * @var \Amasty\Conf\Model\GroupAttrOptionRepository
     */
    private $groupAttrOptionRepository;

    /**
     * @var GroupAttrOption\CollectionFactory
     */
    private $collectionFactory;

    /**
     * GroupAttr constructor.
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     * @param \Amasty\Conf\Model\GroupAttrOptionFactory $option
     * @param \Magento\Framework\Json\EncoderInterface $jsonEncoder
     * @param null $connectionName
     */
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        \Amasty\Conf\Model\GroupAttrOptionFactory $option,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Amasty\Conf\Model\GroupAttrOptionRepository $groupAttrOptionRepository,
        \Amasty\Conf\Model\ResourceModel\GroupAttrOption\CollectionFactory $collectionFactory,
        $connectionName = null
    ) {
        parent::__construct($context, $connectionName);
        $this->option = $option;
        $this->jsonEncoder = $jsonEncoder;
        $this->groupAttrOptionRepository = $groupAttrOptionRepository;
        $this->collectionFactory = $collectionFactory;
    }

    protected function _construct()
    {
        $this->_init('amasty_conf_group_attr', 'group_id');
    }

    /**
     * @param \Magento\Framework\Model\AbstractModel $model
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     */
    protected function _afterSave(\Magento\Framework\Model\AbstractModel $model)
    {
        if ($model->getId() && $model->getData('attribute_options')) {
            foreach ($model->getData('attribute_options') as $key => $attributeOption) {
                if (array_key_exists('is_active', $attributeOption)) {
                    $this->saveOption($model->getId(), $key, $attributeOption['sort_order']['value']);
                } else {
                    $this->removeOption($key, $model->getData('group_id'));
                }
            }
        }
    }

    private function saveOption($groupId, $optionId, $sortOrder)
    {
        $model = $this->groupAttrOptionRepository->getByOptionAndGroup($optionId, $groupId);
        $model->setGroupId($groupId);
        $model->setSortOrder($sortOrder);
        $model->setOptionId($optionId);

        $this->groupAttrOptionRepository->save($model);
    }

    /**
     * @param $groupId
     * @param $optionId
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     */
    private function removeOption($optionId, $groupId)
    {
        $this->groupAttrOptionRepository->deleteByGroupIdAndOptionId($optionId, $groupId);
    }

    /**
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return $this
     */
    protected function _afterLoad(\Magento\Framework\Model\AbstractModel $object)
    {
        if ($object->getId()) {
            $object->setData('attribute_options', null);
            $collection = $this->collectionFactory->create()
                ->addFieldToFilter('group_id', $object->getId());
            if ($collection->getSize()) {
                $data = $optionIds = [];
                foreach ($collection as $value) {
                    $data[] = ['option_id' => $value->getOptionId(), 'sort_order' => $value->getSortOrder()];
                    $optionIds[] = $value->getOptionId();
                }
                $object->setData('attribute_options', $this->jsonEncoder->encode($data));
                $object->setData('option_ids', $optionIds);
            }
        }

        return $this;
    }

    public function getAttributeOptions($object)
    {
        $data = [];
        $collection = $this->collectionFactory->create()
            ->addFieldToFilter('group_id', $object->getId());
        if ($collection->getSize()) {
            foreach ($collection as $value) {
                $data[$value->getOptionId()] = [
                    'sort_order' => [
                        'value' => $value->getSortOrder()
                    ],
                    'is_active' => 1
                ];
            }
        }

        return $data;
    }

    /**
     * @param $item
     * @return array
     */
    public function getOptions($item)
    {
        $select = $this->getConnection()->select()->from(
            ['main' => $this->getTable('amasty_conf_group_attr_option')],
            ['code' => new \Zend_Db_Expr(sprintf('%s', $item['attribute_id'])), 'sort_order']
        )->where(
            'group_id = :group_id'
        );
        $bind = ['group_id' => (int)$item['group_id']];

        $select->columns(['id' => 'option_id']);
        $select->joinLeft(
            ['eaov' => $this->getTable('eav_attribute_option_value')],
            'eaov.option_id=main.option_id',
            ['value']
        )->joinLeft(
            ['eaos' => $this->getTable('eav_attribute_option_swatch')],
            'eaos.option_id=main.option_id and eaos.type <> 0',
            ['swatch' => 'value', 'type']
        );

        $select->group('main.option_id');
        return [$this->getConnection()->fetchAll($select, $bind), 'option'];
    }
}
