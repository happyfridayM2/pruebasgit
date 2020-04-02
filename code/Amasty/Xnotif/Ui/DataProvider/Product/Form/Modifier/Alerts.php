<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Xnotif
 */


namespace Amasty\Xnotif\Ui\DataProvider\Product\Form\Modifier;

use Magento\Framework\Stdlib\ArrayManager;

/**
 * Class Alerts
 */
class Alerts extends \Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\AbstractModifier
{
    /**
     * @var ArrayManager
     */
    private $arrayManager;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var \Magento\Framework\View\LayoutFactory
     */
    private $layoutFactory;

    /**
     * @var \Amasty\Base\Model\MagentoVersion
     */
    private $magentoVersion;

    public function __construct(
        ArrayManager $arrayManager,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\View\LayoutFactory $layoutFactory,
        \Amasty\Base\Model\MagentoVersion $magentoVersion
    ) {
        $this->arrayManager = $arrayManager;
        $this->scopeConfig = $scopeConfig;
        $this->layoutFactory = $layoutFactory;
        $this->magentoVersion = $magentoVersion;
    }

    /**
     * @param array $data
     *
     * @return array
     */
    public function modifyData(array $data)
    {
        return $data;
    }

    /**
     * @return bool
     */
    public function canShowTab()
    {
        $alertPriceAllow = $this->scopeConfig->getValue(
            'catalog/productalert/allow_price',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        $alertStockAllow = $this->scopeConfig->getValue(
            'catalog/productalert/allow_stock',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );

        return ($alertPriceAllow || $alertStockAllow);
    }

    /**
     * @param array $meta
     *
     * @return array
     */
    public function modifyMeta(array $meta)
    {
        /* before magento 2.2.3 tab was missed https://github.com/magento/magento2/issues/10007*/
        if (!$this->canShowTab()
            || version_compare($this->magentoVersion->get(), '2.2.3', '>=')
        ) {
            return $meta;
        }

        $panelConfig['arguments']['data']['config'] = [
            'componentType' => 'fieldset',
            'label' => 'Product Alerts',
            'additionalClasses' => 'admin__fieldset-section',
            'collapsible' => true,
            'opened' => false,
            'dataScope' => 'data',
        ];

        $information['arguments']['data']['config'] = [
            'componentType' => 'container',
            'component' => 'Magento_Ui/js/form/components/html',
            'additionalClasses' => 'admin__fieldset-note',
            'content' => $this->layoutFactory->create()->createBlock(
                \Magento\Catalog\Block\Adminhtml\Product\Edit\Tab\Alerts\Stock::class
            )->toHtml() . '<br />',
        ];

        $panelConfig = $this->arrayManager->set(
            'children',
            $panelConfig,
            [
                'information_links' => $information,
            ]
        );

        return $this->arrayManager->set('product_alerts', $meta, $panelConfig);
    }
}
