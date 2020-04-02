<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Xnotif
 */


namespace Amasty\Xnotif\Model;

use Amasty\Xnotif\Model\ResourceModel\AdminNotification\CollectionFactory as NotificationCollectionFactory;
use Amasty\Xnotif\Helper\Config;
use Amasty\Xnotif\Block\AdminNotify;
use Magento\Store\Model\App\Emulation;
use Magento\Framework\App\State;
use Psr\Log\LoggerInterface;
use Magento\Framework\App\Area;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Store\Model\Store;

class AdminNotification
{
    /**
     * @var NotificationCollectionFactory
     */
    private $notificationsFactory;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var AdminNotify
     */
    private $adminNotify;

    /**
     * @var Emulation
     */
    private $appEmulation;

    /**
     * @var State
     */
    private $appState;

    /**
     * @var LoggerInterface
     */
    private $logger;
    /**
     * @var TransportBuilder
     */
    private $transportBuilder;

    public function __construct(
        NotificationCollectionFactory $notificationsFactory,
        Config $config,
        AdminNotify $adminNotify,
        Emulation $appEmulation,
        State $appState,
        LoggerInterface $logger,
        TransportBuilder $transportBuilder
    ) {
        $this->notificationsFactory = $notificationsFactory;
        $this->config = $config;
        $this->adminNotify = $adminNotify;
        $this->appEmulation = $appEmulation;
        $this->appState = $appState;
        $this->logger = $logger;
        $this->transportBuilder = $transportBuilder;
    }

    public function sendAdminNotifications()
    {
        $emailTo = $this->getEmailTo();
        $sender = $this->config->getAdminNotificationSender();

        if ($this->isAdminNotificationEnabled() && $emailTo && $sender) {
            try {
                $this->adminNotify->setSubscriptionCollection(
                    $this->notificationsFactory->create()->getCollection()
                );

                $this->appEmulation->startEnvironmentEmulation(Store::DEFAULT_STORE_ID);
                $subscriptionGrid = $this->appState->emulateAreaCode(
                    Area::AREA_FRONTEND,
                    [$this->adminNotify, 'toHtml']
                );
                $this->appEmulation->stopEnvironmentEmulation();

                $transport = $this->transportBuilder->setTemplateIdentifier(
                    $this->getAdminNotificationTemplate()
                )->setTemplateOptions(
                    [
                        'area' => Area::AREA_FRONTEND,
                        'store' => Store::DEFAULT_STORE_ID
                    ]
                )->setTemplateVars(
                    [
                        'subscriptionGrid' => $subscriptionGrid
                    ]
                )->setFrom(
                    $sender
                )->addTo(
                    $emailTo
                )->getTransport();

                $transport->sendMessage();
            } catch (\Exception $e) {
                $this->logger->critical($e->getMessage());
            }
        }
    }

    /**
     * @return array|string
     */
    protected function getEmailTo()
    {
        $emailTo = $this->config->getAdminNotificationEmail();
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
     * @return string
     */
    protected function getAdminNotificationSender()
    {
        return $this->config->getAdminNotificationSender();
    }

    /**
     * @return string
     */
    protected function getAdminNotificationTemplate()
    {
        return $this->config->getAdminNotificationTemplate();
    }

    /**
     * @return bool
     */
    protected function isAdminNotificationEnabled()
    {
        return $this->config->isAdminNotificationEnabled();
    }
}
