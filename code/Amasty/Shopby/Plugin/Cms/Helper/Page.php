<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Shopby
 */


namespace Amasty\Shopby\Plugin\Cms\Helper;

use Magento\Framework\App\Action\Action;
use Magento\Cms\Api\PageRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Class Page
 * @package Amasty\Shopby\Plugin\Cms\Helper
 */
class Page
{
    const LAYER_CMS = 'amshopby_cms';

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var PageRepositoryInterface
     */
    protected $pageRepository;

    /**
     * @var \Amasty\Shopby\Api\CmsPageRepositoryInterface
     */
    protected $shopbyPageRepository;

    /**
     * @var \Amasty\Shopby\Model\Cms\PageFactory
     */
    protected $shopbyPageFactory;

    /**
     * @var \Magento\Catalog\Model\Layer\Resolver
     */
    protected $layerResolver;

    /**
     * CmsPageHelperPlugin constructor.
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param PageRepositoryInterface $pageRepository
     * @param \Amasty\Shopby\Api\CmsPageRepositoryInterface $shopbyPageRepository
     * @param \Amasty\Shopby\Model\Cms\PageFactory $shopbyPageFactory
     * @param \Magento\Catalog\Model\Layer\Resolver $layerResolver
     */
    public function __construct(
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        PageRepositoryInterface $pageRepository,
        \Amasty\Shopby\Api\CmsPageRepositoryInterface $shopbyPageRepository,
        \Amasty\Shopby\Model\Cms\PageFactory $shopbyPageFactory,
        \Magento\Catalog\Model\Layer\Resolver $layerResolver
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->pageRepository = $pageRepository;
        $this->shopbyPageRepository = $shopbyPageRepository;
        $this->shopbyPageFactory = $shopbyPageFactory;
        $this->layerResolver = $layerResolver;
    }

    /**
     * @param \Magento\Cms\Helper\Page $helper
     * @param \Closure $proceed
     * @param Action $action
     * @param null $pageId
     * @return \Magento\Framework\View\Result\Page
     * @SuppressWarnings(PHPMD.UnusedFormatParameter)
     */
    public function aroundPrepareResultPage(
        \Magento\Cms\Helper\Page $helper,
        \Closure $proceed,
        Action $action,
        $pageId = null
    ) {
        $duplicatePageId = $pageId;

        if ($pageId !== null) {
            $delimiterPosition = strrpos($pageId, '|');
            if ($delimiterPosition) {
                $pageId = substr($pageId, 0, $delimiterPosition);
            }
        }

        try {
            $page = $this->pageRepository->getById($pageId);
            $shopbyPage = $this->shopbyPageRepository->getByPageId($page->getId());
        } catch (NoSuchEntityException $e) {
            $shopbyPage = $this->shopbyPageFactory->create();
        }

        if ($shopbyPage->getEnabled()) {
            $this->layerResolver->create(self::LAYER_CMS);
            $resultPage = $this->resultPageFactory->create();
            $resultPage->addHandle('amshopby_cms_navigation');
        }

        return $proceed($action, $duplicatePageId);
    }
}
