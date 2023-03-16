<?php

/**
 * Standard: PSR-2
 *
 * @link http://www.php-fig.org/psr/psr-2 Full Documentation
 *
 * @package SC\DUPX\Chunk
 */

defined('ABSPATH') || defined('DUPXABSPATH') || exit;

/**
 * Event iterator
 */
interface EventIterator extends Iterator
{
    /**
     * The callback accept 3 params
     * $event , $index and $current values
     *
     * @param null | callable $callback
     */
    public function setEventCallback($callback = null);

    /**
     * Execute event
     *
     * @param mixed $event
     * @param mixed $key
     * @param mixed $current
     */
    public function doEvent($event, $key, $current);
}
