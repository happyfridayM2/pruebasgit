<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Xnotif
 */
namespace Amasty\Xnotif\Controller\Stock;

/**
 * Class Index
 */
class Index extends \Amasty\Xnotif\Controller\AbstractIndex
{
    const TYPE = "stock";

    public function getTitle()
    {
        return __("My Back in Stock Subscriptions");
    }
}
