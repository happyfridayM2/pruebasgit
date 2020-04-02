<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Base
 */


namespace Amasty\Base\Model;

use Amasty\Base\Helper\Module;
use Amasty\Base\Model\AdminNotification\Model\ResourceModel\Inbox\Collection\ExistsFactory;
use Amasty\Base\Model\AdminNotification\Model\ResourceModel\Inbox\Collection\Expired;
use Amasty\Base\Model\AdminNotification\Model\ResourceModel\Inbox\Collection\ExpiredFactory;
use Amasty\Base\Model\Source\NotificationType;
use Magento\Framework\Escaper;
use Magento\Framework\Notification\MessageInterface;
use Magento\Store\Model\ScopeInterface;

/**
 * Class Feed for get information
 */
class Feed
{
    /**
     * Path to NEWS
     */
    const URN_NEWS = 'amasty.com/feed-news-segments.xml';//do not use https:// or http

    /**
     * Path to ADS
     */
    const URN_ADS = 'amasty.com/media/marketing/upsells.csv';

    /**
     * @var array
     */
    private $amastyModules = [];

    /**
     * @var \Amasty\Base\Model\Config
     */
    private $config;

    /**
     * @var \Magento\Framework\App\ProductMetadataInterface
     */
    private $productMetadata;

    /**
     * @var \Magento\AdminNotification\Model\InboxFactory
     */
    private $inboxFactory;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var ExpiredFactory
     */
    private $expiredFactory;

    /**
     * @var \Magento\Framework\Module\ModuleListInterface
     */
    private $moduleList;

    /**
     * @var AdminNotification\Model\ResourceModel\Inbox\Collection\ExistsFactory
     */
    private $inboxExistsFactory;

    /**
     * @var Module
     */
    private $moduleHelper;

    /**
     * @var Escaper
     */
    private $escaper;

    /**
     * @var FeedContent
     */
    private $feedContent;

    /**
     * @var Parser
     */
    private $parser;

    public function __construct(
        \Amasty\Base\Model\Config $config,
        \Magento\AdminNotification\Model\InboxFactory $inboxFactory,
        \Magento\Framework\App\ProductMetadataInterface $productMetadata,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        ExpiredFactory $expiredFactory,
        \Magento\Framework\Module\ModuleListInterface $moduleList,
        ExistsFactory $inboxExistsFactory,
        Module $moduleHelper,
        Escaper $escaper,
        FeedContent $feedContent,
        Parser $parser
    ) {
        $this->config = $config;
        $this->productMetadata = $productMetadata;
        $this->inboxFactory = $inboxFactory;
        $this->scopeConfig = $scopeConfig;
        $this->expiredFactory = $expiredFactory;
        $this->moduleList = $moduleList;
        $this->inboxExistsFactory = $inboxExistsFactory;
        $this->moduleHelper = $moduleHelper;
        $this->escaper = $escaper;
        $this->feedContent = $feedContent;
        $this->parser = $parser;
    }

    /**
     * @return $this
     */
    public function checkUpdate()
    {
        if ($this->config->getFrequencyInSec() + $this->config->getLastUpdate() > time()) {
            return $this;
        }

        $allowedNotifications = $this->getAllowedTypes();
        if (empty($allowedNotifications) || in_array(NotificationType::UNSUBSCRIBE_ALL, $allowedNotifications)) {
            return $this;
        }

        $feedData = null;
        $maxPriority = 0;

        $content = $this->feedContent->getFeedContent($this->feedContent->getFeedUrl(self::URN_NEWS));
        $feedXml = $this->parser->parseXml($content);

        if ($feedXml && $feedXml->channel && $feedXml->channel->item) {
            $installDate = $this->config->getFirstModuleRun();
            foreach ($feedXml->channel->item as $item) {
                if ((int)$item->version === 1 // for magento One
                    || ((string)$item->edition && (string)$item->edition !== $this->getCurrentEdition())
                    || !array_intersect($this->convertToArray($item->type), $allowedNotifications)
                ) {
                    continue;
                }

                $priority = (int)$item->priority ?: 1;
                if ($priority <= $maxPriority
                    || !$this->validateByExtension((string)$item->extension)
                    || !$this->validateByAmastyCount($item->amasty_module_qty)
                    || !$this->validateByNotInstalled((string)$item->amasty_module_not)
                    || !$this->validateByExtension((string)$item->third_party_modules, true)
                    || !$this->validateByDomainZone((string)$item->domain_zone)
                    || $this->isItemExists($item)
                ) {
                    continue;
                }

                $date = strtotime((string)$item->pubDate);
                $expired = (string)$item->expirationDate ? strtotime((string)$item->expirationDate) : null;
                if ($installDate <= $date
                    && (!$expired || $expired > gmdate('U'))
                ) {
                    $maxPriority = $priority;
                    $expired = $expired ? date('Y-m-d H:i:s', $expired) : null;

                    $feedData = [
                        'severity'        => MessageInterface::SEVERITY_NOTICE,
                        'date_added'      => date('Y-m-d H:i:s', $date),
                        'expiration_date' => $expired,
                        'title'           => $this->convertString($item->title),
                        'description'     => $this->convertString($item->description),
                        'url'             => $this->convertString($item->link),
                        'is_amasty'       => 1,
                        'image_url'       => $this->convertString($item->image)
                    ];
                }
            }

            if ($feedData) {
                /** @var \Magento\AdminNotification\Model\Inbox $inbox */
                $inbox = $this->inboxFactory->create();
                $inbox->parse([$feedData]);
            }
        }
        $this->config->setLastUpdate();

        return $this;
    }

    /**
     * @param $value
     *
     * @return array
     */
    private function convertToArray($value)
    {
        return explode(',', (string)$value);
    }

    /**
     * @param \SimpleXMLElement $item
     *
     * @return bool
     */
    private function isItemExists(\SimpleXMLElement $item)
    {
        return $this->inboxExistsFactory->create()->execute($item);
    }

    /**
     * @return string
     */
    protected function getCurrentEdition()
    {
        return $this->productMetadata->getEdition() === 'Community' ? 'ce' : 'ee';
    }

    /**
     * @return $this
     */
    public function removeExpiredItems()
    {
        if ($this->config->getLastRemovement() + Config::REMOVE_EXPIRED_FREQUENCY > time()) {
            return $this;
        }

        /** @var Expired $collection */
        $collection = $this->expiredFactory->create();
        foreach ($collection as $model) {
            $model->setIsRemove(1)->save();
        }

        $this->config->setLastRemovement();

        return $this;
    }

    /**
     * @return array
     */
    private function getAllowedTypes()
    {
        $allowedNotifications = $this->getModuleConfig('notifications/type');
        $allowedNotifications = explode(',', $allowedNotifications);

        return $allowedNotifications;
    }

    /**
     * @param \SimpleXMLElement $data
     *
     * @return string
     */
    private function convertString(\SimpleXMLElement $data)
    {
        $data = $this->escaper->escapeHtml((string)$data);

        return $data;
    }

    /**
     * @param string $path
     * @param int    $storeId
     *
     * @return mixed
     */
    private function getModuleConfig($path, $storeId = null)
    {
        return $this->scopeConfig->getValue(
            'amasty_base/' . $path,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * @return array|string[]
     */
    private function getInstalledAmastyExtensions()
    {
        if (!$this->amastyModules) {
            $modules = $this->moduleList->getNames();

            $dispatchResult = new \Magento\Framework\DataObject($modules);
            $modules = $dispatchResult->toArray();

            $modules = array_filter(
                $modules,
                static function ($item) {
                    return strpos($item, 'Amasty_') !== false;
                }
            );
            $this->amastyModules = $modules;
        }

        return $this->amastyModules;
    }

    /**
     * @return array|string[]
     */
    private function getAllExtensions()
    {
        $modules = $this->moduleList->getNames();

        $dispatchResult = new \Magento\Framework\DataObject($modules);
        $modules = $dispatchResult->toArray();

        return $modules;
    }

    /**
     * @param string $extensions
     * @param bool   $allModules
     *
     * @return bool
     */
    private function validateByExtension($extensions, $allModules = false)
    {
        if ($extensions) {
            $result = false;
            $arrExtensions = $this->validateExtensionValue($extensions);

            if ($arrExtensions) {
                $installedModules = $allModules ? $this->getAllExtensions() : $this->getInstalledAmastyExtensions();
                $intersect = array_intersect($arrExtensions, $installedModules);
                if ($intersect) {
                    $result = true;
                }
            }
        } else {
            $result = true;
        }

        return $result;
    }

    /**
     * @param string $extensions
     *
     * @return bool
     */
    private function validateByNotInstalled($extensions)
    {
        if ($extensions) {
            $result = false;
            $arrExtensions = $this->validateExtensionValue($extensions);

            if ($arrExtensions) {
                $installedModules = $this->getInstalledAmastyExtensions();
                $diff = array_diff($arrExtensions, $installedModules);
                if ($diff) {
                    $result = true;
                }
            }
        } else {
            $result = true;
        }

        return $result;
    }

    /**
     * @param string $extensions
     *
     * @return array
     */
    private function validateExtensionValue($extensions)
    {
        $arrExtensions = explode(',', $extensions);
        $arrExtensions = array_filter(
            $arrExtensions,
            static function ($item) {
                return strpos($item, '_1') === false;
            }
        );

        $arrExtensions = array_map(
            static function ($item) {
                return str_replace('_2', '', $item);
            },
            $arrExtensions
        );

        return $arrExtensions;
    }

    /**
     * @param int|string $counts
     *
     * @return bool
     */
    private function validateByAmastyCount($counts)
    {
        $result = true;

        $countString = (string)$counts;
        if ($countString) {
            $moreThan = null;
            $result = false;

            $position = strpos($countString, '>');
            if ($position !== false) {
                $moreThan = substr($countString, $position + 1);
                $moreThan = explode(',', $moreThan);
                $moreThan = array_shift($moreThan);
            }

            $arrCounts = $this->convertToArray($counts);
            $amastyModules = $this->getInstalledAmastyExtensions();
            $dependModules = $this->getDependModules($amastyModules);
            $amastyModules = array_diff($amastyModules, $dependModules);

            $amastyCount = count($amastyModules);

            if ($amastyCount
                && (
                    in_array($amastyCount, $arrCounts, false) // non strict
                    || ($moreThan && $amastyCount >= $moreThan)
                )
            ) {
                $result = true;
            }
        }

        return $result;
    }

    /**
     * @param string $zones
     *
     * @return bool
     */
    private function validateByDomainZone($zones)
    {
        $result = true;
        if ($zones) {
            $arrZones = $this->convertToArray($zones);
            $currentZone = $this->feedContent->getDomainZone();

            if (!in_array($currentZone, $arrZones, true)) {
                $result = false;
            }
        }

        return $result;
    }

    /**
     * @param $amastyModules
     *
     * @return array
     */
    private function getDependModules($amastyModules)
    {
        $depend = [];
        $result = [];
        $dataName = [];
        foreach ($amastyModules as $module) {
            $data = $this->moduleHelper->getModuleInfo($module);
            if (isset($data['name'])) {
                $dataName[$data['name']] = $module;
            }

            if (isset($data['require']) && is_array($data['require'])) {
                foreach ($data['require'] as $requireItem => $version) {
                    if (strpos($requireItem, 'amasty') !== false) {
                        $depend[] = $requireItem;
                    }
                }
            }
        }

        $depend = array_unique($depend);
        foreach ($depend as $item) {
            if (isset($dataName[$item])) {
                $result[] = $dataName[$item];
            }
        }

        return $result;
    }
}
