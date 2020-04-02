<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Xnotif
 */
namespace Amasty\Xnotif\Controller\Adminhtml\Stock;

/**
 * Class Index
 */
class Index extends \Amasty\Xnotif\Controller\Adminhtml\AbstractControllerIndex
{
    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|\Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        /** @var \Magento\Framework\View\Result\Page $pageResult */
        $pageResult = $this->getResultPageFactory()->create();
        $layout = $pageResult->getLayout();

        $pageResult->setActiveMenu('Amasty_Xnotif::amxnotif_stock');
        $pageResult->addBreadcrumb(__('Alerts'), __('Stock Alerts'));
        $pageResult->addContent($layout->createBlock(\Amasty\Xnotif\Block\Adminhtml\Stock::class));

        /* Add message about cron job*/
        $this->addMessage();

        $pageResult->getConfig()->getTitle()->prepend(__('Stock Alerts '));

        return $pageResult;
    }

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Amasty_Xnotif::stock');
    }
}
