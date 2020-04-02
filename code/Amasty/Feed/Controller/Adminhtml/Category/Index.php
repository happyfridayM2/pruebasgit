<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */


namespace Amasty\Feed\Controller\Adminhtml\Category;

use Amasty\Feed\Controller\Adminhtml\AbstractCategory;
use Magento\Framework\Controller\ResultFactory;

/**
 * Class Index
 *
 * @package Amasty\Feed
 */
class Index extends AbstractCategory
{
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        $resultPage->setActiveMenu('Amasty_Feed::feed_category');
        $resultPage->addBreadcrumb(__('Amasty Feed'), __('Amasty Feed'));
        $resultPage->addBreadcrumb(__('Categories Mapping'), __('Categories Mapping'));
        $resultPage->getConfig()->getTitle()->prepend(__('Categories Mapping'));

        return $resultPage;
    }
}
