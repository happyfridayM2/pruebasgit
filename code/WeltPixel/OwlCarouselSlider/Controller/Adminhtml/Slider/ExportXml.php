<?php

namespace WeltPixel\OwlCarouselSlider\Controller\Adminhtml\Slider;

use Magento\Framework\App\Filesystem\DirectoryList;

/**
 * ExportXml action
 * @category WeltPixel
 * @package  WeltPixel_OwlCarouselSlider
 * @module   OwlCarouselSlider
 * @author   WeltPixel Developer
 */
class ExportXml extends \WeltPixel\OwlCarouselSlider\Controller\Adminhtml\Slider
{
    /**
     * Dispatch request
     */
    public function execute()
    {
        $fileName = 'owlcarousel_sliders.xml';

        /** @var \\Magento\Framework\View\Result\Page $resultPage */
        $resultPage = $this->_resultPageFactory->create();
        $content = $resultPage->getLayout()
            ->createBlock('WeltPixel\OwlCarouselSlider\Block\Adminhtml\Slider\Grid')->getXml();

        return $this->_fileFactory->create($fileName, $content, DirectoryList::VAR_DIR);
    }
}
