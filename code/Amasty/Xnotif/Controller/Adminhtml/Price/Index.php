<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Xnotif
 */


namespace Amasty\Xnotif\Controller\Adminhtml\Price;

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
        $resultPage = $this->getResultPageFactory()->create();

        $resultPage->getLayout();
        $resultPage->setActiveMenu('Amasty_Xnotig::amxnotif_price');
        $resultPage->addBreadcrumb(__('Alerts'), __('Price Alerts'));
        $resultPage->addContent($resultPage->getLayout()->createBlock(\Amasty\Xnotif\Block\Adminhtml\Price::class));

        $this->addMessage();
        $resultPage->getConfig()->getTitle()->prepend(__('Price Alerts'));

        return $resultPage;
    }

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Amasty_Xnotif::price');
    }
}
