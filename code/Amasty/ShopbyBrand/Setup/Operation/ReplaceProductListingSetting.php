<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_ShopbyBrand
 */


namespace Amasty\ShopbyBrand\Setup\Operation;

use Amasty\ShopbyBrand\Helper\Data;
use Magento\Framework\App\Config\Storage\WriterInterface as ConfigWriter;

class ReplaceProductListingSetting
{
    /**
     * @var Data
     */
    private $helper;

    public function __construct(
        Data $helper
    ) {
        $this->helper = $helper;
    }

    /**
     * @param ConfigWriter $configWriter
     */
    public function execute(ConfigWriter $configWriter)
    {
        $condition = $this->helper->getModuleConfig('general/show_on_listing');
        $configWriter->save('amshopby_brand/product_listing_settings/show_on_listing', $condition);
    }
}
