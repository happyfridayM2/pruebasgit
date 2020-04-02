<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Shopby
 */


declare(strict_types=1);

namespace Amasty\Shopby\Model\Inventory;

use Magento\Framework\Module\Manager;

class Resolver
{
    const WEBSITE_CONDITION_REGEXP = '@(`?website_id`?=\s*)\d+@';

    const DEFAULT_WEBSITE_ID = 0;

    /**
     * @var Manager
     */
    private $moduleManager;

    public function __construct(Manager $moduleManager)
    {
        $this->moduleManager = $moduleManager;
    }

    /**
     * @return bool
     */
    public function isMsiEnabled()
    {
        return $this->moduleManager->isEnabled('Magento_Inventory');
    }

    /**
     * @param string $websiteCondition
     * @return string
     */
    public function replaceWebsiteWithDefault(string $websiteCondition): string
    {
        return preg_replace(
            self::WEBSITE_CONDITION_REGEXP,
            '$1 ' . self::DEFAULT_WEBSITE_ID,
            $websiteCondition
        );
    }
}
