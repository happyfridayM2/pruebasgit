<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Conf
 */


namespace Amasty\Conf\Controller\Adminhtml\Group;

class Index extends \Amasty\Conf\Controller\Adminhtml\Group
{
    /**
     * Index action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Amasty_Conf::group_attributes')
            ->addBreadcrumb(__('Groups'), __('Groups'));
        $resultPage->getConfig()->getTitle()->prepend(__('Manage Group Attributes'));
        return $resultPage;
    }
}