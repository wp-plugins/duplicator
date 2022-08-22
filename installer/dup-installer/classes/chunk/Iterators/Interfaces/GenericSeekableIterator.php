<?php

/**
 *
 *
 * Standard: PSR-2
 * @link http://www.php-fig.org/psr/psr-2 Full Documentation
 *
 * @package SC\DUPX\Chunk
 *
 */

defined('ABSPATH') || defined('DUPXABSPATH') || exit;

/**
 *
 * @author andrea
 */
interface GenericSeekableIterator extends Iterator
{
    /**
     *
     * @param mixed $position
     *
     * @return bool
     */
    public function gSeek($position);

    /**
     * return current position
     *
     * @return mixed
     */
    public function getPosition();

    /**
     * Free resources in current iteration
     */
    public function stopIteration();

    /**
     * Return iterations count
     */
    public function itCount();
}
