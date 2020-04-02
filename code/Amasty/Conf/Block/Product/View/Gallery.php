<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Conf
 */


namespace Amasty\Conf\Block\Product\View;

use Amasty\Conf\Model\Source\CarouselPosition;
use Amasty\Conf\Model\Source\ImageChange;
use Amasty\Conf\Model\Source\ZoomType;
use Magento\Framework\Json\EncoderInterface;

class Gallery extends \Magento\Catalog\Block\Product\View\Gallery
{
    const DEFAULT_LIGHTBOX_SIZE = 50;

    /**
     * @var \Amasty\Conf\Helper\Data
     */
    private $helper;

    public function _construct()
    {
        $this->helper = $this->getData('conf_helper');
        parent::_construct();
    }

    /**
     * @return string
     */
    public function getGalleryOptionJson()
    {
        $result = [];

        $this->generateGeneralConfig($result);
        $this->generateZoomConfig($result);
        $this->generateLightboxConfig($result);
        $this->generateCarouselConfig($result);

        return $this->jsonEncoder->encode($result);
    }

    private function generateGeneralConfig(&$result)
    {
        $result['general'] = [
            'general' => (bool)$this->helper->getModuleConfig('general/enable_zoom_lightbox'),
            'zoom' => $this->helper->getModuleConfig('zoom/zoom_type') ? true : false,
            'lightbox' => (bool)$this->helper->getModuleConfig('lightbox/enable'),
            'carousel' => (bool)$this->helper->getModuleConfig('carousel/enable'),
            'thumbnail_lignhtbox' => (bool)$this->helper->getModuleConfig('lightbox/thumbnail_lignhtbox'),
            'carousel_position' => $this->getCarouselPosition()
        ];
    }

    private function generateZoomConfig(&$result)
    {
        $config = [
            'small_size' => $this->_viewConfig->getViewConfig()->getMediaAttributes(
                'Magento_Catalog',
                'images',
                'product_page_image_small'
            ),
            'medium_size' => $this->_viewConfig->getViewConfig()->getMediaAttributes(
                'Magento_Catalog',
                'images',
                'product_page_image_medium'
            )
        ];

        $zoomType = $this->helper->getModuleConfig('zoom/zoom_type');
        $imageChange = $this->helper->getModuleConfig('carousel/image_change');
        if ($zoomType || $result['general']['lightbox'] || $imageChange) {
            $config['zoomType'] = $zoomType;
            $config['image_change'] = $imageChange;
            $config['loadingIcon'] = $this->getViewFileUrl('images/loader-1.gif');

            if ($imageChange == ImageChange::ON_CLICK) {
                $config['imageCrossfade'] = true;
            }

            if ($imageChange) {
                $config["gallery"] = 'amasty-gallery-images';
                $config["cursor"] = 'pointer';
                $config["galleryActiveClass"] = 'active';
            }

            switch ($config['zoomType']) {
                case ZoomType::LENS:
                    $config["lensShape"] = "round";
                    $config["lensSize"] = $this->helper->getModuleConfig('zoom/lens_size');
                    $config["borderSize"] = 1;
                    $config["containLensZoom"] = true;
                    break;

                case ZoomType::INSIDE:
                    $config["cursor"] = "crosshair";
                    break;

                case ZoomType::OUTSIDE:
                default:
                    $config["zoomWindowOffetx"] = (int)$this->helper->getModuleConfig('zoom/offset_x');
                    $config["zoomWindowOffety"] = (int)$this->helper->getModuleConfig('zoom/offset_y');
                    $config["zoomWindowPosition"] = (int)$this->helper->getModuleConfig('zoom/viewer_position');
                    $config["zoomWindowWidth"] = (int)$this->helper->getModuleConfig('zoom/viewer_width');
                    $config["zoomWindowHeight"] = (int)$this->helper->getModuleConfig('zoom/viewer_height');

                    $color = trim($this->helper->getModuleConfig('zoom/tint_color'));
                    if ($color) {
                        $config["tint"] = true;
                        $config["tintOpacity"] = 0.5;
                        $config["tintColour"] = $color;
                    }
            }
        }

        $result['zoom'] = $config;
    }

    private function generateLightboxConfig(&$result)
    {
        if ((bool)$this->helper->getModuleConfig('lightbox/enable')) {
            $result['zoom']["gallery"] = 'amasty-gallery-images';
            $result['zoom']["cursor"] = 'pointer';
            $result['zoom']["galleryActiveClass"] = 'active';

            $result['lightbox'] = [
                'loop' => (int)$this->helper->getModuleConfig('lightbox/circular_lightbox'),
                'transitionEffect' => $this->helper->getModuleConfig('lightbox/effect_slide'),
                'animationEffect' => $this->helper->getModuleConfig('lightbox/animationEffect')
            ];

            if ($this->helper->getModuleConfig('lightbox/thumbnail_helper')) {
                $result['lightbox']['thumbs']['autoStart'] = true;
            }
        }
    }

    private function generateCarouselConfig(&$result)
    {
        $mainImageSwipe = $this->helper->getModuleConfig('carousel/main_image_swipe')
            && $this->helper->getModuleConfig('carousel/image_change');

        $config = [
            'dots' => (bool)$this->helper->getModuleConfig('carousel/use_pagination'),
            'arrows' => (bool)$this->helper->getModuleConfig('carousel/use_pagination'),
            'infinite' => (bool)$this->helper->getModuleConfig('carousel/circular'),
            'slidesToShow' => (int)$this->helper->getModuleConfig('carousel/slides_to_show'),
            'slidesToScroll' => (int)$this->helper->getModuleConfig('carousel/slides_to_scroll'),
            'autoplay' => (bool)$this->helper->getModuleConfig('carousel/auto_scrolling'),
            'main_image_swipe' => $mainImageSwipe
        ];

        if ($this->getCarouselPosition() == CarouselPosition::LEFT_SIDE_IMAGE) {
            $config['vertical'] = true;
            $config['verticalSwiping'] = true;
        }

        $result['carousel'] = $config;
    }

    /**
     * @return string
     */
    private function getCarouselPosition()
    {
        $value = $this->helper->getModuleConfig('carousel/carousel_position');

        return $value;
    }
}
