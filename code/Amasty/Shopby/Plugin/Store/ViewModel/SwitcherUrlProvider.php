<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Shopby
 */


namespace Amasty\Shopby\Plugin\Store\ViewModel;

use \Magento\Framework\App\ActionInterface;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Store\Model\Store;
use Amasty\ShopbyBase\Helper\Data;

/**
 * Class SwitcherUrlProvider
 * @package Amasty\Shopby\Plugin\Store\ViewModel
 */
class SwitcherUrlProvider
{
    const STORE_PARAM_NAME = '___store';
    const FROM_STORE_PARAM_NAME = '___from_store';

    /**
     * @var \Amasty\ShopbyBase\Api\UrlBuilderInterface
     */
    private $urlBuilder;

    /**
     * @var \Magento\Framework\Url\EncoderInterface
     */
    private $encoder;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var DataPersistorInterface
     */
    private $dataPersistor;

    public function __construct(
        \Amasty\ShopbyBase\Api\UrlBuilderInterface $urlBuilder,
        \Magento\Framework\Url\EncoderInterface $encoder,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        DataPersistorInterface $dataPersistor
    ) {
        $this->urlBuilder = $urlBuilder;
        $this->encoder = $encoder;
        $this->storeManager = $storeManager;
        $this->dataPersistor = $dataPersistor;
    }

    /**
     * @param $subject
     * @param callable $proceed
     * @param Store $store
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function aroundGetTargetStoreRedirectUrl($subject, callable $proceed, Store $store)
    {
        $params['_current'] = true;
        $params['_use_rewrite'] = true;
        $params['_query'] = ['_' => null, 'shopbyAjax' => null, 'amshopby' => null];
        $params['_scope'] = $store;
        $this->dataPersistor->set(Data::SHOPBY_SWITCHER_STORE_ID, $store->getId());
        $currentUrl = $this->urlBuilder->getUrl('*/*/*', $params);
        $this->dataPersistor->clear(Data::SHOPBY_SWITCHER_STORE_ID);

        return $this->urlBuilder->getUrl(
            'stores/store/redirect',
            [
                self::STORE_PARAM_NAME => $store->getCode(),
                self::FROM_STORE_PARAM_NAME => $this->storeManager->getStore()->getCode(),
                ActionInterface::PARAM_NAME_URL_ENCODED => $this->encoder->encode($currentUrl),
            ]
        );
    }
}
