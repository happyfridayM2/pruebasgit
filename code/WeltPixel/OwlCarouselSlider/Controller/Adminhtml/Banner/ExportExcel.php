<?php

namespace WeltPixel\OwlCarouselSlider\Controller\Adminhtml\Banner;

use Magento\Framework\App\Filesystem\DirectoryList;

/**
 * ExportExcel action.
 * @category WeltPixel
 * @package  WeltPixel_OwlCarouselSlider
 * @module   OwlCarouselSlider
 * @author   WeltPixel Developer
 */
class ExportExcel extends \WeltPixel\OwlCarouselSlider\Controller\Adminhtml\Banner
{
    /**
     * Dispatch request
     */
    public function execute()
    {
        $fileName = 'owlcarousel_banners.xls';

        /** @var \\Magento\Framework\View\Result\Page $resultPage */
        $resultPage = $this->_resultPageFactory->create();
        $content = $resultPage->getLayout()
            ->createBlock('WeltPixel\OwlCarouselSlider\Block\Adminhtml\Banner\Grid')->getExcel();

        return $this->_fileFactory->create($fileName, $content, DirectoryList::VAR_DIR);
    }
}
