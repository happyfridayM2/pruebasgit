<?php

namespace WeltPixel\OwlCarouselSlider\Controller\Adminhtml\Slider;

use Magento\Framework\App\Filesystem\DirectoryList;

/**
 * Export Excel action
 * @category WeltPixel
 * @package  WeltPixel_OwlCarouselSlider
 * @module   OwlCarouselSlider
 * @author   WeltPixel Developer
 */
class ExportExcel extends \WeltPixel\OwlCarouselSlider\Controller\Adminhtml\Slider
{
    /**
     * Dispatch request
     */
    public function execute()
    {
        $fileName = 'owlcarousel_sliders.xls';

        /** @var \\Magento\Framework\View\Result\Page $resultPage */
        $resultPage = $this->_resultPageFactory->create();
        $content = $resultPage->getLayout()
            ->createBlock('WeltPixel\OwlCarouselSlider\Block\Adminhtml\Slider\Grid')->getExcel();

        return $this->_fileFactory->create($fileName, $content, DirectoryList::VAR_DIR);
    }
}
