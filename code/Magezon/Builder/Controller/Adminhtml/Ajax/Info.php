<?php
/**
 * Magezon
 *
 * This source file is subject to the Magezon Software License, which is available at https://www.magezon.com/license
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to https://www.magezon.com for more information.
 *
 * @product   Magezon
 * @package   Magezon_Builder
 * @copyright Copyright (C) 2019 Magezon (https://www.magezon.com)
 */

namespace Magezon\Builder\Controller\Adminhtml\Ajax;

class Info extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\Catalog\Model\CategoryFactory
     */
    protected $categoryFactory;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $productFactory;

    /**
     * @var \Magento\Cms\Model\PageFactory
     */
    protected $pageFactory;

    /**
     * @var \Magento\Cms\Model\BlockFactory
     */
    protected $blockFactory;

    /**
     * @param \Magento\Backend\App\Action\Context    $context         
     * @param \Magento\Catalog\Model\CategoryFactory $categoryFactory 
     * @param \Magento\Catalog\Model\ProductFactory  $productFactory  
     * @param \Magento\Cms\Model\PageFactory         $pageFactory     
     * @param \Magento\Cms\Model\BlockFactory        $blockFactory    
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Catalog\Model\CategoryFactory $categoryFactory,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Cms\Model\PageFactory $pageFactory,
        \Magento\Cms\Model\BlockFactory $blockFactory
    ) {
        parent::__construct($context);
        $this->categoryFactory = $categoryFactory;
        $this->productFactory  = $productFactory;
        $this->pageFactory     = $pageFactory;
        $this->blockFactory    = $blockFactory;
    }

    public function execute()
    {
        $data = [];
        try {
            $post = $this->getRequest()->getPostValue();
            if (isset($post['type']) && $post['type'] && isset($post['id']) && $post['id']) {
                switch ($post['type']) {
                    case 'category':
                        $category = $this->categoryFactory->create();
                        $category->load($post['id']);
                        $data = $category->getData();
                        break;

                    case 'product':
                        $product = $this->productFactory->create();
                        $product->load($post['id']);
                        $data = $product->getData();
                        break;

                    case 'page':
                        $page = $this->pageFactory->create();
                        $page->load($post['id']);
                        $data = $page->getData();
                        break;

                    case 'block':
                        $block = $this->blockFactory->create();
                        $block->load($post['id']);
                        $data = $block->getData();
                        break;
                }
            }
        } catch (\Exception $e) {
            
        }
        $this->getResponse()->representJson(
            $this->_objectManager->get(\Magento\Framework\Json\Helper\Data::class)->jsonEncode($data)
        );
        return;
    }
}