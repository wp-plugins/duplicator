<?php

/**
 * Cunking manager
 *
 * Standard: PSR-2
 *
 * @link http://www.php-fig.org/psr/psr-2 Full Documentation
 *
 * @package SC\DUPX\Chunk
 */

defined('ABSPATH') || defined('DUPXABSPATH') || exit;

/**
 * Abstract class to split an single ajax requet in multiple requests
 */
abstract class DUPX_ChunkingManager
{
    /** @var GenericSeekableIterator */
    protected $it = null;

    /** @var mixed */
    protected $position = null;

    /** @var integer max iteration before stop. If 0 have no limit */
    public $maxIteration = 0;

    /** @var integer timeout in milliseconds before stop exectution */
    public $timeOut = 0;

    /** @var integer sleep in milliseconds every iteration */
    public $throttling = 0;

    /** @var float */
    protected $startTime = null;

    /** @var integer */
    protected $itCount = 0;

    /**
     * set params
     *
     * @param number $maxIteration
     * @param number $timeOut
     * @param number $throttling
     */
    public function __construct($maxIteration = 0, $timeOut = 0, $throttling = 0)
    {

        $this->maxIteration = $maxIteration;
        $this->timeOut      = $timeOut;
        $this->throttling   = $throttling;
        $this->it           = $this->getIterator();

        if (!is_subclass_of($this->it, 'GenericSeekableIterator')) {
            throw new Exception('Iterator don\'t extend GenericSeekableIterator');
        }
    }

    /**
     *
     * @param boolean $rewind
     *
     * @return boolean true if execution completed false if stopped
     */
    public function start($rewind = false)
    {
        $this->itCount   = 0;
        $microThrottling = $this->throttling * 1000;

        if ($rewind) {
            /**
             * delete store data and rewind
             */
            $this->deleteStoredData($this->getStoredDataKey());
            $this->it->rewind();
        } elseif (( $last_position = $this->getStoredData($this->getStoredDataKey()) ) !== null) {
            /**
             * load last position if exist and delete it
             */
            $this->deleteStoredData($this->getStoredDataKey());
            $this->it->gSeek($last_position);
            $this->it->next();
        }

        $this->startTime();

        /**
         * Iterate
         */
        for (; $this->it->valid(); $this->it->next()) {
            $this->itCount ++;

            /**
             * excetute action for current item
             */
            $this->action($this->it->key(), $this->it->current());

            if ($microThrottling) {
                usleep($microThrottling);
            }

            if ($this->maxIteration && $this->itCount >= $this->maxIteration || $this->checkTime() == false) {
                $this->stop();
                return false;
            }
        }

        return true;
    }

    /**
     * var bool $saveData is fals don't save data. Used in extended classe fot don't save data on stop.
     *
     * @return mixed
     */
    public function stop($saveData = true)
    {
        if ($saveData) {
            if (!$this->saveStoredData($this->getStoredDataKey(), $this->it->getPosition())) {
                return null;
            }
        }

        $position = $this->it->getPosition();
        $this->it->stopIteration();

        return $position;
    }

    protected function saveData()
    {
        if (!$this->saveStoredData($this->getStoredDataKey(), $this->it->getPosition())) {
            return null;
        }
    }

    /**
     *
     * @return mixed
     */
    public function getLastPosition()
    {
        return $this->it->getPosition();
    }

    /**
     *
     * @return number
     */
    public function getIterationsCount()
    {
        return $this->itCount;
    }

    /**
     * @return void
     */
    protected function startTime()
    {
        $this->startTime = microtime(true);
    }

    /**
     *
     * @return boolean
     */
    protected function checkTime()
    {
        if ($this->timeOut) {
            $delta = $this->getExecutionTime() * 1000;
            if ($delta > $this->timeOut) {
                return false;
            }
        }

        return true;
    }

    /**
     *
     * @return number
     */
    public function getExecutionTime()
    {
        return microtime(true) - $this->startTime;
    }

    /**
     * Get stored data key
     */
    abstract public function getStoredDataKey();

    /**
     * load data from previous step if exists
     */
    abstract protected function getStoredData($key);

    /**
     * delete stored data if exists
     */
    abstract protected function deleteStoredData($key);

    /**
     * save data for next step
     */
    abstract protected function saveStoredData($key, $data);

    /**
     *
     * @param mixed $key
     * @param mixed $current
     */
    abstract protected function action($key, $current);

    /**
     * @return GenericSeekableIterator
     */
    abstract protected function getIterator();

    /**
     * @return int
     */
    abstract public function getProgressPerc();
}
