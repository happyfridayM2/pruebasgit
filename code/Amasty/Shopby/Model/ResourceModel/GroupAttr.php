<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Shopby
 */


namespace Amasty\Shopby\Model\ResourceModel;

/**
 * Class GroupAttr
 * @package Amasty\Shopby\Model\ResourceModel
 */
class GroupAttr extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * @var \Amasty\Shopby\Model\GroupAttrOptionFactory
     */
    protected $option;

    /**
     * @var \Magento\Framework\Json\EncoderInterface
     */
    protected $jsonEncoder;

    /**
     * @var \Amasty\Shopby\Model\GroupAttrValueFactory
     */
    protected $value;

    /**
     * @var array
     */
    protected $relatedArray = ['option', 'value'];
    
    /**
     * @var \Magento\Framework\Indexer\IndexerRegistry
     */
    private $indexerRegistry;

    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        \Amasty\Shopby\Model\GroupAttrOptionFactory $option,
        \Amasty\Shopby\Model\GroupAttrValueFactory $value,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Magento\Framework\Indexer\IndexerRegistry $indexerRegistry,
        $connectionName = null
    ) {
        parent::__construct($context, $connectionName);
        $this->option = $option;
        $this->value = $value;
        $this->jsonEncoder = $jsonEncoder;
        $this->indexerRegistry = $indexerRegistry;
    }

    protected function _construct()
    {
        $this->_init('amasty_amshopby_group_attr', 'group_id');
    }

    /**
     * @param \Magento\Framework\Model\AbstractModel $object
     */
    protected function _afterSave(\Magento\Framework\Model\AbstractModel $object)
    {
        if ($object->getId()) {
            foreach ($this->relatedArray as $relate) {
                if ($object->getData('attribute_' . $relate . 's')) {
                    $this->{'saveTo' . ucfirst($relate)}($object);
                }
            }
        }

        $this->invalidateEavIndex();
        return parent::_afterSave($object);
    }

    /**
     * @param \Magento\Framework\Model\AbstractModel $object
     */
    protected function _afterLoad(\Magento\Framework\Model\AbstractModel $object)
    {
        if ($object->getId()) {
            $object->setData('attribute_options', null);
            foreach ([$this->option, $this->value] as $factory) {
                $collection = $factory->create()->getCollection()->addFieldToFilter('group_id', $object->getId());
                if ($collection->getSize()) {
                    $data = [];
                    foreach ($collection as $value) {
                        if ($factory instanceof \Amasty\Shopby\Model\GroupAttrOptionFactory) {
                            $data[] = ['option_id' => $value->getOptionId(), 'sort_order' => $value->getSortOrder()];
                        } elseif ($factory instanceof \Amasty\Shopby\Model\GroupAttrValueFactory) {
                            $data[] = ['value' => $value->getValue(), 'sort_order' => $value->getSortOrder()];
                        }
                    }
                    $object->setData('attribute_options', $this->jsonEncoder->encode($data));
                }
            }
        }

        return parent::_afterLoad($object);
    }

    /**
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return $this
     */
    protected function _afterDelete(\Magento\Framework\Model\AbstractModel $object)
    {
        $this->invalidateEavIndex();
        return parent::_afterDelete($object);
    }

    /**
     * @return $this
     */
    private function invalidateEavIndex()
    {
        $indexer = $this->indexerRegistry->get(\Magento\Catalog\Model\Indexer\Product\Eav\Processor::INDEXER_ID);
        $indexer->invalidate();
        return $this;
    }

    /**
     * @param $method
     * @param $value
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function __call($method, $value)
    {

        if (strpos($method, 'clear') === 0) {
            $variable = lcfirst(substr($method, 5));
            if (property_exists(self::class, $variable)) {
                $collection = $this->$variable->create()->getCollection()->addFieldToFilter('group_id', $value[0]);
                foreach ($collection as $item) {
                    $item->delete();
                }
            }

            return true;
        }
        if (strpos($method, 'saveTo') === 0) {
            $variable = lcfirst(substr($method, 6));
            if (property_exists(self::class, $variable)) {
                foreach ($this->relatedArray as $relate) {
                    $this->{'clear' . $relate}($value[0]->getId());
                }
                $data = $value[0]->getData('attribute_' . $variable . 's');

                foreach ($data as $key => $item) {
                    $newOptions = [];
                    if (isset($item['is_active'])) {
                        $newOptions = [
                            'group_id' => $value[0]->getId(),
                            'sort_order' => $item['sort_order']['value']
                        ];
                        if ($this->$variable instanceof \Amasty\Shopby\Model\GroupAttrOptionFactory) {
                            $newOptions['option_id'] = $key;
                        }
                        if ($this->$variable instanceof \Amasty\Shopby\Model\GroupAttrValueFactory) {
                            $newOptions['value'] = $item['is_active']['value'];
                        }

                        $option = $this->$variable->create();
                        $option->setData($newOptions);
                        $option->save();
                    }
                }
            }

            return true;
        }

        throw new \Magento\Framework\Exception\LocalizedException(
            __("Invalid method %1::%2", get_class($this), $method)
        );
    }

    /**
     * @param $item
     * @return array
     */
    public function getOptions($item)
    {
        $object = null;
        foreach ($this->relatedArray as $record) {
            if ($this->isOptions((int)$item['group_id'], $record)) {
                $object = $record;
                break;
            }
        }

        if ($object) {
            $select = $this->getConnection()->select()->from(
                ['op' => $this->getTable('amasty_amshopby_group_attr_' . $object)],
                ['code' => new \Zend_Db_Expr(sprintf('%s', $item['attribute_id'])), 'sort_order']
            )->where(
                'group_id = :group_id'
            );
            $bind = ['group_id' => (int)$item['group_id']];

            if ($object == 'option') {
                $select->columns(['id' => 'option_id']);
                $select->joinLeft(
                    ['eaov' => $this->getTable('eav_attribute_option_value')],
                    'eaov.option_id=op.option_id',
                    ['value']
                )->joinLeft(
                    ['eaos' => $this->getTable('eav_attribute_option_swatch')],
                    'eaos.option_id=op.option_id and eaos.type <> 0',
                    ['swatch' => 'value', 'type']
                );
            } else {
                $select->columns([
                    'value',
                    'type' => new \Zend_Db_Expr('0'),
                    'id' => 'group_option_id',
                    'swatch' => new \Zend_Db_Expr('0')
                ]);
            }
            
            $select->group('op.group_option_id');

            return [$this->getConnection()->fetchAll($select, $bind), $object];
        }

        return [[], null];
    }

    /**
     * @param $groupId
     * @param string $part
     * @return string
     */
    private function isOptions($groupId, $part = 'option')
    {
        $select = $this->getConnection()->select()->from(
            ['op' => $this->getTable('amasty_amshopby_group_attr_' . $part)],
            ['count' => new \Zend_Db_Expr('COUNT(group_id)')]
        )->where(
            'group_id = :group_id'
        );
        $bind = ['group_id' => $groupId];

        return $this->getConnection()->fetchOne($select, $bind);
    }
}
