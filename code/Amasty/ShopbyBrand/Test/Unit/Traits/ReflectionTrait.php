<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_ShopbyBrand
 */


namespace Amasty\ShopbyBrand\Test\Unit\Traits;

/**
 * Provide useful methods with reflection.
 *
 * phpcs:ignoreFile
 */
trait ReflectionTrait
{
    /**
     * @param object $object
     * @param string $methodName
     * @param array $parameters
     *
     * @return mixed
     * @throws \ReflectionException
     */
    private function invokeMethod($object, $methodName, array $parameters = [])
    {
        $reflection = new \ReflectionClass(get_class($object));
        $method = $reflection->getMethod($methodName);
        $method->setAccessible(true);

        return $method->invokeArgs($object, $parameters);
    }

    /**
     * @param object $object
     * @param string $propertyName
     * @param mixed $value
     * @param string $originalClassName
     *
     * @return object
     * @throws \ReflectionException
     */
    private function setProperty($object, $propertyName, $value, $originalClassName = '')
    {
        $reflection = new \ReflectionClass($originalClassName ?: get_class($object));
        $property = $reflection->getProperty($propertyName);
        $property->setAccessible(true);
        $property->setValue($object, $value);

        return $object;
    }

    /**
     * @param $object
     * @param $propertyName
     * @return object
     * @throws \ReflectionException
     */
    private function getProperty($object, $propertyName)
    {
        $reflection = new \ReflectionClass(get_class($object));
        $property = $reflection->getProperty($propertyName);
        $property->setAccessible(true);
        $property->getValue($object);

        return $object;
    }
}
