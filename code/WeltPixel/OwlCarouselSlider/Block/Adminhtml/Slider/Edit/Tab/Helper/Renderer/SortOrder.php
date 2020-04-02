<?php

namespace WeltPixel\OwlCarouselSlider\Block\Adminhtml\Slider\Edit\Tab\Helper\Renderer;

/**
 * Edit banner form
 * @category WeltPixel
 * @package  WeltPixel_OwlCarouselSlider
 * @module   OwlCarouselSlider
 * @author   WeltPixel Developer
 */
class SortOrder extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    /**
     * Store manager.
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * banner factory.
     *
     * @var \WeltPixel\OwlCarouselSlider\Model\BannerFactory
     */
    protected $_bannerFactory;

    /**
     * [__construct description].
     *
     * @param \Magento\Backend\Block\Context                   $context
     * @param \Magento\Store\Model\StoreManagerInterface       $storeManager
     * @param \WeltPixel\OwlCarouselSlider\Model\BannerFactory $bannerFactory
     * @param array                                            $data
     */
    public function __construct(
        \Magento\Backend\Block\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \WeltPixel\OwlCarouselSlider\Model\BannerFactory $bannerFactory,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->_storeManager = $storeManager;
        $this->_bannerFactory = $bannerFactory;
    }

    /**
     * Render action.
     *
     * @param \Magento\Framework\DataObject $row
     *
     * @return string
     */
    public function render(\Magento\Framework\DataObject $row)
    {
        $sliderId = $this->getRequest()->getParam('id');
        $sortOrder = @unserialize($row->getSortOrder());
        if ($sortOrder !== false && isset($sortOrder[$sliderId])) {
            $sortOrder = $sortOrder[$sliderId];
        } else {
            $sortOrder = 0;
        }

        $input = '<input type="text" class="input-text " name="sort_order" value="' . $sortOrder . '" tabindex="">';
        $input .= '<p>' . __('Make sure this value is not duplicated') . '</p>';

        return $input;
    }
}
