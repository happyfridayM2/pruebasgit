<?php
/**
 * Copyright © 2019 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

/**
 * Copyright © 2019 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\OrdersExportTool\Helper;

/**
 * Class Data
 * @package Wyomind\OrdersExportTool\Helper
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * Order instance constant
     */
    const ORDER = "order";

    /**
     * Order instance constant
     */
    const SHIPPING = "order_shipping_address";

    /**
     * Order instance constant
     */
    const BILLING = "order_billing_address";

    /**
     * Product instance constant
     */
    const PRODUCT = "order_item";

    /**
     * payment instance constant
     */
    const PAYMENT = "order_payment";

    /**
     * Invoice instance constant
     */
    const INVOICE = "invoice";

    /**
     * Shipment instance constant
     */
    const SHIPMENT = "shipment";

    /**
     * Creditmemo instance constant
     */
    const CREDITMEMO = "creditmemo";

    /**
     * @var \Magento\Catalog\Model\ProductRepository
     */
    protected $productRepository;

    /**
     * @var \Magento\Framework\Message\ManagerInterface|null
     */
    protected $_messageManager = null;

    /**
     * @var null
     */
    protected $_profile = null;

    /**
     * @var \Wyomind\Core\Helper\Data|null
     */
    protected $_coreHelper = null;

    /**
     * @var array
     */
    protected $_envConfig = [];

    /**
     * @var \Magento\Framework\App\DeploymentConfig\Reader|null
     */
    protected $_configReader = null;

    /**
     * @var bool
     */
    protected $skip = false;

    /**
     * Data constructor.
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Catalog\Model\ProductRepository $productRepository
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param \Magento\Framework\App\DeploymentConfig\Reader $configReader
     * @param \Wyomind\Core\Helper\Data $coreHelper
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Catalog\Model\ProductRepository $productRepository,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Framework\App\DeploymentConfig\Reader $configReader,
        \Wyomind\Core\Helper\Data $coreHelper
    )
    {
        $this->productRepository = $productRepository;
        $this->_messageManager = $messageManager;
        $this->_coreHelper = $coreHelper;
        $this->_configReader = $configReader;
        parent::__construct($context);
    }

    /**
     * @param $originalCall
     * @param $script
     * @param null $order
     * @param array $data
     * @param null $item
     * @return mixed
     * @throws \Exception
     */
    public function execPhp($originalCall, $script, $order = null, $data = [], $item = null)
    {
        try {
            $instances = $this->getEntities();
            foreach ($instances as $instance) {
                $object = str_replace(" ", "", ucWords(str_replace("_", " ", $instance["syntax"])));
                $script = preg_replace("#get" . $object . "s\(\)#m", "getItems(\$data,'" . $instance["syntax"] . "')", $script);
            }

            $script = preg_replace("#getItems\((\"|')([a-zA-Z]+)\\1\)#m", "getItems(\$data,'$2')", $script);

            return eval($script);
        } catch (\Throwable $e) {
            if ($item != null) {
                $classes = explode("\\", get_class($item));
                $object = array_pop($classes);
                if ($object == "Interceptor") {
                    $object = array_pop($classes);
                }
                $exc = new \Exception("\nError on line:\n" . $originalCall . "\n\nExecuted script:\n" . $script . "\n\nError message:\n" . $e->getMessage() . "\n\nObject:\n&nbsp;&nbsp;- Type: " . $object . "\n&nbsp;&nbsp;- ID: " . $item->getId());
                throw $exc;
            }
            throw new \Exception($script . "<br/>" . $e->getMessage());
        }
    }

    /**
     * @param $model
     */
    public function setProfile($model)
    {
        $this->_profile = $model;
    }

    /**
     * @param $types
     */
    public function load($types)
    {
        foreach ($types as $type) {
            $this->_profile->requireData($type);
        }
    }

    public function addQuoteToPhpScripts($output)
    {
        $matches = [];
        preg_match_all("/(?<script><\?php(?<php>.*)\?>)/sU", $output, $matches);

        $i = 0;
        foreach (array_values($matches["script"]) as $phpCode) {

            $parsed = preg_replace("#({{[^}]*}})#m", "\"$1\"", $phpCode);

            $output = str_replace($phpCode, $parsed, $output);
        }
        return $output;
    }

    /**
     * @param $preview
     * @param $output
     * @param null $order
     * @param array $data
     * @param null $item
     * @param int $type
     * @return mixed
     * @throws \Exception
     */
    public function executePhpScripts($preview, $output, $order = null, $data = [], $item = null, $type = 1)
    {
        $matches = [];
        preg_match_all("/(?<script><\?php(?<php>.*)\?>)/sU", $output, $matches);

        $i = 0;
        foreach (array_values($matches["php"]) as $phpCode) {
            $val = null;

            if ($type != 1) {
                $phpCode = stripslashes($phpCode);
            }

            $displayErrors = ini_get("display_errors");
            ini_set("display_errors", 0);

            if (($val = $this->execPhp($phpCode, $phpCode, $order, $data, $item)) === false) {
                if ($preview) {
                    ini_set("display_errors", $displayErrors);
                    throw new \Exception("Syntax error in " . $phpCode . " : " . error_get_last()["message"]);
                } else {
                    ini_set("display_errors", $displayErrors);
                    $this->messageManager->addError("Syntax error in <i>" . $phpCode . "</i><br>." . error_get_last()["message"]);
                    throw new \Exception();
                }
            }
            ini_set("display_errors", $displayErrors);

            if (is_array($val)) {
                $val = implode(",", $val);
            }
            $output = str_replace($matches["script"][$i], $val, $output);
            $i++;
        }

        return $output;
    }

    /**
     * @param array $data
     * @param string $type
     * @return mixed
     * @throws \Exception
     */
    function getItems($data = [], $type = "order")
    {

        if (empty($data[$type]) || isset($data[$type])) {
            return $data[$type];
        } else {
            throw new  \Exception(__("Unable to retrieve the data for '%1'. This instance doesn't exist.", $type));
        }
    }

    /**
     * @param bool $skip
     */
    public function skip($skip = true)
    {
        $this->skip = $skip;
    }

    /**
     * @return mixed
     */
    public function getSkip()
    {
        return $this->skip;
    }

    /**
     * Get all db instances
     * @param bool $scope
     * @return array
     */
    public function getEntities($scope = false)
    {
        $data = [
            'order' => [
                'code' => self::ORDER,
                'label' => 'Order',
                'syntax' => 'order',
                'table' => 'sales_order',
                'filterable' => true,
                'scopable' => true,
                'connection' => 'sales'
            ],

            'order_shipping_address' => [
                'code' => self::SHIPPING,
                'label' => 'Shipping address',
                'syntax' => 'shipping',
                'table' => 'sales_order_address',
                'filterable' => false,
                'scopable' => false,
                'connection' => 'sale'
            ],
            'order_billing_address' => [
                'code' => self::BILLING,
                'label' => 'Billing address',
                'syntax' => 'billing',
                'table' => 'sales_order_address',
                'filterable' => false,
                'scopable' => false,
                'connection' => 'sales'
            ],
            'order_item' => [
                'code' => self::PRODUCT,
                'label' => 'Product',
                'syntax' => 'product',
                'table' => 'sales_order_item',
                'filterable' => true,
                'scopable' => true,
                'connection' => 'sales'
            ],
            'order_payment' => [
                'code' => self::PAYMENT,
                'label' => 'Payment',
                'syntax' => 'payment',
                'table' => 'sales_order_payment',
                'filterable' => false,
                'scopable' => true,
                'connection' => 'sales'
            ],
            'invoice' => [
                'code' => self::INVOICE,
                'label' => 'Invoice',
                'syntax' => 'invoice',
                'table' => 'sales_invoice',
                'filterable' => true,
                'scopable' => true,
                'connection' => 'sales'
            ],
            'shipment' => [
                'code' => self::SHIPMENT,
                'label' => 'Shipment',
                'syntax' => 'shipment',
                'table' => 'sales_shipment',
                'filterable' => false,
                'scopable' => true,
                'connection' => 'sales'
            ],
            'creditmemo' => [
                'code' => self::CREDITMEMO,
                'label' => 'Creditmemo',
                'syntax' => 'creditmemo',
                'table' => 'sales_creditmemo',
                'filterable' => false,
                'scopable' => true,
                'connection' => 'sales'
            ]
        ];

        if ($scope) {
            return $data[$scope];
        } else {
            return $data;
        }
    }

    /**
     * Order the item of an array
     * @param array $a
     * @param array $b
     * @return boolean
     */
    public static function cmp($a, $b)
    {
        return ($a['field'] < $b['field']) ? (-1) : 1;
    }

    /**
     * return the profile from which the order item must be exported
     * @param object $_item
     * @return string
     */
    public function getExportTo($_item)
    {
        if ($_item->getExportTo()) {
            return $_item->getExportTo();
        } else {
            return $this->productRepository->getById($_item->getProductId())->getData('export_to');
        }
    }

    /**
     * @param $text
     * @param string $tags
     * @param bool $invert
     * @return null|string|string[]
     */
    public function stripTagsContent($text, $tags = '', $invert = false)
    {
        preg_match_all('/<(.+?)[\s]*\/?[\s]*>/si', trim($tags), $tags);
        $tags = array_unique($tags[1]);

        if (is_array($tags) and count($tags) > 0) {
            if ($invert == false) {
                return preg_replace('@<(?!(?:' . implode('|', $tags) . ')\b)(\w+)\b.*?>.*?</\1>@si', '', $text);
            } else {
                return preg_replace('@<(' . implode('|', $tags) . ')\b.*?>.*?</\1>@si', '', $text);
            }
        } elseif ($invert == false) {
            return preg_replace('@<(\w+)\b.*?>.*?</\1>@si', '', $text);
        }
        return strip_tags($text);
    }

    /**
     * @return array
     * @throws \Magento\Framework\Exception\FileSystemException
     * @throws \Magento\Framework\Exception\RuntimeException
     */
    public function getConnectionConfig()
    {
        if ($this->_coreHelper->moduleIsEnabled('Magento_Enterprise')) {
            if (empty($this->_envConfig)) {
                $this->_envConfig = $this->_configReader->load(\Magento\Framework\Config\File\ConfigFilePool::APP_ENV);
            }
            return $this->_envConfig['db']['connection'];
        } else {
            return [];
        }
    }

    /**
     * @param $db
     * @return string
     * @throws \Magento\Framework\Exception\FileSystemException
     * @throws \Magento\Framework\Exception\RuntimeException
     */
    public function getConnection($db)
    {
        if (isset($this->getConnectionConfig()[$db])) {
            if ($this->getConnectionConfig()[$db]['active'] == 1) {
                return $db;
            } else {
                return 'default';
            }
        } else {
            return 'default';
        }
    }

    /**
     * @param $params
     * @return bool
     */
    public function isXml($params)
    {
        return $params["type"] == 1;
    }

    /**
     * @param $params
     * @return bool
     */
    public function isAdvanced($params)
    {
        return $params["type"] >= 1 && $params["format"] == 2;
    }

    /**
     * @param $params
     * @return bool
     */
    public function isXmlOrAdvanced($params)
    {
        return $this->isXml($params) || $this->isAdvanced($params);
    }
}