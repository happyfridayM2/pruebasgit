<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Shopby
 */

namespace Amasty\Shopby\Api;

use Magento\Framework\Exception\NoSuchEntityException;

interface CmsPageRepositoryInterface
{
    /**
     * @param int $pageId
     * @return \Amasty\Shopby\Model\Cms\Page
     * @throws NoSuchEntityException
     */
    public function get($pageId);

    /**
     * @param int $pageId
     * @return \Amasty\Shopby\Model\Cms\Page
     * @throws NoSuchEntityException
     */
    public function getByPageId($pageId);

    /**
     * @param \Amasty\Shopby\Model\Cms\Page $page
     * @return \Amasty\Shopby\Model\Cms\Page
     */
    public function save($page);
}
