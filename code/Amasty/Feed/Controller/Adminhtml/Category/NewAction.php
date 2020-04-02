<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */


namespace Amasty\Feed\Controller\Adminhtml\Category;

use Amasty\Feed\Controller\Adminhtml\AbstractCategory;

/**
 * Class NewAction
 *
 * @package Amasty\Feed
 */
class NewAction extends AbstractCategory
{
    public function execute()
    {
        $this->_forward('edit');
    }
}
