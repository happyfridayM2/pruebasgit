<?php
namespace WeltPixel\OwlCarouselSlider\Block\Product;

use Magento\Catalog\Helper\ImageFactory as HelperFactory;

class ImageBuilder extends \Magento\Catalog\Block\Product\ImageBuilder
{
    /**
     * @var \WeltPixel\OwlCarouselSlider\Helper\Custom
     */
    protected $_helperCustom;

    /**
     * @param HelperFactory $helperFactory
     * @param \Magento\Catalog\Block\Product\ImageFactory $imageFactory
     * @param \WeltPixel\OwlCarouselSlider\Helper\Custom $_helperCustom
     */
    public function __construct(
        HelperFactory $helperFactory,
        \Magento\Catalog\Block\Product\ImageFactory $imageFactory,
        \WeltPixel\OwlCarouselSlider\Helper\Custom $_helperCustom
    ) {
        $this->_helperCustom = $_helperCustom;
        parent::__construct($helperFactory, $imageFactory);
    }



    /**
     * Create image block
     *
     * @return \Magento\Catalog\Block\Product\Image
     */
    public function create()
    {
        /** Check if module is enabled */
        if (!$this->_helperCustom->isHoverImageEnabled() && !$this->isLazyLoadEnabled()) {
            return parent::create();
        }

        /** @var \Magento\Catalog\Helper\Image $helper */
        $helper = $this->helperFactory->create()
            ->init($this->product, $this->imageId);

        $template = $helper->getFrame()
            ? 'WeltPixel_OwlCarouselSlider::product/image.phtml'
            : 'WeltPixel_OwlCarouselSlider::product/image_with_borders.phtml';

        $data['data']['template'] = $template;

        $imagesize = $helper->getResizedImageInfo();

        $data = [
            'data' => [
                'template' => $template,
                'image_url' => $helper->getUrl(),
                'width' => $helper->getWidth(),
                'height' => $helper->getHeight(),
                'label' => $helper->getLabel(),
                'ratio' =>  $this->getRatio($helper),
                'custom_attributes' => $this->getCustomAttributes(),
                'resized_image_width' => !empty($imagesize[0]) ? $imagesize[0] : $helper->getWidth(),
                'resized_image_height' => !empty($imagesize[1]) ? $imagesize[1] : $helper->getHeight(),
            ],
        ];

        $hoverImageIds = [
            'related_products_list',
            'upsell_products_list',
            'cart_cross_sell_products',
            'new_products_content_widget_grid'
        ];
        if ($this->_helperCustom->isHoverImageEnabled() && in_array( $this->imageId, $hoverImageIds ) ) {
            /** @var \Magento\Catalog\Helper\Image $helper */
            $hoverHelper = $this->helperFactory->create()
                ->init($this->product, $this->imageId . '_hover')->resize($helper->getWidth(), $helper->getHeight());

            $hoverImageUrl = $hoverHelper->getUrl();
            $placeHolderUrl =  $hoverHelper->getDefaultPlaceholderUrl();

            /** Do not display hover placeholder */
            if ($placeHolderUrl == $hoverImageUrl) {
                $data['data']['hover_image_url'] = NULL;
            } else {
                $data['data']['hover_image_url'] = $hoverImageUrl;
            }
        }

        if ($this->isLazyLoadEnabled()) {
            $data['data']['lazy_load'] = true;
        }


        return $this->imageFactory->create($data);
    }

    /**
     * @return bool
     */
    protected function isLazyLoadEnabled() {
        foreach ($this->attributes as $name => $value) {
            if ($name == 'weltpixel_lazyLoad') {
                return true;
            }
        }

        return false;
    }

    /**
     * Retrieve image custom attributes for HTML element
     *
     * @return string
     */
    protected function getCustomAttributes()
    {
        $result = [];
        foreach ($this->attributes as $name => $value) {
            if ($name == 'weltpixel_lazyLoad') continue;
            $result[] = $name . '="' . $value . '"';
        }
        return !empty($result) ? implode(' ', $result) : '';
    }

}