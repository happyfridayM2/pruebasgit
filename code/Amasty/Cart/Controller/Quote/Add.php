<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Cart
 */


namespace Amasty\Cart\Controller\Quote;

use Amasty\Cart\Controller\Cart\Add as CartAdd;
use Amasty\Cart\Model\Source\Section;

/**
 * Controller used when Request A Quote module installed
 *
 * Class Add
 */
class Add extends CartAdd
{
    /**
     * @var string
     */
    protected $type = Section::QUOTE;
}
