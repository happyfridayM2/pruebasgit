<?php
/**
 * Copyright © 2019 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\OrdersExportTool\Model;

/**
 * Variable Model
 */
class Variables extends \Magento\Framework\Model\AbstractModel
{
    public function _construct()
    {
        $this->_init('Wyomind\OrdersExportTool\Model\ResourceModel\Variables');
    }
}