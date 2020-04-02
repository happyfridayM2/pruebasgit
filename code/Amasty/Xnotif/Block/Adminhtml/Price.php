<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Xnotif
 */
namespace Amasty\Xnotif\Block\Adminhtml;

/**
 * Class Price
 */
class Price extends \Magento\Backend\Block\Widget\Grid\Container
{
    public function _construct()
    {
        $this->_controller = 'adminhtml_price';
        $this->_blockGroup = 'Amasty_Xnotif';
        $this->removeButton('add');
    }
}
