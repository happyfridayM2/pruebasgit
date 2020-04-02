<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Shopby
 */


namespace Amasty\Shopby\Plugin\Cms\Model;

use Magento\Framework\Exception\NoSuchEntityException;
use Amasty\Shopby\Model\Cms\Page as AmastyCmsPage;

/**
 * Class Page
 * @package Amasty\Shopby\Plugin\Cms\Model
 */
class Page
{
    /**
     * @var \Amasty\Shopby\Model\Cms\PageFactory
     */
    private $pageFactory;

    /**
     * @var \Amasty\Shopby\Api\CmsPageRepositoryInterface
     */
    private $pageRepository;

    /**
     * @var array
     */
    private $pageData = [];

    /**
     * Page constructor.
     * @param \Amasty\Shopby\Model\Cms\PageFactory $pageFactory
     * @param \Amasty\Shopby\Api\CmsPageRepositoryInterface $cmsPageRepository
     */
    public function __construct(
        \Amasty\Shopby\Model\Cms\PageFactory $pageFactory,
        \Amasty\Shopby\Api\CmsPageRepositoryInterface $cmsPageRepository
    ) {
        $this->pageFactory = $pageFactory;
        $this->pageRepository = $cmsPageRepository;
    }

    /**
     * @param \Magento\Cms\Model\Page $page
     * @param \Closure $proceed
     * @param string $key
     * @param null $index
     * @return mixed
     */
    public function aroundGetData(
        \Magento\Cms\Model\Page $page,
        \Closure $proceed,
        $key = '',
        $index = null
    ) {
        $data = $proceed($key, $index);
        if ($this->isAddAmastyPageData($page, $key, $data)) {
            $data[AmastyCmsPage::VAR_SETTINGS] = $this->getAmastyPageData($page->getId());
        }

        return $data;
    }

    /**
     * @param \Magento\Cms\Model\Page $page
     * @param string $key
     * @param mixed $data
     * @return bool
     */
    private function isAddAmastyPageData(\Magento\Cms\Model\Page $page, $key, $data)
    {
        $isPageDataNeeded = $key === '' || $key === AmastyCmsPage::VAR_SETTINGS;
        $isFirstCall = !(is_array($data) && array_key_exists(AmastyCmsPage::VAR_SETTINGS, $data));
        return $isPageDataNeeded && $isFirstCall && $page->getId();
    }

    /**
     * @param int $pageId
     * @return array
     */
    private function getAmastyPageData($pageId)
    {
        if (!array_key_exists($pageId, $this->pageData)) {
            $this->pageData[$pageId] = [];
            try {
                $shopbyPage = $this->pageRepository->getByPageId($pageId);
                if ($shopbyPage->getId()) {
                    $this->pageData[$pageId] = $shopbyPage->getData();
                }
            } catch (NoSuchEntityException $e) {
                return $this->pageData[$pageId];
            }
        }

        return $this->pageData[$pageId];
    }

    /**
     * @param \Magento\Cms\Model\Page $page
     * @param \Magento\Cms\Model\Page $returnPage
     * @return \Magento\Cms\Model\Page
     */
    public function afterSave(
        \Magento\Cms\Model\Page $page,
        \Magento\Cms\Model\Page $returnPage
    ) {
        if (!$page instanceof \Amasty\Xlanding\Model\Page) {
            $settings = $returnPage->getData();
            if (isset($settings[AmastyCmsPage::VAR_SETTINGS]) && is_array($settings[AmastyCmsPage::VAR_SETTINGS])) {
                try {
                    $shopbyPage = $this->pageRepository->getByPageId($page->getId());
                } catch (NoSuchEntityException $e) {
                    $shopbyPage = $this->pageFactory->create();
                }
                $shopbyPage->setData(array_merge(['page_id' => $page->getId()], $settings[AmastyCmsPage::VAR_SETTINGS]));
                $this->pageRepository->save($shopbyPage);
            }
        }

        return $returnPage;
    }
}
