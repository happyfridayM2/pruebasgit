<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_ShopbyBrand
 */


namespace Amasty\ShopbyBrand\Setup\Operation;

use Amasty\ShopbyBrand\Block\Widget\BrandListAbstract;
use Magento\Framework\App\Cache\Type\Config as ConfigCache;
use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\Config\Storage\WriterInterface as ConfigWriter;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

class DropStoreSpecificBrandAttributeSettings
{
    /**
     * @var ConfigWriter
     */
    private $configWriter;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var TypeListInterface
     */
    private $cacheTypeList;

    public function __construct(
        ConfigWriter $configWriter,
        StoreManagerInterface $storeManager,
        TypeListInterface $cacheTypeList
    ) {
        $this->configWriter = $configWriter;
        $this->storeManager = $storeManager;
        $this->cacheTypeList = $cacheTypeList;
    }

    public function execute()
    {
        foreach ($this->storeManager->getStores() as $store) {
            $this->configWriter->delete(
                BrandListAbstract::PATH_BRAND_ATTRIBUTE_CODE,
                ScopeInterface::SCOPE_STORES,
                $store->getId()
            );
        }

        $this->cacheTypeList->invalidate(ConfigCache::TYPE_IDENTIFIER);
    }
}
