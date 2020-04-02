<?php
/**
 * Magezon
 *
 * This source file is subject to the Magezon Software License, which is available at https://www.magezon.com/license
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to https://www.magezon.com for more information.
 *
 * @page  Magezon
 * @package   Magezon_Builder
 * @copyright Copyright (C) 2019 Magezon (https://www.magezon.com)
 */

namespace Magezon\Builder\Controller\Adminhtml\Ajax;

class PageList extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\Cms\Model\ResourceModel\Page\CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @param \Magento\Backend\App\Action\Context                     $context           
     * @param \Magento\Cms\Model\ResourceModel\Page\CollectionFactory $collectionFactory 
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Cms\Model\ResourceModel\Page\CollectionFactory $collectionFactory
    ) {
        parent::__construct($context);
        $this->collectionFactory = $collectionFactory;
    }

    public function execute()
    {
        $result = [];
        try {
            $collection = $this->collectionFactory->create();
            if ($searchKey = $this->getRequest()->getParam('searchKey')) {
                $collection->addFieldToFilter('title', ['like' => '%' . $searchKey . '%']);
                foreach ($collection as $item) {
                    $result['options'][] = [
                        'label' => '[ID:' . $item->getId() . '] ' . $item->getTitle(),
                        'value' => $item->getId(),
                        'path'  => ''
                    ];
                }
                $result['total'] = $collection->getSize();
            } else {
                $post = $this->getRequest()->getPostValue();
                if (isset($post['key'])) {
                    $key = $post['key'];
                    if (is_numeric($key)) {
                        $collection->addFieldToFilter('page_id', $key);
                    } else {
                        $collection->addFieldToFilter('title', ['like' => '%' . $key . '%']);
                    }
                }
                foreach ($collection as $item) {
                    $result[] = [
                        'label' => '[ID:' . $item->getId() . '] ' . $item->getTitle(),
                        'value' => $item->getId()
                    ];
                }
            }
        } catch (\Exception $e) {
            
        }
        $this->getResponse()->representJson(
            $this->_objectManager->get(\Magento\Framework\Json\Helper\Data::class)->jsonEncode($result)
        );
    }
}