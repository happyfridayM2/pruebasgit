<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Checkout
 */


namespace Amasty\Checkout\Model\Config\Backend;

use Magento\Framework\App\Config\Value;
use Magento\Framework\App\Config\Data\ProcessorInterface;
use Magento\Framework\Model\Context;
use Magento\Framework\Registry;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Cache\TypeListInterface;
use Amasty\Base\Model\Serializer;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Data\Collection\AbstractDb;

/**
 * Backend Model For Draggable Fields
 */
class OrderBlockSave extends Value implements ProcessorInterface
{
    /**
     * @var Serializer
     */
    private $serializer;

    public function __construct(
        Context $context,
        Registry $registry,
        ScopeConfigInterface $config,
        TypeListInterface $cacheTypeList,
        Serializer $serializer,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct($context, $registry, $config, $cacheTypeList, $resource, $resourceCollection, $data);
        $this->serializer = $serializer;
    }

    /**
     * @inheritdoc
     */
    public function processValue($value)
    {
        return $value;
    }

    /**
     * @inheritdoc
     */
    public function beforeSave()
    {
        $groups = $this->getData('groups');
        if (is_array($groups) && isset($groups['design']['groups']['layout'])) {
            $blockField = $groups['design']['groups']['layout']['fields'][$this->getField()];
            $this->setValue($this->serializer->serialize($blockField));
        }

        return parent::beforeSave();
    }

    /**
     * @inheritdoc
     */
    public function afterDelete()
    {
        $data = $this->getData('groups');

        if (isset($data['block_names']['fields']['block_management']['inherit'])) {
            $inherit = $data['block_names']['fields']['block_management']['inherit'];

            if ($inherit) {
                $this->_resourceCollection->addFieldToFilter('scope_id', $this->getScopeId())
                    ->addFieldToFilter('scope', $this->getScope())
                    ->walk('delete');
            }
        }

        return parent::afterDelete();
    }
}
