<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Conf
 */


namespace Amasty\Conf\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Inventory extends AbstractDb
{
    /**
     * @var array
     */
    private $isInStock;

    /**
     * @var array
     */
    private $stockIds;

    /**
     * @var array
     */
    private $sourceCodes;

    /**
     * @var array
     */
    private $qty;

    /**
     * @var \Magento\Framework\Module\Manager
     */
    private $moduleManager;

    /**
     * @var \Magento\CatalogInventory\Api\StockRegistryInterface
     */
    private $stockRegistry;

    /**
     * @var array
     */
    private $stockItems;

    public function __construct(
        \Magento\Framework\Module\Manager $moduleManager,
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry,
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        $connectionName = null
    ) {
        parent::__construct($context, $connectionName);
        $this->moduleManager = $moduleManager;
        $this->stockRegistry = $stockRegistry;
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        $this->isInStock = [];
        $this->stockIds = [];
        $this->sourceCodes = [];
        $this->qty = [];
    }

    /**
     * @param string $productSku
     * @param string $websiteCode
     *
     * @return bool
     *
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getIsInStock($productSku, $websiteCode)
    {
        if (!isset($this->isInStock[$productSku])) {
            $stockId = $this->getStockId($websiteCode);
            $select = $this->getConnection()->select()
                ->from($this->getTable('inventory_stock_' . $stockId), ['is_salable'])
                ->where('sku = ?', $productSku);
            $this->isInStock[$productSku] = (bool) $this->getConnection()->fetchOne($select);
        }

        return $this->isInStock[$productSku];
    }

    /**
     * @param $skuValues
     * @param $websiteCode
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getStocks($skuValues, $websiteCode)
    {
        if ($this->moduleManager->isEnabled('Magento_Inventory')) {
            $stockId = $this->getStockId($websiteCode);
            $select = $this->getConnection()->select()
                ->from($this->getTable('inventory_stock_' . $stockId), ['sku', 'is_salable'])
                ->where('sku IN (?)', $skuValues);
            $stocks = $this->getConnection()->fetchPairs($select);
        } else {
            $skuValues = is_array($skuValues) ? $skuValues : [$skuValues];
            $stocks = [];
            foreach ($skuValues as $id => $sku) {
                $stocks[$sku] = $this->getStockItem($sku, $websiteCode)->getIsInStock();
            }
        }

        return $stocks;
    }

    /**
     * @param $productSku
     * @param $websiteCode
     *
     * @return float|int|array
     *
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getQty($productSku, $websiteCode)
    {
        if ($this->moduleManager->isEnabled('Magento_Inventory')) {
            $qty = $this->getMsiQty($productSku, $websiteCode);
        } else {
            $productSku = is_array($productSku) ? $productSku : [$productSku];
            $qty = [];
            foreach ($productSku as $id => $sku) {
                $qty[$sku] = $this->getStockItem($sku, $websiteCode)->getQty();
            }
        }

        return $qty;
    }

    /**
     * @param $productSku
     * @param $websiteCode
     *
     * @return \Magento\CatalogInventory\Api\Data\StockItemInterface
     *
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getStockItem($productSku, $websiteCode)
    {
        if (!isset($this->stockItems[$productSku])) {
            $this->stockItems[$productSku] = $this->stockRegistry->getStockItemBySku($productSku, $websiteCode);
        }

        return $this->stockItems[$productSku];
    }

    /**
     * For MSI. Need to get negative qty.
     * Emulate \Magento\InventoryReservations\Model\ResourceModel\GetReservationsQuantity::execute
     *
     * @param string|array $productSku
     * @param string $websiteCode
     *
     * @return float|int|array
     *
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getMsiQty($productSku, $websiteCode)
    {
        if (is_array($productSku)) {
            $itemQty = $this->getItemsQty($productSku, $websiteCode);
            $reservationsQty = $this->getReservationsQty($productSku, $this->getStockId($websiteCode));
            foreach ($productSku as $sku) {
                $qty[$sku] = ($itemQty[$sku] ?? 0) + ($reservationsQty[$sku] ?? 0);
            }
        } else {
            if (!isset($this->qty[$websiteCode][$productSku])) {
                $this->qty[$websiteCode][$productSku] = $this->getItemQty($productSku, $websiteCode)
                    + $this->getReservationQty($productSku, $this->getStockId($websiteCode));
            }
            $qty = $this->qty[$websiteCode][$productSku];
        }

        return $qty;
    }

    /**
     * @param string $productSku
     * @param string $websiteCode
     *
     * @return float|int
     */
    private function getItemQty($productSku, $websiteCode)
    {
        $select = $this->getConnection()->select()
            ->from($this->getTable('inventory_source_item'), ['SUM(quantity)'])
            ->where('source_code IN (?)', $this->getSourceCodes($websiteCode))
            ->where('sku = ?', $productSku)
            ->group('sku');

        return $this->getConnection()->fetchOne($select);
    }

    /**
     * @param array $productsSku
     * @param string $websiteCode
     *
     * @return array
     */
    private function getItemsQty($productsSku, $websiteCode)
    {
        $select = $this->getConnection()->select()
            ->from($this->getTable('inventory_source_item'), ['sku', 'SUM(quantity)'])
            ->where('source_code IN (?)', $this->getSourceCodes($websiteCode))
            ->where('sku IN (?)', $productsSku)
            ->group('sku');

        return $this->getConnection()->fetchPairs($select);
    }

    /**
     * For MSI.
     *
     * @param string $websiteCode
     *
     * @return int
     */
    public function getStockId($websiteCode)
    {
        if (!isset($this->stockIds[$websiteCode])) {
            $select = $this->getConnection()->select()
                ->from($this->getTable('inventory_stock_sales_channel'), ['stock_id'])
                ->where('type = \'website\' AND code = ?', $websiteCode);

            $this->stockIds[$websiteCode] = (int)$this->getConnection()->fetchOne($select);
        }

        return $this->stockIds[$websiteCode];
    }

    /**
     * For MSI.
     *
     * @param string $websiteCode
     *
     * @return array
     */
    public function getSourceCodes($websiteCode)
    {
        if (!isset($this->sourceCodes[$websiteCode])) {
            $select = $this->getConnection()->select()
                ->from($this->getTable('inventory_source_stock_link'), ['source_code'])
                ->where('stock_id = ?', $this->getStockId($websiteCode));

            $this->sourceCodes[$websiteCode] = $this->getConnection()->fetchCol($select);
        }

        return $this->sourceCodes[$websiteCode];
    }

    /**
     * For MSI.
     *
     * @param string $sku
     * @param int $stockId
     *
     * @return int|string
     */
    private function getReservationQty($sku, $stockId)
    {
        $select = $this->getConnection()->select()
            ->from($this->getTable('inventory_reservation'), ['quantity' => 'SUM(quantity)'])
            ->where('sku = ?', $sku)
            ->where('stock_id = ?', $stockId)
            ->limit(1);

        $reservationQty = $this->getConnection()->fetchOne($select);
        if ($reservationQty === false) {
            $reservationQty = 0;
        }

        return $reservationQty;
    }

    /**
     * For MSI.
     *
     * @param array $sku
     * @param int $stockId
     *
     * @return array
     */
    private function getReservationsQty($sku, $stockId)
    {
        $select = $this->getConnection()->select()
            ->from($this->getTable('inventory_reservation'), ['sku', 'quantity' => 'SUM(quantity)'])
            ->where('sku IN (?)', $sku)
            ->where('stock_id = ?', $stockId)
            ->group('sku');

        $reservationQty = $this->getConnection()->fetchPairs($select);
        if ($reservationQty == false) {
            $reservationQty = array_fill_keys($sku, 0);
        }

        return $reservationQty;
    }
}
