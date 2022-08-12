<?php

namespace andmemasin\myabstract\test;

use ReflectionException;

/**
 * Trait InvokeProtectedTrait
 * @package andmemasin\myabstract\traits
 * @author Tõnis Ormisson <tonis@andmemasin.eu>
 */
trait InvokeProtectedTrait
{
    /**
     * Call protected/private method of a class.
     *
     * @param object &$object Instantiated object that we will run method on.
     * @param string $methodName Method name to call
     * @param array $parameters Array of parameters to pass into method.
     *
     * @return mixed Method return.
     * @throws ReflectionException
     */
    public function invokeMethod(object $object, string $methodName, array $parameters = array())
    {
        $reflection = new \ReflectionClass(get_class($object));
        $method = $reflection->getMethod($methodName);
        $method->setAccessible(true);

        return $method->invokeArgs($object, $parameters);
    }

}