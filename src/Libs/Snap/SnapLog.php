<?php

/**
 *
 * @package   Duplicator
 * @copyright (c) 2022, Snap Creek LLC
 */

namespace Duplicator\Libs\Snap;

use Error;
use Exception;

class SnapLog
{
    /** @var ?string */
    public static $logFilepath = null;
    /** @var ?resource */
    public static $logHandle = null;

    /**
     * Init log file
     *
     * @param string $logFilepath lof file path
     *
     * @return void
     */
    public static function init($logFilepath)
    {
        self::$logFilepath = $logFilepath;
    }

    /**
     * write in PHP error log with DUP prefix
     *
     * @param string $message error message
     * @param int    $type    error type
     *
     * @return bool true on success or false on failure.
     *
     * @link https://php.net/manual/en/function.error-log.php
     */
    public static function phpErr($message, $type = 0)
    {
        if (function_exists('error_log')) {
            return error_log('DUP:' . $message, $type);
        } else {
            return true;
        }
    }

    /**
     * Remove file log if exists
     *
     * @return void
     */
    public static function clearLog()
    {
        if (file_exists(self::$logFilepath)) {
            if (self::$logHandle !== null) {
                fflush(self::$logHandle);
                fclose(self::$logHandle);
                self::$logHandle = null;
            }
            @unlink(self::$logFilepath);
        }
    }

    /**
     * Write in log passed object
     *
     * @param string  $s     log string
     * @param mixed   $o     object to print
     * @param boolean $flush if true flush log file
     *
     * @return void
     */
    public static function logObject($s, $o, $flush = false)
    {
        self::log($s, $flush);
        self::log(print_r($o, true), $flush);
    }

    /**
     * Write in log file
     *
     * @param string    $s                       string to write
     * @param boolean   $flush                   if true flush log file
     * @param ?callable $callingFunctionOverride @deprecated 4.0.4 not used
     *
     * @return void
     */
    public static function log($s, $flush = false, $callingFunctionOverride = null)
    {
        //   echo "{$s}<br/>";
        $lfp = self::$logFilepath;
        //  echo "logging $s to {$lfp}<br/>";
        if (self::$logFilepath === null) {
            throw new Exception('Logging not initialized');
        }

        if (isset($_SERVER['REQUEST_TIME_FLOAT'])) {
            $timepart = $_SERVER['REQUEST_TIME_FLOAT'];
        } else {
            $timepart = $_SERVER['REQUEST_TIME'];
        }

        $thread_id = sprintf("%08x", abs(crc32($_SERVER['REMOTE_ADDR'] . $timepart . $_SERVER['REMOTE_PORT'])));

        $s = $thread_id . ' ' . date('h:i:s') . ":$s";

        if (self::$logHandle === null) {
            self::$logHandle = fopen(self::$logFilepath, 'a');
        }

        fwrite(self::$logHandle, "$s\n");

        if ($flush) {
            fflush(self::$logHandle);

            fclose(self::$logHandle);

            self::$logHandle = fopen(self::$logFilepath, 'a');
        }
    }

    /**
     * Get formatted string fo value
     *
     * @param mixed $var           value to convert to string
     * @param bool  $checkCallable if true check if var is callable and display it
     *
     * @return string
     */
    public static function v2str($var, $checkCallable = false)
    {
        if ($checkCallable && is_callable($var)) {
            return '(callable) ' . print_r($var, true);
        }
        switch (gettype($var)) {
            case "boolean":
                return $var ? 'true' : 'false';
            case "integer":
            case "double":
                return (string) $var;
            case "string":
                return '"' . $var . '"';
            case "array":
            case "object":
                return print_r($var, true);
            case "resource":
            case "resource (closed)":
            case "NULL":
            case "unknown type":
            default:
                return gettype($var);
        }
    }

    /**
     * Get backtrace of calling line
     *
     * @param string $message message
     *
     * @return string
     */
    public static function getCurrentbacktrace($message = 'getCurrentLineTrace')
    {
        $callers = debug_backtrace();
        array_shift($callers);
        $file    = $callers[0]['file'];
        $line    = $callers[0]['line'];
        $result  = 'BACKTRACE: ' . $message . "\n";
        $result .= "\t[" . $file . ':' . $line . "]\n";
        $result .= self::traceToString($callers, 1, true);
        return $result;
    }

    /**
     * Get trace string
     *
     * @param mixed[] $callers   result of debug_backtrace
     * @param int     $fromLevel level to start
     * @param bool    $tab       if true apply tab foreach line
     *
     * @return string
     */
    public static function traceToString($callers, $fromLevel = 0, $tab = false)
    {
        $result = '';
        for ($i = $fromLevel; $i < count($callers); $i++) {
            $result .= ($tab ? "\t" : '');
            $trace   = $callers[$i];
            if (!empty($trace['class'])) {
                $result .= str_pad('TRACE[' . $i . '] CLASS___: ' . $trace['class'] . $trace['type'] . $trace['function'], 45, ' ');
            } else {
                $result .= str_pad('TRACE[' . $i . '] FUNCTION: ' . $trace['function'], 45, ' ');
            }
            if (isset($trace['file'])) {
                $result .= ' FILE: ' . $trace['file'] . '[' . $trace['line'] . ']';
            } else {
                $result .= ' NO FILE';
            }
            $result .= "\n";
        }
        return $result;
    }

    /**
     * Get exception message file line trace
     *
     * @param Exception|Error $e              exception object
     * @param bool            $displayMessage if true diplay exception message
     *
     * @return string
     */
    public static function getTextException($e, $displayMessage = true)
    {
        $result = ($displayMessage ? $e->getMessage() . "\n" : '');
        return $result . "FILE:" . $e->getFile() . '[' . $e->getLIne() . "]\n" .
        "TRACE:\n" . $e->getTraceAsString();
    }
}
