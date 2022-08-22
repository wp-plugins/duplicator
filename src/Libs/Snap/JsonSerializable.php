<?php

/**
 *
 * @package Duplicator
 * @copyright (c) 2021, Snapcreek LLC
 *
 * this file isn't under PSR4 autoloader standard
 *
 */

if (!interface_exists('JsonSerializable')) {
    if (!defined('WP_JSON_SERIALIZE_COMPATIBLE')) {
        define('WP_JSON_SERIALIZE_COMPATIBLE', true);
    }

    /**
     * JsonSerializable interface.
     *
     * Compatibility shim for PHP <5.4
     *
     */
    interface JsonSerializable
    {
        /**
         * Serialize object
         *
         * @return string
         */
        public function jsonSerialize();
    }
}
