<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Shopby
 */

// phpcs:ignoreFile

namespace Amasty\ShopbyBase\Test\Integration;

use PHPUnit\Framework\TestCase;
use Magento\Framework\View\Layout;
use Magento\Framework\View\LayoutFactory;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\Framework\App\Cache\Type\Layout as LayoutCache;

class SettingOptionPopupDeprecatedTest extends TestCase
{
    /**
     * @var Layout
     */
    protected $layout;

    /**
     * @var string
     */
    protected $shopbyDirectory;

    protected function setUp()
    {
        $objectManager = Bootstrap::getObjectManager();
        $this->layout = $objectManager->get(LayoutFactory::class)->create();
        $this->shopbyDirectory = $objectManager->get('Magento\Framework\Module\Dir\Reader')
            ->getModuleDir('view', 'Amasty_Shopby');
        $objectManager->get(LayoutCache::class)->clean();
    }

    public function testOptionDeprecatedPopup()
    {
        $filePath = $this->shopbyDirectory . '/adminhtml/layout/amshopby_option_option_settings.xml';
        $this->layout->setXml(simplexml_load_file($filePath, Layout\Element::class));
        $this->layout->generateElements();
        $result = $this->layout->getAllBlocks();
        $this->assertEmpty($result, 'This layout is deprecated and should be empty.');
    }
}
