<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */


namespace Amasty\Feed\Model\Export\Adapter;

use Magento\Framework\ObjectManagerInterface;

class DocumentFactory
{
    /**
     * @var ObjectManagerInterface|null
     */
    private $objectManager = null;

    /**
     * @var string
     */
    private $instanceName = null;

    public function __construct(
        ObjectManagerInterface $objectManager,
        $instanceName
    ) {
        $this->objectManager = $objectManager;
        $this->instanceName = $instanceName;
    }

    /**
     * @param array $data
     * @return mixed
     */
    public function create(array $data = [])
    {
        return $this->objectManager->create($this->instanceName, $data);
    }
}
