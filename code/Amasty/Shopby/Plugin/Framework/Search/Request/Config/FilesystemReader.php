<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Shopby
 */


namespace Amasty\Shopby\Plugin\Framework\Search\Request\Config;

/**
 * Class FilesystemReader
 * @package Amasty\Shopby\Plugin\Framework\Search\Request\Config
 */
class FilesystemReader
{
    /**
     * @var \Amasty\Shopby\Model\Search\RequestGenerator
     */
    protected $requestGenerator;

    /**
     * ReaderPlugin constructor.
     *
     * @param \Amasty\Shopby\Model\Search\RequestGenerator $requestGenerator
     */
    public function __construct(
        \Amasty\Shopby\Model\Search\RequestGenerator $requestGenerator
    ) {
        $this->requestGenerator = $requestGenerator;
    }

    /**
     * @param \Magento\Framework\Config\ReaderInterface $subject
     * @param array $requests
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormatParameter)
     */
    public function afterRead(\Magento\Framework\Config\ReaderInterface $subject, $requests)
    {
        return array_merge_recursive($requests, $this->requestGenerator->generate());
    }
}
