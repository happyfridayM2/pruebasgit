<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_ShopbyBrand
 */


namespace Amasty\ShopbyBrand\Setup;

use Amasty\ShopbyBrand\Setup\Operation\DropStoreSpecificBrandAttributeSettings;
use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Cms\Model\PageFactory;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Amasty\ShopbyBase\Api\Data\FilterSettingInterface;
use Magento\UrlRewrite\Service\V1\Data\UrlRewrite;

class UpgradeData implements UpgradeDataInterface
{
    /**
     * @var \Amasty\ShopbyBrand\Helper\Data
     */
    private $brandHelper;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var \Amasty\ShopbyBase\Model\ResourceModel\FilterSetting\CollectionFactory
     */
    private $filterCollectionFactory;

    /**
     * @var PageFactory
     */
    protected $_pageFactory;

    /**
     * @var WriterInterface
     */
    protected $_configWriter;

    /**
     * @var \Amasty\ShopbyBase\Helper\Data
     */
    protected $baseHelper;

    /**
     * @var \Magento\UrlRewrite\Model\UrlFinderInterface
     */
    protected $urlFinder;

    /**
     * @var \Amasty\ShopbyBase\Api\Data\FilterSettingRepositoryInterface
     */
    protected $filterSettingRepository;

    /**
     * @var \Magento\Framework\App\State
     */
    private $appState;

    /**
     * @var Operation\ReplaceProductListingSetting
     */
    private $replaceListingSetting;

    /**
     * @var DropStoreSpecificBrandAttributeSettings
     */
    private $dropStoreSpecificBrandAttributeSettings;

    public function __construct(
        \Magento\Cms\Model\PageFactory $pageFactory,
        WriterInterface $configWriter,
        \Amasty\ShopbyBrand\Helper\Data $brandHelper,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Amasty\ShopbyBase\Model\ResourceModel\FilterSetting\CollectionFactory $filterCollectionFactory,
        \Amasty\ShopbyBase\Helper\Data $baseHelper,
        \Magento\UrlRewrite\Model\UrlFinderInterface $urlFinder,
        \Amasty\ShopbyBase\Api\Data\FilterSettingRepositoryInterface $filterSettingRepository,
        \Magento\Framework\App\State $appState,
        \Amasty\ShopbyBrand\Setup\Operation\ReplaceProductListingSetting $replaceListingSetting,
        DropStoreSpecificBrandAttributeSettings $dropStoreSpecificBrandAttributeSettings
    ) {
        $this->_pageFactory = $pageFactory;
        $this->_configWriter = $configWriter;
        $this->brandHelper = $brandHelper;
        $this->scopeConfig = $scopeConfig;
        $this->filterCollectionFactory = $filterCollectionFactory;
        $this->baseHelper = $baseHelper;
        $this->urlFinder = $urlFinder;
        $this->filterSettingRepository = $filterSettingRepository;
        $this->appState = $appState;
        $this->replaceListingSetting = $replaceListingSetting;
        $this->dropStoreSpecificBrandAttributeSettings = $dropStoreSpecificBrandAttributeSettings;
    }

    /**
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface $context
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $this->appState->emulateAreaCode(
            \Magento\Framework\App\Area::AREA_ADMINHTML,
            [$this, 'upgradeCallback'],
            [$setup, $context]
        );
    }

    /**
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface $context
     */
    public function upgradeCallback(
        ModuleDataSetupInterface $setup,
        ModuleContextInterface $context
    ) {
        $setup->startSetup();

        if (version_compare($context->getVersion(), '1.0.1', '<')) {
            $this->createAllBrandsPage();
        }

        if (!$this->baseHelper->isShopbyInstalled()) {
            if (version_compare($context->getVersion(), '1.0.2', '<')) {
                $this->brandHelper->updateBrandOptions();
            }

            if (version_compare($context->getVersion(), '1.0.3', '<')) {
                $this->setShowBrandIcon();
            }
        }

        if (version_compare($context->getVersion(), '1.0.4', '<')) {
            $this->replaceListingSetting->execute($this->_configWriter);
        }

        if (version_compare($context->getVersion(), '2.10.3', '<')) {
            $this->dropStoreSpecificBrandAttributeSettings->execute();
        }

        $setup->endSetup();
    }

    /**
     * Add cms page for all brands
     * @return void
     */
    protected function createAllBrandsPage()
    {
        $identifier = $this->getIdentifier();
        $content = '<p style="text-align: left;"><span style="font-size: small;">
<strong>Searching for a favorite brand? Browse the list below to find just the label you\'re looking for!</strong>
</span></p><p style="text-align: left;"><span style="font-size: medium;"><strong><br /></strong></span></p>
<p><img src="{{media url="wysiwyg/collection/collection-performance.jpg"}}" alt="" /></p>
<p>{{widget type="Amasty\ShopbyBrand\Block\Widget\BrandSlider" template="widget/brand_list/slider.phtml"}}</p>
<p>{{widget type="Amasty\ShopbyBrand\Block\Widget\BrandList" columns="3" 
template="widget/brand_list/index.phtml"}}</p>';
        $page = $this->_pageFactory->create();
        $page->setTitle('All Brands Page')
            ->setIdentifier($identifier)
            ->setData('mageworx_hreflang_identifier', 'en-us')
            ->setData('mp_exclude_sitemap', '1')
            ->setIsActive(false)
            ->setPageLayout('1column')
            ->setStores([0])
            ->setContent($content)
            ->save();
        $this->_configWriter->save('amshopby_brand/general/brands_page', $identifier);
    }

    /**
     * @param int $index
     * @return string
     */
    protected function getIdentifier($index = 0)
    {
        $identifier = 'brands';
        if ($index) {
            $identifier .= '_' . $index;
        }
        $rewrite = $this->urlFinder->findOneByData([UrlRewrite::REQUEST_PATH => $identifier]);
        if ($rewrite !== null) {
            return $this->getIdentifier(++$index);
        }
        return $identifier;
    }

    /**
     * Update icon for brand attribute
     * @return void
     */
    private function setShowBrandIcon()
    {
        if ($this->scopeConfig->isSetFlag('amshopby_brand/general/product_icon')) {
            $attributeCode = $this->scopeConfig->getValue('amshopby_brand/general/attribute_code');
            $filterCode = \Amasty\ShopbyBase\Helper\FilterSetting::ATTR_PREFIX . $attributeCode;
            $filterCollection = $this->filterCollectionFactory->create();
            /** @var \Amasty\ShopbyBase\Api\Data\FilterSettingInterface $filter */
            $filter = $filterCollection
                ->addFieldToFilter(FilterSettingInterface::FILTER_CODE, $filterCode)
                ->getFirstItem();
            if ($filter && $filter->getId()) {
                $filter->setShowIconsOnProduct(true);
                $this->filterSettingRepository->save($filter);
            }
        }
    }
}
