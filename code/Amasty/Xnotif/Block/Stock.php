<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Xnotif
 */
namespace Amasty\Xnotif\Block;

/**
 * Class Stock
 */
class Stock extends \Amasty\Xnotif\Block\AbstractBlock
{
    public function _construct()
    {
        $this->setAlertType("stock");
        parent::_construct();
    }
}
