<?php
/**
 * Copyright © 2019 Magmodules.eu. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magmodules\AlternateHreflang\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\Request\Http;
use Magento\Catalog\Api\Data\CategoryInterface;
use Magento\Catalog\Model\CategoryRepository;
use Magento\Framework\Registry;
use Magento\Framework\App\Area;
use Magento\Store\Model\App\Emulation;
use Psr\Log\LoggerInterface;
use Magmodules\AlternateHreflang\Helper\General as GeneralHelper;

/**
 * Class Category
 *
 * @package Magmodules\AlternateHreflang\Helper
 */
class Category extends AbstractHelper
{

    const UNSET_PARAMS = ['id', 'show-alternate', 'am_base_price'];

    /**
     * @var General
     */
    private $generalHelper;

    /**
     * @var CategoryRepository
     */
    private $categoryRepository;

    /**
     * @var Registry
     */
    private $registry;

    /**
     * @var Http
     */
    private $request;

    /**
     * @var Emulation
     */
    private $appEmulation;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * Category constructor.
     *
     * @param Context            $context
     * @param CategoryRepository $categoryRepository
     * @param General            $generalHelper
     * @param Registry           $registry
     * @param Http               $request
     * @param Emulation          $appEmulation
     */
    public function __construct(
        Context $context,
        CategoryRepository $categoryRepository,
        GeneralHelper $generalHelper,
        Registry $registry,
        Http $request,
        Emulation $appEmulation
    ) {
        parent::__construct($context);
        $this->categoryRepository = $categoryRepository;
        $this->generalHelper = $generalHelper;
        $this->registry = $registry;
        $this->request = $request;
        $this->appEmulation = $appEmulation;
        $this->logger = $context->getLogger();
    }

    /**
     * Returns all alternate category url's in array
     *
     * @return array|bool
     */
    public function getAlternateData()
    {
        if ($this->generalHelper->getEnabled('category')) {
            $alternateData = [];
            $storeId = $this->generalHelper->getCurrentStore();
            $targetData = $this->generalHelper->getTargetData($storeId);

            if (empty($targetData['group_id'])) {
                return false;
            }

            $groupId = $targetData['group_id'];
            $categoryId = $this->getCurrentCategory()->getId();
            $storeIds = $this->getCurrentCategory()->getStoreIds();
            $params = $this->getCanonicalCheck();

            if (count($params) > 0) {
                $paramsImploded = implode(',', array_keys($params));
                $alternateData['error'] = __('It seems that the current Category URLS has filters (%1), 
                the Alternate Hreflang Tags can not be placed on filtered URLS.', $paramsImploded);

                return $alternateData;
            }

            foreach ($targetData[$groupId] as $row) {
                if (!in_array($row['store_id'], $storeIds)) {
                    continue;
                }

                if ($storeId != $row['store_id']) {
                    $url = $this->getCategoryUrlByStore($categoryId, $row['store_id'], $storeId);
                    if ($url) {
                        $languageCode = $row['language_code'];
                        $alternateData['urls'][$languageCode] = $url;
                    }
                } else {
                    $url = $this->getCategoryUrlByStore($categoryId, $row['store_id'], $storeId);
                    $currentAlternate = $url;
                    $languageCode = $row['language_code'];
                    $alternateData['urls'][$languageCode] = $url;
                }
            }

            if (empty($alternateData['urls'])) {
                $alternateData['error'] = __('No Alternate URLs found.');
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
            $alternateData['error'] = __('Category Alternate Data not enabled.');
            return $alternateData;
        }

        return false;
    }

    /**
     * @return CategoryInterface Category
     */
    public function getCurrentCategory()
    {
        return $this->registry->registry('current_category');
    }

    /**
     * @return array
     */
    public function getCanonicalCheck()
    {
        $params = $this->request->getParams();
        foreach (self::UNSET_PARAMS as $param) {
            unset($params[$param]);
        }

        return $params;
    }

    /**
     * @param $categoryId
     * @param $storeId
     * @param $currentStoreId
     *
     * @return mixed|null|string
     */
    public function getCategoryUrlByStore($categoryId, $storeId, $currentStoreId)
    {
        $this->appEmulation->startEnvironmentEmulation($storeId, Area::AREA_FRONTEND, true);

        try {
            $category = $this->categoryRepository->get($categoryId, $storeId);
            if ($category->getIsActive()) {
                $categoryUrl = $category->getUrl();
                $baseUrlCurrent = $this->generalHelper->getBaseUrlStore($currentStoreId);
                $baseUrlTarget = $this->generalHelper->getBaseUrlStore($storeId);
                $url = str_replace($baseUrlCurrent, $baseUrlTarget, $categoryUrl);
            }
        } catch (\Exception $e) {
            $this->logger->critical($e);
        }

        $this->appEmulation->stopEnvironmentEmulation();

        return isset($url) ? strtok($url, '?') : null;
    }
}
