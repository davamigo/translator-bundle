<?php
namespace Ifraktal\TranslatorBundle\Tests;

/**
 * IfraktalTestCase is the base class for unit tests.
 *
 * @package Ifraktal\TranslatorBundle\Tests
 * @author David Amigo <davamigo@gmail.com>
 */
class IfraktalTestCase extends \PHPUnit_Framework_TestCase
{
    /**
     * Gets the value of a private or protected attribute
     *
     * @param mixed  $object    The source object
     * @param string $attribute The attribute name
     * @return mixed
     */
    public static function getPrivateValue($object, $attribute)
    {
        return \PHPUnit_Framework_Assert::readAttribute($object, $attribute);
    }

    /**
     * Sets the value of a private or protected attribute
     *
     * @param mixed  $object    The source object
     * @param string $attribute The attribute name
     * @param mixed  $value     The new value of the attribute
     * @return mixed
     */
    public function setPrivateValue($object, $attribute, $value)
    {
        $class = get_class($object);
        $reflection = new \ReflectionClass($class);
        $property = $reflection->getProperty($attribute);
        $property->setAccessible(true);
        $property->setValue($object, $value);
    }

    /**
     * Executes a protected or private member of an object
     *
     * @param mixed  $object The source object
     * @param string $name   The name of the method
     * @param array  $params Attributes to call the method
     * @return mixed
     */
    public static function runPrivateMethod($object, $name, array $params = array())
    {
        $class = get_class($object);
        $reflection = new \ReflectionClass($class);
        $method = $reflection->getMethod($name);
        $method->setAccessible(true);
        return $method->invokeArgs($object, $params);
    }

    /**
     * Similar to \PHPUnit_Framework_TestCase::onConsecutiveCalls, but passing an array
     *
     * @param array $returnValues
     * @return \PHPUnit_Framework_MockObject_Stub_ConsecutiveCalls
     */
    public static function onConsecutiveCallsArray(array $returnValues)
    {
        return new \PHPUnit_Framework_MockObject_Stub_ConsecutiveCalls($returnValues);
    }
}
