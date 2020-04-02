<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_PageSpeedOptimizer
 */


namespace Amasty\PageSpeedOptimizer\Block\Adminhtml\Buttons\Image;

use Amasty\PageSpeedOptimizer\Block\Adminhtml\Buttons\GenericButton;
use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

class SaveAndOptimize extends GenericButton implements ButtonProviderInterface
{
    /**
     * @return array
     */
    public function getButtonData()
    {
        return [
            'label' => __('Save and Optimize'),
            'class' => 'save',
            'data_attribute' => [
                'mage-init' => [
                    'Magento_Ui/js/form/button-adapter' => [
                        'actions' => [
                            [
                                'targetName' => 'amoptimizer_image_form.amoptimizer_image_form',
                                'actionName' => 'save',
                                'params' => [
                                    true,
                                    ['save_and_optimize' => 1],
                                ]
                            ]
                        ]
                    ]
                ],
            ],
            'on_click' => '',
            'sort_order' => 10
        ];
    }
}
