<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Shopby
 */


namespace Amasty\Shopby\Plugin\Store;

use \Magento\Framework\App\ActionInterface;
use \Magento\Framework\App\ProductMetadataInterface;

/**
 * Class Switcher
 * @package Amasty\Shopby\Plugin\Store
 */
class Switcher
{
    const STORE_PARAM_NAME = '___store';

    /**
     * @var \Amasty\ShopbyBase\Api\UrlBuilderInterface
     */
    private $urlBuilder;

    /**
     * @var \Magento\Framework\Url\EncoderInterface
     */
    private $encoder;

    /**
     * @var \Magento\Framework\Data\Helper\PostHelper
     */
    private $postHelper;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var ProductMetadataInterface
     */
    private $productMetadata;

    public function __construct(
        \Amasty\ShopbyBase\Api\UrlBuilderInterface $urlBuilder,
        \Magento\Framework\Url\EncoderInterface $encoder,
        \Magento\Framework\Data\Helper\PostHelper $postHelper,
        ProductMetadataInterface $productMetadata,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->urlBuilder = $urlBuilder;
        $this->encoder = $encoder;
        $this->postHelper = $postHelper;
        $this->storeManager = $storeManager;
        $this->productMetadata = $productMetadata;
    }

    /**
     * @param \Magento\Store\Block\Switcher $subject
     * @param \Closure $closure
     * @param $store
     * @param array $data
     * @return false|string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function aroundGetTargetStorePostData(
        \Magento\Store\Block\Switcher $subject,
        \Closure $closure,
        \Magento\Store\Model\Store $store,
        $data = []
    ) {
        $params['_current'] = true;
        $params['_use_rewrite'] = true;
        $params['_query'] = ['_' => null, 'shopbyAjax' => null, 'amshopby' => null];
        $params['_scope'] = $store;

        $currentUrl = $this->urlBuilder->getUrl('*/*/*', $params);
        $data[self::STORE_PARAM_NAME] = $store->getCode();
        $data['___from_store'] = $this->storeManager->getStore()->getCode();
        $data[ActionInterface::PARAM_NAME_URL_ENCODED] = $this->encoder->encode($currentUrl);

        $url = $subject->getUrl('stores/store/redirect');
        if ($this->isOldMagentoVersion()) {
            $url = $currentUrl;
        }

        return $this->postHelper->getPostData($url, $data);
    }

    /**
     * @return bool
     */
    protected function isOldMagentoVersion()
    {
        return version_compare($this->productMetadata->getVersion(), '2.2.6', '<');
    }
}
