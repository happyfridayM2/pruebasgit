<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */


namespace Amasty\Feed\Controller\Adminhtml\Feed;

use Magento\Framework\Controller\ResultFactory;

/**
 * Class Index
 *
 * @package Amasty\Feed
 */
class Index extends \Amasty\Feed\Controller\Adminhtml\AbstractFeed
{
    /**
     * @inheritdoc
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        $resultPage->setActiveMenu('Amasty_Feed::feed_category');
        $resultPage->addBreadcrumb(__('Amasty Feed'), __('Amasty Feed'));
        $resultPage->addBreadcrumb(__('Feeds'), __('Feeds'));
        $resultPage->getConfig()->getTitle()->prepend(__('Feeds'));

        return $resultPage;
    }
}
