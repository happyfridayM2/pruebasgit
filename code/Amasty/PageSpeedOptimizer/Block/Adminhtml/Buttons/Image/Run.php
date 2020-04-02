<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_PageSpeedOptimizer
 */


namespace Amasty\PageSpeedOptimizer\Block\Adminhtml\Buttons\Image;

use Amasty\PageSpeedOptimizer\Block\Adminhtml\Buttons\GenericButton;
use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

class Run extends GenericButton implements ButtonProviderInterface
{
    /**
     * @return array
     */
    public function getButtonData()
    {
        $data = [
            'label' => __('Run Optimization'),
            'class' => 'primary',
            'id' => 'image-optimization-run-button',
            'on_click' => 'var registry = require("uiRegistry");'
                . 'registry.get("amoptimizer_image_listing.amoptimizer_image_listing.modal").toggleModal();'
                . 'registry.get("amoptimizer_image_listing.amoptimizer_image_listing.modal.container").start()'
        ];

        return $data;
    }
}
