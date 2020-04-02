<?php

namespace WeltPixel\OwlCarouselSlider\Controller\Adminhtml\Banner;

use Magento\Framework\App\Filesystem\DirectoryList;

/**
 * ExportXml action
 * @category WeltPixel
 * @package  WeltPixel_OwlCarouselSlider
 * @module   OwlCarouselSlider
 * @author   WeltPixel Developer
 */
class ExportXml extends \WeltPixel\OwlCarouselSlider\Controller\Adminhtml\Banner
{
    /**
     * Dispatch request
     */
    public function execute()
    {
        $fileName = 'owlcarousel_banners.xml';

        /** @var \\Magento\Framework\View\Result\Page $resultPage */
        $resultPage = $this->_resultPageFactory->create();
        $content = $resultPage->getLayout()
            ->createBlock('WeltPixel\OwlCarouselSlider\Block\Adminhtml\Banner\Grid')->getXml();

        return $this->_fileFactory->create($fileName, $content, DirectoryList::VAR_DIR);
    }
}
