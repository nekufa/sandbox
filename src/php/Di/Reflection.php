<?php

namespace Di;

use ReflectionClass;
use ReflectionMethod;
use ReflectionProperty;

/**
 * Class Reflection
 * @package Di
 */
abstract class Reflection {

    /**
     * @param $class
     * @return ReflectionClass
     */
    public static function getReflectionClass($class)
    {
        static $instances = array();
        if(!isset($instances[$class])) {
            $instances[$class] = new ReflectionClass($class);
        }
        return $instances[$class];
    }

    /**
     * @param $class
     * @param $property
     * @return ReflectionProperty
     */
    public static function getReflectionProperty($class, $property)
    {
        static $instances = array();
        $key = $class . '.' . $property;
        if(!isset($instances[$key])) {
            $instances[$key] = new ReflectionProperty($class, $property);
        }
        return $instances[$key];
    }

    /**
     * @param $class
     * @param $method
     * @return ReflectionMethod
     */
    public static function getReflectionMethod($class, $method)
    {
        static $instances = array();
        $key = $class . '.' . $method;
        if(!isset($instances[$key])) {
            $instances[$key] = new ReflectionMethod($class, $method);
        }
        return $instances[$key];
    }

} 