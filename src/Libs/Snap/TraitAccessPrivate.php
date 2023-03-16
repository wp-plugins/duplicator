<?php

/**
 *
 * @package   Duplicator
 * @copyright (c) 2022, Snap Creek LLC
 */

namespace Duplicator\Libs\Snap;

use ReflectionClass;
use ReflectionProperty;

// This trait allows us to access private methods/properties of parent classes from a child class.
// You can define list of methods/properties for which you want to allow this.
// We slightly modified version presented here: https://newicon.net/breaking-php-oo-inheritance
trait TraitAccessPrivate
{
    // DEV note: Please fill the following lists with parent's private methods/properties
    // that you want to be able to access from child classes!
    // You can fill them in the constructor of the child class where you use this trait.

    /** @var string[] Parent's private methods that you want to allow to be called from child class */
    private static $allowedPrivateMethodsCallList = array();
    /** @var string[] Parent's private static methods that you want to allow to be called from child class */
    private static $allowedPrivateStaticMethodsCallList = array();
    /** @var string[] Parent's private attributes for which you want to allow get access from child class */
    private static $allowedPrivateAttributesGetList = array();
    /** @var string[] Parent's private attributes for which you want to allow set access from child class */
    private static $allowedPrivateAttributesSetList = array();
    /**
     * Set this to true if you want to allow access to all parent's methods/properties,
     * not just those defined in lists above!
     *
     * @var bool
     */
    private static $allowedForAll = false;
    /** @var ?array<int, ReflectionClass> */
    private static $ancestorReflectionClasses = null;

    /**
     * Get ancestor reflection classes
     *
     * @return array<int, ReflectionClass> Ancestor reflection classes
     */
    private static function getAncestorReflectionClasses()
    {
        if (is_null(self::$ancestorReflectionClasses)) {
            $parentClasses                   = class_parents(self::class);
            self::$ancestorReflectionClasses = [];
            foreach ($parentClasses as $parentClass) {
                self::$ancestorReflectionClasses[] = new ReflectionClass($parentClass);
            }
        }
        return self::$ancestorReflectionClasses;
    }

    /**
     * Return reflection property if it's found by its name in parents.
     * Throws \ReflectionException if the property can not be found.
     *
     * @param string $property Property to find
     *
     * @return ReflectionProperty
     */
    private static function findParentReflectProperty($property)
    {
        $ancestorReflectionClasses = self::getAncestorReflectionClasses();
        foreach ($ancestorReflectionClasses as $ancestorReflectionClass) {
            if ($ancestorReflectionClass->hasProperty($property)) {
                return $ancestorReflectionClass->getProperty($property);
            }
        }
        throw new \ReflectionException("Property '" . $property . "' does not exist as an attribute of " . self::class . " or its parents");
    }

    /**
     * Return reflection method if it's found by its name in parents.
     * Throws \ReflectionException if it can not be found.
     *
     * @param string $method Method name to find
     *
     * @return \ReflectionMethod
     */
    private static function findParentReflectMethod($method)
    {
        $ancestorReflectionClasses = self::getAncestorReflectionClasses();
        foreach ($ancestorReflectionClasses as $ancestorReflectionClass) {
            if ($ancestorReflectionClass->hasMethod($method)) {
                return $ancestorReflectionClass->getMethod($method);
            }
        }
        throw new \ReflectionException("Property '" . $method . "' does not exist as a method of " . self::class . " or its parents");
    }

    /**
     * Searches for the specified method in parents. If it founds it and
     * it is an allowed private method, then it calls it using reflection mechanism.
     *
     * @param string  $method Method name to call
     * @param mixed[] $args   Arguments that will be passed to the method
     *
     * @return mixed
     */
    public function __call($method, $args)
    {
        $reflectMethod = self::findParentReflectMethod($method);
        if (
            $reflectMethod->isPrivate() &&
            (self::$allowedForAll || in_array($method, self::$allowedPrivateMethodsCallList))
        ) {
            $reflectMethod->setAccessible(true);
        }
        return $reflectMethod->invokeArgs($this, $args);
    }

    /**
     * Searches for the specified property in parents. If it founds it and
     * it is an allowed private property, then it gets it using reflection mechanism.
     *
     * @param string $name Name of the property that we want to get
     *
     * @return mixed
     */
    public function __get($name)
    {
        $reflectProperty = self::findParentReflectProperty($name);
        if (
            $reflectProperty->isPrivate() &&
            (self::$allowedForAll || in_array($name, self::$allowedPrivateAttributesGetList))
        ) {
            $reflectProperty->setAccessible(true);
        }
        return $reflectProperty->getValue($this);
    }

    /**
     * Searches for the specified property in parents. If it founds it and
     * it is an allowed private property, then it sets it using reflection mechanism.
     *
     * @param string $name  Name of the property that we want to set
     * @param mixed  $value Value that we want to assign to the property
     *
     * @return void
     */
    public function __set($name, $value)
    {
        $reflectProperty = self::findParentReflectProperty($name);
        if (
            $reflectProperty->isPrivate() &&
            (self::$allowedForAll || in_array($name, self::$allowedPrivateAttributesSetList))
        ) {
            $reflectProperty->setAccessible(true);
        }
        $reflectProperty->setValue($this, $value);
    }

    /**
     * Searches for the specified static method in parents. If it founds it and
     * it is an allowed private static method, then it calls it using reflection mechanism.
     *
     * @param string  $method Static method name to call
     * @param mixed[] $args   Arguments that will be passed to the static method
     *
     * @return mixed
     */
    public static function __callStatic($method, $args)
    {
        $reflectStaticMethod = self::findParentReflectMethod($method);
        if (
            $reflectStaticMethod->isPrivate() &&
            (self::$allowedForAll || in_array($method, self::$allowedPrivateStaticMethodsCallList))
        ) {
            $reflectStaticMethod->setAccessible(true);
        }
        return $reflectStaticMethod->invokeArgs(null, $args);
    }
}
