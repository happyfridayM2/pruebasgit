<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */


namespace Amasty\Feed\Model;

/**
 * Class Config
 */
class Config
{
    /**#@+
     * Configuration paths
     */
    const FEED_SECTION = 'amasty_feed/';

    const GENERAL_GROUP = 'general/';

    const MULTI_PROCESS_GROUP = 'multi_process/';

    const NOTIFICATION_GROUP = 'notifications/';

    const BATCH_SIZE_FIELD = 'batch_size';

    const FILE_PATH_FIELD = 'file_path';

    const STORAGE_FOLDER = 'storage_folder';

    const ENABLED_FIELD = 'enabled';

    const PROCESS_COUNT_FIELD = 'process_count';

    const PREVIEW_ITEMS = 'preview_items';

    const EVENTS_FIELD = 'events';

    const SENDER_FIELD = 'email_sender';

    const EMAILS_FIELD = 'emails';

    const SUCCESS_TEMPLATE_FIELD = 'success_template';

    const UNSUCCESS_TEMPLATE_FIELD = 'unsuccess_template';
    /**#@-*/

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $config;

    public function __construct(\Magento\Framework\App\Config\ScopeConfigInterface $config)
    {
        $this->config = $config;
    }

    /**
     * @param string $group
     * @param string $path
     *
     * @return mixed
     */
    private function getScopeValue($group, $path)
    {
        return $this->config->getValue(
            self::FEED_SECTION . $group . $path,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @param string $group
     * @param string $path
     *
     * @return bool
     */
    private function isSetFlag($group, $path)
    {
        return $this->config->isSetFlag(
            self::FEED_SECTION . $group . $path,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return int
     */
    public function getItemsForPreview()
    {
        return (int)$this->getScopeValue(self::GENERAL_GROUP, self::PREVIEW_ITEMS);
    }

    /**
     * @return int
     */
    public function getItemsPerPage()
    {
        return (int)$this->getScopeValue(self::GENERAL_GROUP, self::BATCH_SIZE_FIELD);
    }

    /**
     * @return string
     */
    public function getSelectedEvents()
    {
        return $this->getScopeValue(self::NOTIFICATION_GROUP, self::EVENTS_FIELD);
    }

    /**
     * @return string
     */
    public function getSuccessEmailTemplate()
    {
        return $this->getScopeValue(self::NOTIFICATION_GROUP, self::SUCCESS_TEMPLATE_FIELD);
    }

    /**
     * @return string
     */
    public function getUnsuccessEmailTemplate()
    {
        return $this->getScopeValue(self::NOTIFICATION_GROUP, self::UNSUCCESS_TEMPLATE_FIELD);
    }

    /**
     * @return string
     */
    public function getEmailSenderContact()
    {
        return $this->getScopeValue(self::NOTIFICATION_GROUP, self::SENDER_FIELD);
    }

    /**
     * @return string
     */
    public function getEmails()
    {
        return $this->getScopeValue(self::NOTIFICATION_GROUP, self::EMAILS_FIELD);
    }

    /**
     * @return string
     */
    public function getStorageFolder()
    {
        return $this->getScopeValue(self::GENERAL_GROUP, self::STORAGE_FOLDER);
    }

    /**
     * @return string
     */
    public function getFilePath()
    {
        return $this->getScopeValue(self::GENERAL_GROUP, self::FILE_PATH_FIELD);
    }

    /**
     * @return int
     */
    public function getMaxJobsCount()
    {
        if (!$this->isSetFlag(self::MULTI_PROCESS_GROUP, self::ENABLED_FIELD)) {
            return 1;
        }

        if (!function_exists('pcntl_fork')) {
            return 1;
        }

        $processCount = (int)$this->getScopeValue(self::MULTI_PROCESS_GROUP, self::PROCESS_COUNT_FIELD);

        return $processCount > 1 ? $processCount : 1;
    }
}
