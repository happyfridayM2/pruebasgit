<?php

namespace WeltPixel\OwlCarouselSlider\Controller\Adminhtml\Banner;

use Magento\Framework\App\Filesystem\DirectoryList;

/**
 * ExportCsv action.
 * @category WeltPixel
 * @package  WeltPixel_OwlCarouselSlider
 * @module   OwlCarouselSlider
 * @author   WeltPixel Developer
 */
class ExportCsv extends \WeltPixel\OwlCarouselSlider\Controller\Adminhtml\Banner
{
    /**
     * Dispatch request
     */
    public function execute()
    {
        $fileName = 'owlcarousel_banners.csv';

        /** @var \\Magento\Framework\View\Result\Page $resultPage */
        $resultPage = $this->_resultPageFactory->create();
        $content = $resultPage->getLayout()
            ->createBlock('WeltPixel\OwlCarouselSlider\Block\Adminhtml\Banner\Grid')->getCsv();

        return $this->_fileFactory->create($fileName, $content, DirectoryList::VAR_DIR);
    }
}
