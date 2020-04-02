<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */


namespace Amasty\Feed\Controller\Adminhtml\Feed;

use Amasty\Feed\Controller\Adminhtml\AbstractFeed;

/**
 * Class NewAction
 *
 * @package Amasty\Feed
 */
class NewAction extends AbstractFeed
{
    /**
     * @inheritdoc
     */
    public function execute()
    {
        $this->_forward('edit');
    }
}
