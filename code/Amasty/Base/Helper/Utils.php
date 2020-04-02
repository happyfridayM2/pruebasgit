<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Base
 */


namespace Amasty\Base\Helper;

use Magento\Framework\App\Helper\AbstractHelper;

class Utils extends AbstractHelper
{
    public function _exit($code = 0)
    {
        //phpcs:ignore
        exit($code);
    }

    public function _echo($a)
    {
        //phpcs:ignore
        echo $a;
    }
}
