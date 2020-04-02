<?php

/**
 * Copyright © 2017 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\Core\Model;

/**
 * License backend Notifications
 */
class Notifications extends \Magento\AdminNotification\Model\System\Message
{

    /**
     * @var array
     */
    protected $_values = [];

    /**
     * @var string
     */
    public $version = "";

    /**
     * @var array
     */
    protected $_warnings = [];

    /**
     * @var \Wyomind\Core\Helper\Data
     */
    protected $_coreHelper = null;

    /**
     * @var
     */
    protected $_cacheManager = null;

    /**
     * @var \Magento\Framework\Filesystem\Directory\ReadFactory array
     */
    protected $_directoryRead = [];

    /**
     * @var \Magento\Framework\App\Filesystem\DirectoryList
     */
    protected $_directoryList = null;

    /**
     * @var boolean
     */
    protected $_refreshCache = false;


    /**
     * @var string
     */
    protected $_magentoVersion = 0;

    /**
     * {@inherit}
     */
    protected $_logger = null;

    /**
     * @var boolean
     */
    protected $_logEnabled = false;

    /**
     * @var \Magento\Backend\Model\Auth\Session
     */
    protected $_auth = null;

    /**
     * @var \Magento\Framework\HTTP\PhpEnvironment\Request
     */
    public $request = null;
    protected $_configResourceModel = null;
    protected $_encryptor = null;
    protected $_licenseHelper = null;
    /**
     * @var \Magento\Framework\Serialize\SerializerInterface
     */
    protected $serializer;
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    /**
     * Notifications constructor.
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magento\Framework\Registry $registry
     * @param \Wyomind\Core\Helper\License $licenseHelper
     * @param \Magento\Framework\App\Config\MutableScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\UrlInterface $urlBuilder
     * @param \Magento\Framework\Session\SessionManagerInterface $session
     * @param \Wyomind\Core\Helper\Data $coreHelper
     * @param \Magento\Framework\Filesystem\Directory\ReadFactory $directoryRead
     * @param \Magento\Framework\Filesystem\File\ReadFactory $fileRead
     * @param \Magento\Framework\App\Filesystem\DirectoryList $directoryList
     * @param \Magento\Backend\Model\Auth\Session $auth
     * @param \Magento\Framework\HTTP\PhpEnvironment\Request $request
     * @param ResourceModel\Config $configResourceModel
     * @param \Magento\Framework\Encryption\EncryptorInterface $encryptor
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\Registry $registry,
        \Wyomind\Core\Helper\License $licenseHelper,
        \Magento\Framework\App\Config\MutableScopeConfigInterface $scopeConfig,
        \Magento\Framework\UrlInterface $urlBuilder,
        \Magento\Framework\Session\SessionManagerInterface $session,
        \Wyomind\Core\Helper\Data $coreHelper,
        \Magento\Framework\Filesystem\Directory\ReadFactory $directoryRead,
        \Magento\Framework\Filesystem\File\ReadFactory $fileRead,
        \Magento\Framework\App\Filesystem\DirectoryList $directoryList,
        \Magento\Backend\Model\Auth\Session $auth,
        \Magento\Framework\HTTP\PhpEnvironment\Request $request,
        \Wyomind\Core\Model\ResourceModel\Config $configResourceModel,
        \Magento\Framework\Encryption\EncryptorInterface $encryptor,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    )
    {

        parent::__construct($context, $registry, $resource, $resourceCollection, $data);


        if (class_exists("\Magento\Framework\Serialize\Serializer\Serialize")) {
            $this->serializer = $objectManager->get("\Magento\Framework\Serialize\Serializer\Serialize");
        } else {
            $this->serializer = null;
        }

        $this->_magentoVersion = $coreHelper->getMagentoVersion();
        $this->_scopeConfig = $scopeConfig;
        $this->_urlBuilder = $urlBuilder;
        $this->_cacheManager = $context->getCacheManager();
        $this->_session = $session;
        $this->_coreHelper = $coreHelper;
        $this->_logEnabled = $this->_coreHelper->isLogEnabled();
        $this->_logger = $objectManager->create("\Wyomind\Core\Logger\Logger");
        $this->_auth = $auth;
        $this->_configResourceModel = $configResourceModel;
        $this->_encryptor = $encryptor;
        $root = $directoryList->getPath(\Magento\Framework\App\Filesystem\DirectoryList::ROOT);


        if (file_exists($root . "/vendor/wyomind/")) {
            $this->_directoryRead[$root . "/vendor/wyomind/"] = $directoryRead->create($root . "/vendor/wyomind/");
        }
        if (file_exists($root . "/app/code/Wyomind/")) {
            $this->_directoryRead[$root . "/app/code/Wyomind/"] = $directoryRead->create($root . "/app/code/Wyomind/");
        }

        $this->_httpRead = $fileRead;
        $this->_directoryList = $directoryList;

        $this->_licenseHelper = $licenseHelper;

        $this->version = $licenseHelper->getCoreVersion();

        $this->_refreshCache = false;

        if ($request->getParam("isAjax") !== "true") {
            $this->getValues();
            foreach ($this->_values as $ext) {
                $this->checkActivation($ext);
            }

            if ($this->_refreshCache) {
                $this->_cacheManager->clean(['config']);
            }
            // Mage >= 2.2
            if ($this->serializer) {
                $session->setData("wyomind_core_warnings", $this->serializer->serialize($this->_warnings));
            } else {
                // @codingStandardsIgnoreStart
                $session->setData("wyomind_core_warnings", serialize($this->_warnings));
                // @codingStandardsIgnoreEnd
            }

        } else {
            // Mage >= 2.2
            if ($this->serializer) {
                $this->_warnings = $this->serializer->unserialize($session->getData("wyomind_core_warnings"));
            } else {
                // @codingStandardsIgnoreStart
                $this->_warnings = unserialize($session->getData("wyomind_core_warnings"));
                // @codingStandardsIgnoreEnd
            }

        }
        $this->objectManager = $objectManager;
    }

    /**
     * Add a line in the log
     * @param string $msg
     */
    public function notice($msg)
    {
        if ($this->_logEnabled) {
            $this->_logger->notice($msg);
        }
    }

    /**
     * Retrieve extensions information
     */
    public function getValues()
    {
        $dir = ".";
        $ret = [];

        foreach ($this->_directoryRead as $root => $directoryRead) {
            foreach ($directoryRead->read($dir) as $file) {
                if ($file !== "./Core" && $file !== "./core") {
                    if ($directoryRead->isDirectory($file) && $file != "." && $file != "..") {
                        if ($directoryRead->isFile($file . "/etc/config.xml")) {
                            $namespace = strtolower(str_replace("./", "", $file));
                            $xml = simplexml_load_file($root . $file . "/etc/module.xml");
                            $modules = $xml->xpath('/config/module');

                            foreach ($modules as $module) {
                                $moduleName = (string)$module['name'];
                                $version = (string)$module['setup_version'];
                            }

                            if ($this->_coreHelper->moduleIsEnabled($moduleName)) { // disabled ?
                                $prefix = $this->_coreHelper->getPrefix($moduleName);
                                $label = $this->_coreHelper->getStoreConfig($prefix . $namespace . "/license/extension_label");

                                $ret[] = ["label" => $label, "value" => $file, "version" => $version, "config" => $prefix . $namespace, "namespace" => $moduleName];
                            }
                        }
                    }
                }
            }
        }

        $this->_values = $ret;
    }

    /**
     * Transform XML to array
     * @param string $xml
     * @return array
     */
    public function XML2Array($xml)
    {
        $newArray = [];
        $array = (array)$xml;
        foreach ($array as $key => $value) {
            $value = (array)$value;
            if (isset($value [0])) {
                $newArray [$key] = trim($value [0]);
            } else {
                $newArray [$key] = $this->XML2Array($value, true);
            }
        }
        return $newArray;
    }

    /**
     * Add a license warning
     * @param string $name
     * @param string $type
     * @param array $vars
     */
    protected function addWarning(
        $name,
        $type,
        $vars = []
    )
    {
        if ($type) {
            $output = $this->_licenseHelper->sprintfArray($type, $vars);
        } else {
            $output = implode(" " . $vars);
        }
        $output = "<b> Wyomind " . $name . "</b> <br> " . $output . "";

        $this->_warnings[] = $output;
    }

    /**
     * Check if extension can be registered
     * @param array $extension
     */
    public function checkActivation($extension)
    {


        $registeredVersion = $this->_configResourceModel->getDefaultValueByPath($extension["config"] . "/license/extension_version");


        $activationKey = $this->_encryptor->decrypt($this->_configResourceModel->getDefaultValueByPath($extension["config"] . "/license/activation_key"));


        if ($activationKey === "") {
            $activationKey = $this->_coreHelper->getStoreConfig($extension["config"] . "/license/activation_key");
            if ($activationKey != "") {
                json_encode($activationKey);
                if (json_last_error() != JSON_ERROR_NONE || substr($activationKey, 0, 3) == "0:2") {
                    $this->_coreHelper->setDefaultConfigCrypted($extension["config"] . "/license/activation_key", $activationKey);
                }
            }
        }

        if ($activationKey != "") {
            json_encode($activationKey);
            if (json_last_error() != JSON_ERROR_NONE || substr($activationKey, 0, 3) == "0:2") {

                $activationKey = "";
                $this->_coreHelper->setDefaultConfigCrypted($extension["config"] . "/license/activation_key", $activationKey);
            }
        }

        $licenseCode = $this->_encryptor->decrypt($this->_configResourceModel->getDefaultValueByPath($extension["config"] . "/license/activation_code"));
        $namespace = str_replace(" ", "", $extension["namespace"]);

        // licence deleted because wrong ak or ac
        if ($registeredVersion != "" && $registeredVersion != $extension["version"]) { // Extension upgrade
            $this->notice("------------------------------------------");
            $this->notice("Checking registration of the license");
            $this->notice("Upgrade " . $extension['label'] . " from " . $registeredVersion . " to " . $extension["version"]);
            $this->notice("Activation key: " . $activationKey);
            if ($this->_auth->getUser() != null) {
                $this->notice("User: " . $this->_auth->getUser()->getUsername());
            }
            $this->_coreHelper->setDefaultConfig($extension["config"] . "/license/license_code", "");
            $this->_coreHelper->setDefaultConfig($extension["config"] . "/license/extension_version", $extension["version"]);

            $this->addWarning($extension["label"], "upgrade", [$registeredVersion, $extension["version"], $namespace, $activationKey]);
            $this->_session->setData("update_" . $extension["value"], "true");

        } else {
            if ($activationKey != "" && $licenseCode == "") {
                $this->addWarning($extension["label"], "invalidated", [$namespace, $activationKey]);
            } elseif ($licenseCode == '') {

                $this->addWarning($extension["label"], "pending", [$namespace]);
            }

        }
    }

    /**
     * @return string
     */
    public function getIdentity()
    {
        $md5 = "md5";
        return $md5($this->getText());
    }

    /**
     * @return int
     */
    public function getSeverity()
    {
        return self::SEVERITY_CRITICAL;
    }

    /**
     * @return string
     */
    public function getText()
    {
        $html = null;
        $count = count($this->_warnings);
        for ($i = 0; $i < $count; $i++) {
            $html .= "<div style='padding-bottom:5px;" . (($i != 0) ? "margin-top:5px;" : "") . "" . (($i < $count - 1) ? "border-bottom:1px solid gray;" : "") . "'>" . $this->_warnings[$i] . "</div>";
        }
        return $html;
    }

    /**
     * @return boolean
     */
    public function isDisplayed()
    {
        return count($this->_warnings) > 0;
    }

}
