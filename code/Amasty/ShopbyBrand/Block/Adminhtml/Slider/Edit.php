<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_ShopbyBrand
 */


namespace Amasty\ShopbyBrand\Block\Adminhtml\Slider;

use Magento\Backend\Block\Widget\Form\Container;

/**
 * @api
 */
class Edit extends Container
{
    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_objectId = 'option_setting_id';
        $this->_controller = 'adminhtml_slider';
        $this->_blockGroup = 'Amasty_ShopbyBrand';
        parent::_construct();
        $this->buttonList->add(
            'saveandcontinue',
            [
                'label' => __('Save and Continue Edit'),
                'class' => 'save primary',
                'data_attribute' => [
                    'mage-init' => [
                        'button' => ['event' => 'saveAndContinueEdit', 'target' => '#edit_form'],
                    ],
                ]
            ],
            -100
        );
    }
}
