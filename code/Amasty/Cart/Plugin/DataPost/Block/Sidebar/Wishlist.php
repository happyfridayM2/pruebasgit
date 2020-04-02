<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Cart
 */


namespace Amasty\Cart\Plugin\DataPost\Block\Sidebar;

use Amasty\Cart\Plugin\DataPost\Replacer;
use Magento\Wishlist\Block\Customer\Sidebar as SidebarWishlist;

class Wishlist extends Replacer
{
    /**
     * @param SidebarWishlist $subject
     * @param string $result
     *
     * @return string
     */
    public function afterToHtml(SidebarWishlist $subject, $result)
    {
        if ($this->helper->isWishlistAjax()) {
            $this->dataPostReplace($result);
        }

        return $result;
    }
}
