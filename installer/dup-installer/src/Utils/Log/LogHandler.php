<?php

/**
 * Error Hadler logging
 *
 * Standard: PSR-2
 * @link http://www.php-fig.org/psr/psr-2 Full Documentation
 *
 * @package SC\DUPX\Log
 *
 */

namespace Duplicator\Installer\Utils\Log;

use Duplicator\Installer\Core\Bootstrap;

class LogHandler
{
    const MODE_OFF         = 0; // don't write in log
    const MODE_LOG         = 1; // write errors in log file
    const MODE_VAR         = 2; // put php errors in $varModeLog static var
    const SHUTDOWN_TIMEOUT = 'tm';
    const ERRNO_EXCEPTION  = 1073741824; // 31 pos of bit mask

    /**
     * Set error handler
     *
     * @return void
     */
    public static function initErrorHandler()
    {
        Bootstrap::disableBootShutdownFunction();

        set_error_handler(array(__CLASS__, 'error'));
        register_shutdown_function(array(__CLASS__, 'shutdown'));
    }
    /**
     *
     * @var array
     */
    private static $shutdownReturns = array(
        'tm' => 'timeout'
    );

    /**
     *
     * @var int
     */
    private static $handlerMode = self::MODE_LOG;

    /**
     *
     * @var bool // print code reference and errno at end of php error line  [CODE:10|FILE:test.php|LINE:100]
     */
    private static $codeReference = true;

    /**
     *
     * @var bool // print prefix in php error line [PHP ERR][WARN] MSG: .....
     */
    private static $errPrefix = true;

    /**
     *
     * @var string // php errors in MODE_VAR
     */
    private static $varModeLog = '';

    /**
     * Error handler
     *
     * @param  integer $errno   Error level
     * @param  string  $errstr  Error message
     * @param  string  $errfile Error file
     * @param  integer $errline Error line
     * @return void
     */
    public static function error($errno, $errstr, $errfile, $errline)
    {
        switch (self::$handlerMode) {
            case self::MODE_OFF:
                if ($errno == E_ERROR) {
                    $log_message = self::getMessage($errno, $errstr, $errfile, $errline);
                    Log::error($log_message);
                }
                break;
            case self::MODE_VAR:
                self::$varModeLog .= self::getMessage($errno, $errstr, $errfile, $errline) . "\n";
                break;
            case self::MODE_LOG:
            default:
                switch ($errno) {
                    case E_ERROR:
                        $log_message = self::getMessage($errno, $errstr, $errfile, $errline);
                        Log::error($log_message);
                        break;
                    case E_NOTICE:
                    case E_WARNING:
                    default:
                        $log_message = self::getMessage($errno, $errstr, $errfile, $errline);
                        Log::info($log_message);
                        break;
                }
        }
    }

    /**
     * Get error message from erro data
     *
     * @param int    $errno   error code
     * @param string $errstr  error message
     * @param string $errfile file
     * @param int    $errline line
     *
     * @return string
     */
    private static function getMessage($errno, $errstr, $errfile, $errline)
    {
        $result = '';

        if (self::$errPrefix) {
            $result = '[PHP ERR]' . '[' . self::errnoToString($errno) . '] MSG:';
        }

        $result .= $errstr;

        if (self::$codeReference) {
            $result .= ' [CODE:' . $errno . '|FILE:' . $errfile . '|LINE:' . $errline . ']';
            if (Log::isLevel(Log::LV_DEBUG)) {
                Log::info(Log::traceToString(debug_backtrace(), 1));
            }
        }

        return $result;
    }

    /**
     * Errno code to string
     *
     * @param int $errno error code
     *
     * @return string
     */
    public static function errnoToString($errno)
    {
        switch ($errno) {
            case E_PARSE:
                return 'E_PARSE';
            case E_ERROR:
                return 'E_ERROR';
            case E_CORE_ERROR:
                return 'E_CORE_ERROR';
            case E_COMPILE_ERROR:
                return 'E_COMPILE_ERROR';
            case E_USER_ERROR:
                return 'E_USER_ERROR';
            case E_WARNING:
                return 'E_WARNING';
            case E_USER_WARNING:
                return 'E_USER_WARNING';
            case E_COMPILE_WARNING:
                return 'E_COMPILE_WARNING';
            case E_NOTICE:
                return 'E_NOTICE';
            case E_USER_NOTICE:
                return 'E_USER_NOTICE';
            case self::ERRNO_EXCEPTION:
                return 'EXCEPTION';
            default:
                break;
        }
        if (defined('E_STRICT') && $errno === E_STRICT) {
            return 'E_STRICT';
        }
        if (defined('E_RECOVERABLE_ERROR') && $errno === E_RECOVERABLE_ERROR) {
            return 'E_RECOVERABLE_ERROR';
        }
        if (defined('E_DEPRECATED') && $errno === E_DEPRECATED) {
            return 'E_DEPRECATED';
        }
        if (defined('E_USER_DEPRECATED') && $errno === E_USER_DEPRECATED) {
            return 'E_USER_DEPRECATED';
        }
        return 'E_UNKNOWN CODE: ' . $errno;
    }

    /**
     * if setMode is called without params set as default
     *
     * @param int  $mode          log mode
     * @param bool $errPrefix     print prefix in php error line [PHP ERR][WARN] MSG: .....
     * @param bool $codeReference print code reference and errno at end of php error line  [CODE:10|FILE:test.php|LINE:100]
     *
     * @return void
     */
    public static function setMode($mode = self::MODE_LOG, $errPrefix = true, $codeReference = true)
    {
        switch ($mode) {
            case self::MODE_OFF:
            case self::MODE_VAR:
                self::$handlerMode = $mode;
                break;
            case self::MODE_LOG:
            default:
                self::$handlerMode = self::MODE_LOG;
        }

        self::$varModeLog    = '';
        self::$errPrefix     = $errPrefix;
        self::$codeReference = $codeReference;
    }

    /**
     *
     * @return string // return var log string in MODE_VAR
     */
    public static function getVarLog()
    {
        return self::$varModeLog;
    }

    /**
     *
     * @return string // return var log string in MODE_VAR and clean var
     */
    public static function getVarLogClean()
    {
        $result           = self::$varModeLog;
        self::$varModeLog = '';
        return $result;
    }

    /**
     * Set shutdown print string
     *
     * @param string $status status type
     * @param string $str    string to print if is shouddown status
     *
     * @return void
     */
    public static function setShutdownReturn($status, $str)
    {
        self::$shutdownReturns[$status] = $str;
    }

    /**
     * Shutdown handler
     *
     * @return void
     */
    public static function shutdown()
    {
        if (($error = error_get_last())) {
            if (preg_match('/^Maximum execution time (?:.+) exceeded$/i', $error['message'])) {
                echo self::$shutdownReturns[self::SHUTDOWN_TIMEOUT];
            }
            self::error($error['type'], $error['message'], $error['file'], $error['line']);
        }
    }
}
