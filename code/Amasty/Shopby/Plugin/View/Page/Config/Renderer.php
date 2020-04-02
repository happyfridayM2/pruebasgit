<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Shopby
 */


namespace Amasty\Shopby\Plugin\View\Page\Config;

use Magento\Framework\App\ProductMetadataInterface;
use Magento\Framework\View\Page\Config;
use Magento\Framework\View\Page\Config\Renderer as MagentoRenderer;

class Renderer
{

    const SWATCHES_FILE = 'Magento_Swatches::css/swatches.css';

    const UNSUPPORTED_VERSION = '2.3.0';

    /**
     * @var Config
     */
    private $config;

    /**
     * @var ProductMetadataInterface
     */
    private $productMetadata;

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    private $request;

    public function __construct(
        Config $config,
        ProductMetadataInterface $productMetadata,
        \Magento\Framework\App\Request\Http $request
    ) {
        $this->config = $config;
        $this->productMetadata = $productMetadata;
        $this->request = $request;
    }

    /**
     * Here we add the Magento_Swatches css file for Magento version <= 2.2.8
     *
     * @param MagentoRenderer $subject
     * @param array $resultGroups
     * @return array
     */
    public function beforeRenderAssets(
        MagentoRenderer $subject,
        $resultGroups = []
    )
    {
        if ($this->isSupportedVersion() && $this->isNeededAction()) {
            $this->config->addPageAsset(self::SWATCHES_FILE);
        }

        return [$resultGroups];
    }

    /**
     * @return int|bool
     */
    private function isSupportedVersion()
    {
        $version = $this->productMetadata->getVersion();
        $version = str_replace(['-develop', 'dev-'], '', $version);

        return version_compare($version, self::UNSUPPORTED_VERSION, '<');
    }

    /**
     * @return bool
     */
    private function isNeededAction()
    {
        return in_array($this->request->getFullActionName(), [
                'amshopby_cms_navigation',
                'amshopby_option_group_edit'
            ]
        );
    }
}
