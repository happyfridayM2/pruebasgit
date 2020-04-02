<?php

/**
 * Copyright © 2017 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\Core\Plugin\Config\Model;

use Magento\Framework\ObjectManagerInterface;

/**
 * Add log lines when modifying the license group of any extension
 */
class Config
{

    /**
     * {@inherit}
     */
    public $logger=null;

    /**
     * @var \Wyomind\Core\Helper\Data
     */
    public $dataHelper=null;

    /**
     * @var boolean
     */
    public $_logEnabled=false;
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * Config constructor.
     * @param ObjectManagerInterface $objectManager
     * @param \Wyomind\Core\Helper\Data $dataHelper
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Wyomind\Core\Helper\Data $dataHelper
    )
    {
        $this->logger=$objectManager->create("\Wyomind\Core\Logger\Logger");
        $this->dataHelper=$dataHelper;
        $this->logEnabled=$this->dataHelper->isLogEnabled();
        $this->objectManager=$objectManager;
    }

    /**
     * Add a line in the log file
     * @param string $msg
     */
    public function notice($msg)
    {
        if ($this->logEnabled) {
            $this->logger->notice($msg);
        }
    }

    /**
     * Check the value of the configuration before saving them
     * @param type $subject
     */
    public function beforeSave($subject)
    {
        $groups=$subject->getGroups();
        if ($groups)
            foreach ($groups as $groupId=>$groupData) {
                $groupPath=$subject->getSection() . '/' . $groupId;
                if (isset($groupData['fields'])) {
                    foreach ($groupData['fields'] as $key=>$values) {
                        $fullPath=$groupPath . "/" . $key;
                        if ($key == "activation_key") {
                            $this->notice("------------------------------------------");
                            $this->notice("Update in Stores > Configuration");
                            $this->notice("Activation key updated in config: " . $fullPath . " => " . implode(',', $values));
                            if ($this->dataHelper->isAdmin()) {
                                $objectManager=\Magento\Framework\App\ObjectManager::getInstance();
                                $auth=$objectManager->get("\Magento\Backend\Model\Auth");
                                if ($auth->getUser() != null) {
                                    $this->notice("User: " . $auth->getUser()->getUsername());
                                }
                            }
                        }
                        if ($key == "activation_code") {
                            $this->notice("------------------------------------------");
                            $this->notice("Update in Stores > Configuration");
                            $this->notice("License code updated in config: " . $fullPath . " => " . implode(',', $values));
                            if ($this->dataHelper->isAdmin()) {
                                $objectManager=\Magento\Framework\App\ObjectManager::getInstance();
                                $auth=$objectManager->get("\Magento\Backend\Model\Auth");
                                if ($auth->getUser() != null) {
                                    $this->notice("User: " . $auth->getUser()->getUsername());
                                }
                            }
                        }
                    }
                }
            }
    }

}
