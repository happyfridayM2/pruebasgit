<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_ShopbyBase
 */


namespace Amasty\ShopbyBase\Model\Integration;

use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Module\Manager as ModuleManager;

/**
 * Class IntegrationFactory
 */
class IntegrationFactory
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var ModuleManager
     */
    private $moduleManager;

    /**
     * @var DummyObject
     */
    private $dummyObject;

    /**
     * @param ObjectManagerInterface $objectManager
     * @param ModuleManager $moduleManager
     * @param DummyObject $dummyObject
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        ModuleManager $moduleManager,
        DummyObject $dummyObject
    ) {
        $this->objectManager = $objectManager;
        $this->moduleManager = $moduleManager;
        $this->dummyObject = $dummyObject;
    }

    /**
     * @param string $type
     * @param array $data
     * @param bool $readonly
     * @return mixed
     */
    public function create($type, array $data = [], $readonly = false)
    {
        if ($this->isModuleEnabled($type)) {
            return $this->objectManager->create($type, $data);
        }

        return $readonly ? $this->dummyObject : null;
    }

    /**
     * @param string $type
     * @param bool $readOnly
     * @return mixed
     */
    public function get($type, $readOnly = false)
    {
        if ($this->isModuleEnabled($type)) {
            return $this->objectManager->get($type);
        }

        return $readOnly ? $this->dummyObject: null;
    }

    /**
     * @param $type
     * @return bool
     */
    private function isModuleEnabled($type)
    {
        $moduleName = implode('_', array_slice(explode('\\', $type), 0, 2));
        return $this->moduleManager->isEnabled($moduleName);
    }
}
