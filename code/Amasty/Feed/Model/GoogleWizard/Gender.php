<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */


namespace Amasty\Feed\Model\GoogleWizard;

/**
 * Class Gender
 */
class Gender extends Element
{
    protected $type = 'attribute';

    protected $tag = 'g:gender';

    protected $modify = 'html_escape';

    protected $name = 'gender';

    protected $description = 'Gender of the item';
}
