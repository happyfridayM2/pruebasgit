<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */


namespace Amasty\Feed\Model\Category;

use Amasty\Feed\Model\Category\ResourceModel\Taxonomy as ResourceTaxonomy;

/**
 * Class Taxonomy
 *
 * @package Amasty\Feed
 */
class Taxonomy extends \Magento\Framework\Model\AbstractModel
{
    public function _construct()
    {
        $this->_init(ResourceTaxonomy::class);
    }
}
