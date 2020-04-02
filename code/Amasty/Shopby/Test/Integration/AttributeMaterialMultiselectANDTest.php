<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Shopby
 */

// phpcs:ignoreFile

namespace Amasty\Shopby\Test\Integration;

use \Magento\TestFramework\TestCase\AbstractController;

class AttributeMaterialMultiselectANDTest extends AbstractController
{
    /**
     * @var string
     */
    private static $nextParam;

    /**
     * @var \DOMDocument
     */
    private $dom;

    /**
     * @magentoConfigFixture current_store amshopby_root/general/enabled 1
     * @magentoDataFixture ../../../../app/code/Amasty/Shopby/Test/_files/multiselect_attribute_material.php
     */
    public function testClearPage()
    {
        $this->dispatch('amshopby/index/index');
        $materialOptionCount = $this->getMaterialOptionsCount();
        $this->assertEquals(31, $materialOptionCount, 'Material filter options amount doesn\'t match');
    }

    /**
     * @magentoConfigFixture current_store amshopby_root/general/enabled 1
     * @magentoDataFixture ../../../../app/code/Amasty/Shopby/Test/_files/multiselect_attribute_material.php
     */
    public function test1FilterPage()
    {
        $this->getRequest()->setParam('material', self::$nextParam);
        $this->dispatch('amshopby/index/index');
        $materialOptionCount = $this->getMaterialOptionsCount();
        $productsCount = $this->getProductsCount();

        $this->assertEquals(6, $materialOptionCount, 'Material filter options amount doesn\'t match');
        $this->assertEquals(3, $productsCount, 'Products amount doesn\'t match');
    }


    /**
     * @magentoConfigFixture current_store amshopby_root/general/enabled 1
     * @magentoDataFixture ../../../../app/code/Amasty/Shopby/Test/_files/multiselect_attribute_material.php
     */
    public function test2FiltersPage()
    {
        $this->getRequest()->setParam('material', self::$nextParam);
        $this->dispatch('amshopby/index/index');
        $materialOptionCount = $this->getMaterialOptionsCount();
        $productsCount = $this->getProductsCount();

        $this->assertEquals(4, $materialOptionCount, 'Material filter options amount doesn\'t match');
        $this->assertEquals(1, $productsCount, 'Products amount doesn\'t match');
    }

    /**
     * @return int
     */
    private function getMaterialOptionsCount()
    {
        $result = $this->getHtmlDom();
        $materialOptionCount = 0;
        foreach ($result->getElementsByTagName('form') as $form) {
            if ($form->getAttribute('data-amshopby-filter') == 'attr_material') {
                foreach($form->childNodes as $node) {
                    if (!($node instanceof \DomText))
                        $materialOptionCount++;
                }

                $url = $form->childNodes[5]->childNodes[1]->getAttribute('href');
                $parts = parse_url($url);
                parse_str($parts['query'], $query);
                if (isset($query['material'])) {
                    self::$nextParam = $query['material'];
                }

                break;
            }
        }

        return $materialOptionCount;
    }

    /**
     * @return int
     */
    private function getProductsCount()
    {
        $result = $this->getHtmlDom();
        $productCount = 0;
        foreach ($result->getElementsByTagName('ol') as $list) {
            if ($list->getAttribute('class') == 'products list items product-items') {
                foreach($list->childNodes as $node) {
                    if (!($node instanceof \DomText) && $node->getAttribute('class') == 'item product product-item') {
                        $productCount++;
                    }
                }

                break;
            }
        }

        return $productCount;
    }

    /**
     * @return \DOMDocument
     */
    private function getHtmlDom()
    {
        if ($this->dom === null) {
            libxml_use_internal_errors(true);
            $this->dom = new \DOMDocument();
            $this->dom->preserveWhiteSpace = false;
            $this->dom->loadHTML($this->getResponse()->getBody());
            libxml_use_internal_errors(false);
        }

        return $this->dom;
    }
}

