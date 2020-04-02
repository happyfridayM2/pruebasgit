<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_ShopbyPage
 */


namespace Amasty\ShopbyPage\Block\Adminhtml\Page\Edit;

/**
 * @api
 */
class Tabs extends \Magento\Backend\Block\Widget\Tabs
{
    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('amasty_shopbypage_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(__('Custom Page Information'));
    }
}
