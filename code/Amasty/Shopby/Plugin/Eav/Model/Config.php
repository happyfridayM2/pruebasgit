<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Shopby
 */


namespace Amasty\Shopby\Plugin\Eav\Model;

use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;

/**
 * Class Config
 * @package Amasty\Shopby\Plugin\Eav\Model
 */
class Config
{
    /**
     * @param \Magento\Eav\Model\Config $subject
     * @param \Closure $closure
     * @param mixed $entityType
     * @param mixed $code
     * @return AbstractAttribute
     * @SuppressWarnings(PHPMD.UnusedFormatParameter)
     */
    public function aroundGetAttribute(\Magento\Eav\Model\Config $subject, \Closure $closure, $entityType, $code)
    {
        if (is_string($code) &&
            ($pos = strpos($code, \Amasty\Shopby\Model\Search\RequestGenerator::FAKE_SUFFIX)) !== false) {
            $code = substr($code, 0, $pos);
        }
        return $closure($entityType, $code);
    }
}
