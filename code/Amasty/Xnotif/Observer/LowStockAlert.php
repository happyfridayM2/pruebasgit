<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Xnotif
 */


namespace Amasty\Xnotif\Observer;

use Amasty\Xnotif\Helper\Config;
use Amasty\Xnotif\Model\ResourceModel\Inventory as InventoryResolver;
use Amasty\Xnotif\Model\Notification\LowStockAlert as NotificationModel;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Model\Order\Shipment;
use Psr\Log\LoggerInterface;

class LowStockAlert implements ObserverInterface
{
    /**
     * @var NotificationModel
     */
    private $notificationModel;

    /**
     * @var InventoryResolver
     */
    private $inventoryResolver;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var Config
     */
    private $config;

    public function __construct(
        InventoryResolver $inventoryResolver,
        NotificationModel $notificationModel,
        LoggerInterface $logger,
        Config $config
    ) {
        $this->notificationModel = $notificationModel;
        $this->inventoryResolver = $inventoryResolver;
        $this->logger = $logger;
        $this->config = $config;
    }

    /**
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        if (!$this->config->isLowStockNotifications()) {
            return false;
        }

        try {
            /** @var Shipment $shipment */
            $shipment = $observer->getShipment();
            $sourceCode = $this->getSourceCode($shipment);
            $items = $shipment->getAllItems();

            foreach ($items as $key => $item) {
                if ($item->getQty() <= 0
                    || $item->getOrderItem()->isDummy(true)
                    || !$this->inventoryResolver->isProductLowStock($item->getSku(), $sourceCode)
                ) {
                    unset($items[$key]);
                }
            }
            if ($items) {
                $this->notificationModel->notify($items, $sourceCode);
            }
        } catch (\Exception $e) {
            $this->logger->critical($e);
        }
    }

    /**
     * @param Shipment $shipment
     *
     * @return string|null
     */
    protected function getSourceCode(Shipment $shipment)
    {
        $attributes = $shipment->getExtensionAttributes();
        if (!empty($attributes)
            && method_exists($attributes, 'getSourceCode')
            && $attributes->getSourceCode()
        ) {
            $sourceCode = $attributes->getSourceCode();
        } else {
            $sourceCode = 'default';
        }

        return $sourceCode;
    }
}
