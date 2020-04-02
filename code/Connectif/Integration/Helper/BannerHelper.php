<?php

namespace Connectif\Integration\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Connectif\Integration\Helper\ConfigHelper;

/**
 * Helper class for Cart related features.
 */
class BannerHelper extends AbstractHelper
{
    protected $_confighelper;

    /**
     * @param Context $context
     */
    public function __construct(
        Context $context,
        ConfigHelper $configHelper
    ) {
        parent::__construct($context);
        $this->_confighelper = $configHelper;
    }
    /**
     * Get all cart products
     */
    public function getBannerIds($blockName)
    {
        return $this->_confighelper->getBanners($blockName);
    }
}
