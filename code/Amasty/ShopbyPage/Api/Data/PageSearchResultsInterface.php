<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_ShopbyPage
 */


namespace Amasty\ShopbyPage\Api\Data;

use Magento\Framework\Api\SearchResultsInterface;

interface PageSearchResultsInterface extends SearchResultsInterface
{
    /**
     * @return \Amasty\ShopbyPage\Api\Data\PageInterface[]
     */
    public function getItems();

    /**
     * @param \Amasty\ShopbyPage\Api\Data\PageInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
