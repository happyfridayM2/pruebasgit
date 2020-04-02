<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Xnotif
 */


namespace Amasty\Xnotif\Model\Notification;

use Amasty\Xnotif\Helper\Config;
use Amasty\Xnotif\Model\ResourceModel\Inventory as InventoryResolver;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\App\Area;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Layout;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;

class LowStockAlert
{
    const XML_PATH_EMAIL_TO = 'admin_notifications/stock_alert_email';

    const XML_PATH_SENDER_EMAIL = 'admin_notifications/sender_email_identity';

    const TEMPLATE_FILE = 'Amasty_Xnotif::notifications/low_stock_alert.phtml';

    /**
     * @var Config
     */
    private $config;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var TransportBuilder
     */
    private $transportBuilder;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var Layout
     */
    private $layout;

    /**
     * @var InventoryResolver
     */
    private $inventoryResolver;

    public function __construct(
        Config $config,
        StoreManagerInterface $storeManager,
        TransportBuilder $transportBuilder,
        ProductRepositoryInterface $productRepository,
        LoggerInterface $logger,
        Layout $layout,
        InventoryResolver $inventoryResolver
    ) {
        $this->config = $config;
        $this->storeManager = $storeManager;
        $this->transportBuilder = $transportBuilder;
        $this->productRepository = $productRepository;
        $this->logger = $logger;
        $this->layout = $layout;
        $this->inventoryResolver = $inventoryResolver;
    }

    /**
     * @param array $items
     * @param null|string $sourceCode
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function notify($items, $sourceCode = null)
    {
        $emailTo = $this->getEmailTo();
        $sender = $this->config->getModuleConfig(self::XML_PATH_SENDER_EMAIL);

        if ($this->config->isLowStockNotifications() && $emailTo && $sender) {
            $storeId = $this->storeManager->getStore()->getId();
            $products = $this->getLowStockItems($items, $storeId, $sourceCode);

            if (empty($products)) {
                return;
            }

            try {
                $lowStockHtml = $this->getLowStockHtml($products);

                if ($lowStockHtml) {
                    $transport = $this->transportBuilder->setTemplateIdentifier(
                        $this->config->getModuleConfig('admin_notifications/notify_low_stock_template')
                    )->setTemplateOptions(
                        ['area' => Area::AREA_FRONTEND, 'store' => $storeId]
                    )->setTemplateVars([
                        'alertGrid' => $lowStockHtml,
                        'sourceName' => $sourceCode ? $this->inventoryResolver->getSourceName($sourceCode) : null
                    ])->setFrom(
                        $sender
                    )->addTo(
                        $emailTo
                    )->getTransport();
                    $transport->sendMessage();
                }
            } catch (\Exception $e) {
                $this->logger->critical($e);
            }
        }
    }

    /**
     * @return array|mixed
     */
    protected function getEmailTo()
    {
        $emailTo = $this->config->getModuleConfig(self::XML_PATH_EMAIL_TO);

        if (strpos($emailTo, ',') !== false) {
            /*
             * It's done to bypass the Magento 2.3.3 bug, which makes it impossible to add an array
             * of mail recipients until you add one recipient
             */
            $emailTo = array_map('trim', explode(',', $emailTo));
            $firstReceiver = array_shift($emailTo);
            $this->transportBuilder->addTo($firstReceiver);
        }

        return $emailTo;
    }

    /**
     * @param array $products
     *
     * @return string
     */
    protected function getLowStockHtml($products)
    {
        /** @var Template $lowStockAlert */
        $lowStockAlert = $this->layout->createBlock(Template::class)
            ->setTemplate(self::TEMPLATE_FILE)
            ->setData('lowStockProducts', $products);

        return trim($lowStockAlert->toHtml());
    }

    /**
     * @param array $items
     * @param int $storeId
     * @param null|string $sourceCode
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function getLowStockItems($items, $storeId, $sourceCode = null)
    {
        $products = [];

        foreach ($items as $lowStockItem) {
            if (!$storeId) {
                $storeId = $lowStockItem->getStoreId();
            }

            $product = $lowStockItem->getData('sku')
                ? $lowStockItem
                : $this->initProduct($lowStockItem->getProductId(), $storeId);
            $leftQty = $sourceCode
                ? $this->inventoryResolver->getQtyBySource($product->getData('sku'), $sourceCode)
                : $lowStockItem->getQty();

            if ($leftQty < $this->config->getQuantityBelow($storeId)) {
                $products[] = [
                    'name' => $product->getName(),
                    'sku' => $product->getData('sku'),
                    'qty' => $leftQty
                ];
            }
        }

        return $products;
    }

    /**
     * @param int $productId
     * @param int $storeId
     *
     * @return \Magento\Catalog\Api\Data\ProductInterface
     */
    protected function initProduct($productId, $storeId)
    {
        return $this->productRepository->getById(
            $productId,
            false,
            $storeId
        );
    }
}
