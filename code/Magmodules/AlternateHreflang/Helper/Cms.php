<?php
/**
 * Copyright © 2019 Magmodules.eu. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magmodules\AlternateHreflang\Helper;

use Magento\Framework\Registry;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Cms\Model\Page as CmsPage;
use Magento\Cms\Model\ResourceModel\Page\CollectionFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magmodules\AlternateHreflang\Helper\General as GeneralHelper;
use Magento\Framework\App\Request\Http as HttpRequest;

class Cms extends AbstractHelper
{

    /**
     * @var General
     */
    private $generalHelper;
    /**
     * @var Registry
     */
    private $registry;
    /**
     * @var CmsPage
     */
    private $cmsPage;
    /**
     * @var CollectionFactory
     */
    private $pageCollectionFactory;
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var HttpRequest
     */
    private $request;

    /**
     * Cms constructor.
     *
     * @param Context               $context
     * @param General               $generalHelper
     * @param CmsPage               $cmsPage
     * @param CollectionFactory     $pageCollectionFactory
     * @param StoreManagerInterface $storeManager
     * @param Registry              $registry
     * @param HttpRequest           $request
     */
    public function __construct(
        Context $context,
        GeneralHelper $generalHelper,
        CmsPage $cmsPage,
        CollectionFactory $pageCollectionFactory,
        StoreManagerInterface $storeManager,
        Registry $registry,
        HttpRequest $request
    ) {
        parent::__construct($context);
        $this->generalHelper = $generalHelper;
        $this->registry = $registry;
        $this->cmsPage = $cmsPage;
        $this->pageCollectionFactory = $pageCollectionFactory;
        $this->storeManager = $storeManager;
        $this->request = $request;
    }

    /**
     * Returns all alternate CMS url's in array
     *
     * @return array|bool
     */
    public function getAlternateData()
    {
        if ($this->request->getFullActionName() == 'cms_index_index') {
            return $this->getAlternateHomepageData();
        }

        $alternateData = [];
        if ($this->generalHelper->getEnabled('cms')) {
            $storeId = $this->generalHelper->getCurrentStore();

            $cmsCategory = $this->cmsPage->getAlternateCategory();
            if (empty($cmsCategory)) {
                $alternateData['error'] = __('There is no Category set for this CMS page.');
                return $alternateData;
            }

            $targetData = $this->generalHelper->getTargetData($storeId);
            if (empty($targetData['group_id'])) {
                $alternateData['error'] = __('No alternate stores URLs found.');
                return $alternateData;
            }

            $groupId = $targetData['group_id'];
            $alternateCmsPages = $this->getAlternateCmsPages($cmsCategory, $storeId);

            foreach ($targetData[$groupId] as $row) {
                if ($storeId != $row['store_id']) {
                    if (isset($alternateCmsPages[$row['store_id']])) {
                        $languageCode = $row['language_code'];
                        $alternateData['urls'][$languageCode] = $alternateCmsPages[$row['store_id']];
                    }
                } else {
                    if (isset($alternateCmsPages[$row['store_id']])) {
                        $languageCode = $row['language_code'];
                        $alternateData['urls'][$languageCode] = $alternateCmsPages[$row['store_id']];
                        $currentAlternate = $alternateData['urls'][$languageCode];
                    }
                }
            }

            if (empty($alternateData['urls'])) {
                $alternateData['error'] = __('No Alternate URL found.');
                return $alternateData;
            }

            if (count($alternateData['urls']) == 1) {
                $alternateData['error'] = __(
                    'Only one Alternate URL Found (%1). Needs at least two.',
                    implode('', $alternateData['urls'])
                );
                return $alternateData;
            }

            $canonical = $this->generalHelper->getCanonicalEnabled();
            $currentUrl = $this->generalHelper->getCurrentUrl(true);
            if (isset($currentAlternate) && $canonical && $currentAlternate != $currentUrl) {
                $alternateData['error'] = __(
                    'Current URL %1 not canonical. Canonical: %2.',
                    $currentUrl,
                    $currentAlternate
                );
                return $alternateData;
            }

            return $alternateData;
        }

        if ($this->generalHelper->getAlternateDebug()) {
            $alternateData['error'] = __('CMS Alternate Data not enabled.');
            return $alternateData;
        }
    }

    /**
     * @return array|bool
     */
    public function getAlternateHomepageData()
    {
        $alternateData = [];

        if ($this->generalHelper->getEnabled('homepage')) {
            $alternateData = [];
            $storeId = $this->generalHelper->getCurrentStore();
            $targetData = $this->generalHelper->getTargetData($storeId);

            if (empty($targetData['group_id'])) {
                return false;
            }

            $groupId = $targetData['group_id'];
            $currentUrl = $this->generalHelper->getCurrentUrl(true);
            foreach ($targetData[$groupId] as $row) {
                if ($storeId != $row['store_id']) {
                    $url = $this->storeManager->getStore($row['store_id'])->getBaseUrl();
                    $languageCode = $row['language_code'];
                    $alternateData['urls'][$languageCode] = $url;
                } else {
                    $url = $currentUrl;
                    $languageCode = $row['language_code'];
                    $alternateData['urls'][$languageCode] = $url;
                }
            }

            if (empty($alternateData['urls'])) {
                $alternateData['error'] = __('No Alternate URLs found..');
                return $alternateData;
            }

            if (count($alternateData['urls']) == 1) {
                $alternateData['error'] = __(
                    'Only one Alternate URL Found (%1). Needs at least two.',
                    implode('', $alternateData['urls'])
                );
                return $alternateData;
            }

            $canonical = $this->generalHelper->getCanonicalEnabled();
            $currentUrl = $this->generalHelper->getCurrentUrl(true);
            if (isset($currentAlternate) && $canonical && $currentAlternate != $currentUrl) {
                $alternateData['error'] = __(
                    'Current URL %1 not canonical. Canonical: %2.',
                    $currentUrl,
                    $currentAlternate
                );
                return $alternateData;
            }

            return $alternateData;
        }

        if ($this->generalHelper->getAlternateDebug()) {
            $alternateData['error'] = __('Homepage Alternate Data not enabled.');
            return $alternateData;
        }
    }

    /**
     * @param $cmsCategory
     *
     * @return array
     */
    public function getAlternateCmsPages($cmsCategory)
    {
        $alternates = [];
        $pages = $this->pageCollectionFactory->create()
            ->addFieldToFilter('alternate_category', ['eq' => $cmsCategory]);

        foreach ($pages as $page) {
            foreach ($page->getStoreId() as $storeId) {
                $baseUrl = $this->getBaseUrl($storeId);
                $alternates[$storeId] = $baseUrl . $page->getIdentifier();
            }
        }

        return $alternates;
    }

    /**
     * @param $storeId
     *
     * @return mixed
     */
    public function getBaseUrl($storeId)
    {
        return $this->storeManager->getStore($storeId)->getBaseUrl();
    }
}
