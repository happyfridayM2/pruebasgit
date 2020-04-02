<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */


namespace Amasty\Feed\Controller\Adminhtml;

/**
 * Class AbstractFeed
 *
 * @package Amasty\Feed
 */
abstract class AbstractFeed extends \Magento\Backend\App\Action
{
    /**
     * Authorization level of a basic admin action
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Amasty_Feed::feed';
}
