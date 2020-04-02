<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Xnotif
 */
namespace Amasty\Xnotif\Plugins\ConfigurableProduct\Block\Product\View\Type;

use Magento\Catalog\Model\Product\Attribute\Source\Status;

/**
 * Class Configurable
 */
class Configurable
{
    /**
     * @var \Magento\Framework\Module\Manager
     */
    private $moduleManager;

    /**
     * @var \Magento\Framework\Json\EncoderInterface
     */
    private $jsonEncoder;

    /**
     * @var \Magento\Framework\Registry
     */
    private $registry;

    /**
     * @var array
     */
    private $allProducts = [];

    public function __construct(
        \Magento\Framework\Module\Manager $moduleManager,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Magento\Framework\Registry $registry
    ) {
        $this->moduleManager = $moduleManager;
        $this->jsonEncoder = $jsonEncoder;
        $this->registry = $registry;
    }

    /**
     * @param $subject
     * @return mixed
     */
    public function beforeGetAllowProducts($subject)
    {
        if (!$subject->hasAllowProducts()) {
            $subject->setAllowProducts($this->getAllProducts($subject));
        }

        return $subject->getData('allow_products');
    }

    /**
     * @param $subject
     * @param $html
     * @return string
     */
    public function afterFetchView($subject, $html)
    {
        $configurableLayout = ['product.info.options.configurable', 'product.info.options.swatches'];
        if (in_array($subject->getNameInLayout(), $configurableLayout)
            && !$this->moduleManager->isEnabled('Amasty_Stockstatus')
            && !$this->registry->registry('amasty_xnotif_initialization')
        ) {
            $this->registry->register('amasty_xnotif_initialization', 1);

            /*move creating code to Amasty\Xnotif\Plugins\ConfigurableProduct\Data */
            $aStockStatus = $this->registry->registry('amasty_xnotif_data');
            $aStockStatus['changeConfigurableStatus'] = true;
            $data = $this->jsonEncoder->encode($aStockStatus);

            $html
                = '<script type="text/x-magento-init">
                    {
                        ".product-options-wrapper": {
                                    "amnotification": {
                                        "xnotif": ' . $data . '
                                    }
                         }
                    }
                   </script>' . $html;
        }

        return $html;
    }

    /**
     * @param $subject
     * @return mixed
     */
    private function getAllProducts($subject)
    {
        $productId = $subject->getProduct()->getId();

        if (!isset($this->allProducts[$productId])) {
            $products = [];
            $allProducts = $subject->getProduct()->getTypeInstance(true)
                ->getUsedProducts($subject->getProduct());
            foreach ($allProducts as $product) {
                if ($product->getStatus() == Status::STATUS_ENABLED) {
                    $products[] = $product;
                }
            }
            $this->allProducts[$productId] = $products;
        }

        return $this->allProducts[$productId];
    }
}
