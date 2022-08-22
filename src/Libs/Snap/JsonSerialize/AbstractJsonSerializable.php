<?php

/**
 *
 * @package Duplicator
 * @copyright (c) 2021, Snapcreek LLC
 *
 */

namespace Duplicator\Libs\Snap\JsonSerialize;

// phpcs:disable
require_once(dirname(__DIR__) . '/JsonSerializable.php'); 
// phpcs:enable

/**
 * Abstract class to extend in order to use the maximum potentialities of JsonSerialize
 */
abstract class AbstractJsonSerializable extends AbstractJsonSerializeObjData implements \JsonSerializable
{
    /**
     * Prepared json serialized object
     *
     * @return mixed
     */
    #[\ReturnTypeWillChange]
    final public function jsonSerialize()
    {
        return self::objectToJsonData($this, 0, array());
    }
}
