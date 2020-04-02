<?php
/**
 * Magezon
 *
 * This source file is subject to the Magezon Software License, which is available at https://www.magezon.com/license
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to https://www.magezon.com for more information.
 *
 * @category  Magezon
 * @package   Magezon_Builder
 * @copyright Copyright (C) 2019 Magezon (https://www.magezon.com)
 */

namespace Magezon\Builder\Controller\Adminhtml\Ajax;

class BlockList extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\Cms\Model\ResourceModel\Block\CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @param \Magento\Backend\App\Action\Context                      $context           
     * @param \Magento\Cms\Model\ResourceModel\Block\CollectionFactory $collectionFactory 
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Cms\Model\ResourceModel\Block\CollectionFactory $collectionFactory
    ) {
        parent::__construct($context);
        $this->collectionFactory = $collectionFactory;
    }

    public function execute()
    {
        $list = [];
        try {
            $post = $this->getRequest()->getPostValue();
            $collection = $this->collectionFactory->create();
            if (isset($post['key'])) {
                if (is_numeric($post['key'])) {
                    $collection->addFieldToFilter('block_id', $post['key']);
                } else {
                    $collection->addFieldToFilter('title', ['like' => '%' . $post['key'] . '%']);
                }
            }
            foreach ($collection as $item) {
                $list[] = [
                    'label' => '[ID:' . $item->getId() . '] ' . $item->getTitle(),
                    'value' => $item->getId()
                ];
            }
        } catch (\Exception $e) {
            
        }
        $this->getResponse()->representJson(
            $this->_objectManager->get(\Magento\Framework\Json\Helper\Data::class)->jsonEncode($list)
        );
    }
}