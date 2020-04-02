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
 * @package   Magezon_PageBuilder
 * @copyright Copyright (C) 2019 Magezon (https://www.magezon.com)
 */

namespace Magezon\PageBuilder\Controller\Adminhtml\Ajax;

class LibraryTemplate extends \Magento\Backend\App\Action
{
    /**
     * @var \Magezon\PageBuilder\Helper\Data
     */
    protected $dataHelper;

    /**
     * @param \Magento\Backend\App\Action\Context $context    
     * @param \Magezon\PageBuilder\Helper\Data    $dataHelper 
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magezon\PageBuilder\Helper\Data $dataHelper
    ) {
        parent::__construct($context);
        $this->dataHelper = $dataHelper;
    }

    public function execute()
    {
        $templates = $this->dataHelper->getTemplates();
    	$this->getResponse()->representJson(
            $this->_objectManager->get(\Magento\Framework\Json\Helper\Data::class)->jsonEncode($templates)
        );
    }
}