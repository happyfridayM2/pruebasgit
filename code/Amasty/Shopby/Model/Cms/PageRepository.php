<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Shopby
 */


namespace Amasty\Shopby\Model\Cms;

use Amasty\Shopby\Api\CmsPageRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Class PageRepository
 * @package Amasty\Shopby\Model\Cms
 */
class PageRepository implements CmsPageRepositoryInterface
{
    /**
     * @var PageFactory
     */
    protected $facory;

    /**
     * @var \Amasty\Shopby\Model\ResourceModel\Cms\Page
     */
    protected $resource;

    /**
     * PageRepository constructor.
     * @param PageFactory $factory
     * @param \Amasty\Shopby\Model\ResourceModel\Cms\Page $resource
     */
    public function __construct(
        PageFactory $factory,
        \Amasty\Shopby\Model\ResourceModel\Cms\Page $resource
    ) {
        $this->facory = $factory;
        $this->resource = $resource;
    }

    /**
     * @param int $pageId
     * @return \Amasty\Shopby\Model\Cms\Page
     * @throws NoSuchEntityException
     */
    public function get($pageId)
    {
        $page = $this->facory->create();
        $this->resource->load($page, $pageId);
        if (!$page->getId()) {
            throw new NoSuchEntityException(__('Requested page doesn\'t exist'));
        }
        return $page;
    }

    /**
     * @param int $pageId
     * @return \Amasty\Shopby\Model\Cms\Page
     * @throws NoSuchEntityException
     */
    public function getByPageId($pageId)
    {
        $page = $this->facory->create();
        $this->resource->load($page, $pageId, 'page_id');
        if (!$page->getId()) {
            throw new NoSuchEntityException(__('Requested page doesn\'t exist'));
        }
        return $page;
    }

    /**
     * @param \Amasty\Shopby\Model\Cms\Page $page
     * @return \Amasty\Shopby\Model\Cms\Page
     */
    public function save($page)
    {
        $this->resource->save($page);
        return $page;
    }
}
