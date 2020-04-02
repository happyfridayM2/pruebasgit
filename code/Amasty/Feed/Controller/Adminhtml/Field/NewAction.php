<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */


namespace Amasty\Feed\Controller\Adminhtml\Field;

use Amasty\Feed\Controller\Adminhtml\AbstractField;

/**
 * Class NewAction
 *
 * @package Amasty\Feed
 */
class NewAction extends AbstractField
{
    public function execute()
    {
        $this->_forward('edit');
    }
}
