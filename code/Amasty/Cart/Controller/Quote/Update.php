<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Cart
 */


namespace Amasty\Cart\Controller\Quote;

use Amasty\Cart\Controller\Cart\Update as CartUpdate;
use Amasty\Cart\Model\Source\Section;

/**
 * Controller used when Request A Quote module installed
 *
 * Class Update
 */
class Update extends CartUpdate
{
    /**
     * @var string
     */
    protected $type = Section::QUOTE;
}
