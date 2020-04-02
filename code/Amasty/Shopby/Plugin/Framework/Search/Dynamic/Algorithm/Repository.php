<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Shopby
 */


namespace Amasty\Shopby\Plugin\Framework\Search\Dynamic\Algorithm;

use Magento\Framework\Search\Dynamic\Algorithm\Repository as DynamicAlgorithmRepository;
use Magento\Framework\Search\Dynamic\Algorithm;

/**
 * For temporary use. Untill all buckets will be evaluated in 1 request @todo
 *
 * Class DynamicAlgorithmRepositoryPlugin
 * @package Amasty\Shopby\Plugin
 */
class Repository
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    /**
     * Repository constructor.
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     */
    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * Reinitialize shared instance. In order to get correct aggregations for a price when it has current value applied
     *
     * @param Repository $subject
     * @param \Closure $closure
     * @param $algorithmType
     * @param array $data
     * @return mixed
     * @SuppressWarnings(PHPMD.UnusedFormatParameter)
     */
    public function aroundGet(DynamicAlgorithmRepository $subject, \Closure $closure, $algorithmType, array $data = [])
    {
        $result = $closure($algorithmType, $data);
        if ($algorithmType == 'auto') {
            return $this->objectManager->create(Algorithm\Auto::class, $data);
        } elseif ($algorithmType == 'manual') {
            return $this->objectManager->create(Algorithm\Manual::class, $data);
        }

        return $result;
    }
}
