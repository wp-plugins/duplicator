<?php

/**
 *
 * @package   Duplicator
 * @copyright (c) 2021, Snapcreek LLC
 */

namespace Duplicator\Libs\DupArchive\States;

use Duplicator\Libs\DupArchive\Processors\DupArchiveProcessingFailure;

/**
 * Dup archive state base
 */
abstract class DupArchiveStateBase
{
    const MAX_FAILURE = 1000;

    public $basePath          = '';
    public $archivePath       = '';
    public $isCompressed      = false;
    public $currentFileOffset = -1;
    public $archiveOffset     = -1;
    public $timeSliceInSecs   = -1;
    public $working           = false;
    /** @var DupArchiveProcessingFailure[] */
    public $failures          = array();
    public $failureCount      = 0;
    public $startTimestamp    = -1;
    public $throttleDelayInUs = 0;
    public $timeoutTimestamp  = -1;
    public $timerEnabled      = true;
    public $isRobust          = false;

    /**
     * Class constructor
     */
    public function __construct()
    {
    }

    /**
     * Check if is present a critical failure
     *
     * @return boolean
     */
    public function isCriticalFailurePresent()
    {
        if (count($this->failures) > 0) {
            foreach ($this->failures as $failure) {
                if ($failure->isCritical) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Che failure summary
     *
     * @param boolean $includeCritical include critical failures
     * @param boolean $includeWarnings include warnings failures
     *
     * @return string
     */
    public function getFailureSummary($includeCritical = true, $includeWarnings = false)
    {
        if (count($this->failures) > 0) {
            $message = '';

            foreach ($this->failures as $failure) {
                if ($includeCritical || !$failure->isCritical) {
                    $message .= "\n" . $this->getFailureString($failure);
                }
            }

            return $message;
        } else {
            if ($includeCritical) {
                if ($includeWarnings) {
                    return 'No errors or warnings.';
                } else {
                    return 'No errors.';
                }
            } else {
                return 'No warnings.';
            }
        }
    }

    /**
     * Return failure string from item
     *
     * @param DupArchiveProcessingFailure $failure failure item
     *
     * @return string
     */
    public function getFailureString(DupArchiveProcessingFailure $failure)
    {
        $s = '';

        if ($failure->isCritical) {
            $s = 'CRITICAL: ';
        }

        return "{$s}{$failure->subject} : {$failure->description}";
    }

    /**
     * Add failure item
     *
     * @param int     $type        failure type enum
     * @param string  $subject     failure subject
     * @param string  $description failure description
     * @param boolean $isCritical  true if is critical
     *
     * @return DupArchiveProcessingFailure
     */
    public function addFailure($type, $subject, $description, $isCritical = true)
    {
        $this->failureCount++;
        if ($this->failureCount > self::MAX_FAILURE) {
            return false;
        }

        $failure = new DupArchiveProcessingFailure();

        $failure->type        = $type;
        $failure->subject     = $subject;
        $failure->description = $description;
        $failure->isCritical  = $isCritical;

        $this->failures[] = $failure;

        return $failure;
    }

    /**
     * Set start time
     *
     * @return void
     */
    public function startTimer()
    {
        if ($this->timerEnabled) {
            $this->timeoutTimestamp = time() + $this->timeSliceInSecs;
        }
    }

    /**
     * Check if is timeout
     *
     * @return bool
     */
    public function timedOut()
    {
        if ($this->timerEnabled) {
            if ($this->timeoutTimestamp != -1) {
                return time() >= $this->timeoutTimestamp;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }
}
