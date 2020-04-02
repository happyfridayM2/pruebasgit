<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Shopby
 */

// phpcs:ignoreFile

namespace Amasty\Shopby\Test\Integration;

use Magento\TestFramework\TestCase\AbstractController;

class AttributePatternSeoTest extends AbstractController
{
    /**
     * @var string
     */
    private static $filteredPageBody;

    /**
     * @var array
     */
    private $param = ['value' => 196, 'seo_link' => '/graphic-print'];

    /**
     * @magentoConfigFixture current_store amasty_shopby_seo/url/mode 1
     * @magentoConfigFixture current_store amshopby_root/general/enabled 1
     * @magentoDataFixture ../../../../app/code/Amasty/Shopby/Test/_files/seofy_attribute_pattern.php
     */
    public function testGraphicPrintSeoLink()
    {
        $this->dispatch('amshopby/index/index');
        $body = $this->getResponse()->getBody();
        $expectedLink = 'href="http://localhost/index.php' . $this->param['seo_link'];
        $this->assertContains($expectedLink, $body, 'seo link not found');
    }

    /**
     * @magentoConfigFixture current_store amasty_shopby_seo/url/mode 1
     * @magentoConfigFixture current_store amshopby_root/general/enabled 1
     * @magentoDataFixture ../../../../app/code/Amasty/Shopby/Test/_files/seofy_attribute_pattern.php
     */
    public function testGraphicPrintSeoPage()
    {
        $pattern = sprintf(
            '/li class="item am-shopby-item" [0-9a-zA-Z\-\=\" ]* data-value="%d"/',
            $this->param['value']
        );

        $this->assertRegexp($pattern, $this->getFilteredPageBody(), 'seo url is not valid');
    }

    /**
     * @magentoConfigFixture current_store amasty_shopby_seo/url/mode 1
     * @magentoConfigFixture current_store amshopby_root/general/enabled 1
     * @magentoDataFixture ../../../../app/code/Amasty/Shopby/Test/_files/seofy_attribute_pattern.php
     */
    public function testGraphicPrintProductCount()
    {
        $this->assertEquals(
            1,
            substr_count($this->getFilteredPageBody(), 'item product product-item'),
            'the amount of products should be 1'
        );
    }

    /**
     * @magentoConfigFixture current_store amasty_shopby_seo/url/mode 1
     * @magentoConfigFixture current_store amshopby_root/general/enabled 1
     * @magentoDataFixture ../../../../app/code/Amasty/Shopby/Test/_files/seofy_attribute_pattern.php
     */
    public function testGraphicPrintOptionProductList()
    {
        $this->assertContains('href="http://localhost/index.php/erika-running-short.html"',
            $this->getFilteredPageBody(),
            'Erika Running Short expected'
        );
    }

    /**
     * @return string
     */
    private function getFilteredPageBody()
    {
        if (self::$filteredPageBody === null) {
            $this->dispatch($this->param['seo_link']);
            $body = $this->getResponse()->getBody();
            self::$filteredPageBody = str_replace(["\r", "\n"], '', $body);
        }

        return self::$filteredPageBody;
    }
}

