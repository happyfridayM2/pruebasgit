<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Xnotif
 */


namespace Amasty\Xnotif\Plugins\Backend\Model;

use Magento\Backend\Model\Url as BackendUrlModel;
use Magento\Framework\Registry;

/**
 * Class Url
 */
class Url
{
    /**
     * @var Registry
     */
    private $registry;

    public function __construct(Registry $registry)
    {
        $this->registry = $registry;
    }

    /**
     * @param BackendUrlModel $subject
     * @param string $areaFrontName
     * @return string
     */
    public function afterGetAreaFrontName(BackendUrlModel $subject, $areaFrontName)
    {
        if ($this->registry->registry('xnotif_test_notification')) {
            $areaFrontName = '';
        }

        return $areaFrontName;
    }
}
