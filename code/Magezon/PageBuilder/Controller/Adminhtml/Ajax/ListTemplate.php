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

class ListTemplate extends \Magento\Backend\App\Action
{
    /**
     * @var \Magezon\PageBuilder\Model\ResourceModel\Template\CollectionFactory
     */
    protected $templateCollectionFactory;

    /**
     * @var \Magezon\Builder\Helper\Data
     */
    protected $builderHelper;

    /**
     * @param \Magento\Backend\App\Action\Context                                 $context                   
     * @param \Magezon\PageBuilder\Model\ResourceModel\Template\CollectionFactory $templateCollectionFactory 
     * @param \Magezon\Builder\Helper\Data                                        $builderHelper             
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magezon\Core\Helper\Data $coreHelper,
        \Magezon\PageBuilder\Model\ResourceModel\Template\CollectionFactory $templateCollectionFactory,
        \Magezon\Builder\Helper\Data $builderHelper
    ) {
        parent::__construct($context);
        $this->coreHelper                = $coreHelper;
        $this->templateCollectionFactory = $templateCollectionFactory;
        $this->builderHelper             = $builderHelper;
    }

    /**
     * Add product to shopping cart action
     *
     * @return \Magento\Framework\Controller\Result\Redirect
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function execute()
    {
    	$result = [];
    	$collection = $this->templateCollectionFactory->create();
    	$collection->setOrder('name', 'ASC');
    	foreach ($collection as $template) {
            $profile  = str_replace(['[mgz_pagebuilder]', '[/mgz_pagebuilder]'], ['', ''], $template->getProfile());
            $result[] = [
                'name'    => $template->getName(),
                'label'   => $template->getName(),
                'value'   => $template->getId(),
                'profile' => $this->coreHelper->unserialize($profile),
                'url'     => $this->builderHelper->getUrl('mgzpagebuilder/template/edit', ['template_id' => $template->getId()])
    		];
    	}
        $this->getResponse()->representJson(
            $this->_objectManager->get(\Magento\Framework\Json\Helper\Data::class)->jsonEncode($result)
        );
    }
}