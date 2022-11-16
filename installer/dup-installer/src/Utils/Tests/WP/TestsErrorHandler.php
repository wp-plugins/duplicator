<?php

/**
 * Error handler for test scripts
 * *******************
 * IMPORTANT
 * Don\'t use snap lib functions o other duplicator functions
 * *******************
 *
 * Standard: PSR-2
 * @link http://www.php-fig.org/psr/psr-2 Full Documentation
 *
 * @package SC\DUPX\U
 *
 */

namespace Duplicator\Installer\Utils\Tests\WP;

class TestsErrorHandler
{
    const ERR_TYPE_ERROR      = 'error';
    const ERR_TYPE_WARNING    = 'warning';
    const ERR_TYPE_NOTICE     = 'notice';
    const ERR_TYPE_DEPRECATED = 'deprecated';
    const ERRNO_EXCEPTION     = 1073741824; // 31 pos of bit mask

    protected static $errors = array();

    /**
     * If it is null a json is displayed otherwise the callback function is executed in the shutd
     *
     * @var null| callable
     */
    protected static $shutdownCallback = null;

    /**
     * register error handlers
     *
     * @return void
     */
    public static function register()
    {
        @register_shutdown_function(array(__CLASS__, 'shutdown'));
        @set_error_handler(array(__CLASS__, 'error'));
        @set_exception_handler(array(__CLASS__, 'exception'));
    }

    /**
     * @param callable $callback shutdown callback
     * @return void
     */
    public static function setShutdownCallabck($callback)
    {
        if (is_callable($callback)) {
            self::$shutdownCallback = $callback;
        } else {
            self::$shutdownCallback = null;
        }
    }

    /**
     * add error on list
     *
     * @param int    $errno   error number
     * @param string $errstr  error string
     * @param string $errfile error file
     * @param int    $errline error line
     * @param array  $trace   error trace
     * @return void
     */
    protected static function addError($errno, $errstr, $errfile, $errline, $trace)
    {
        $newError = array(
            'error_cat' => self::getErrorCategoryFromErrno($errno),
            'errno'     => $errno,
            'errno_str' => self::errnoToString($errno),
            'errstr'    => $errstr,
            'errfile'   => $errfile,
            'errline'   => $errline,
            'trace'     => array_map(array(__CLASS__, 'normalizeTraceElement'), $trace)
        );

        self::$errors[] = $newError;

        if (function_exists('error_clear_last')) {
            error_clear_last();
        }
    }

    /**
     * @param array $error the error array
     * @return string human-readable error message with trace
     */
    public static function errorToString($error)
    {
        $result  = $error['errno_str'] . ' ' . $error['errstr'] . "\n";
        $result .= "\t" . 'FILE: ' . $error['errfile'] . '[' . $error['errline'] . ']' . "\n";
        $result .= "\t--- TRACE ---\n";
        foreach ($error['trace'] as $trace) {
            $result .= "\t";
            if (!empty($trace['class'])) {
                $result .= str_pad('CLASS___: ' . $trace['class'] . $trace['type'] . $trace['function'], 40, ' ');
            } else {
                $result .= str_pad('FUNCTION: ' . $trace['function'], 40, ' ');
            }
            $result .= 'FILE: ' . $trace['file'] . '[' . $trace['line'] . ']' . "\n";
        }

        return $result;
    }

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
        $trace = debug_backtrace();
        array_shift($trace);
        self::adderror($errno, $errstr, $errfile, $errline, $trace);
    }

    /**
     * Exception handler
     *
     * @param Exception|Error $e // Throwable in php 7
     * @return void
     */
    public static function exception($e)
    {
        self::adderror(self::ERRNO_EXCEPTION, $e->getMessage(), $e->getFile(), $e->getLine(), $e->getTrace());
    }

    /**
     * Shutdown handler
     *
     * @return void
     */
    public static function shutdown()
    {
        self::obCleanAll();

        if (($error = error_get_last())) {
            self::error($error['type'], $error['message'], $error['file'], $error['line']);
        }
        ob_end_clean();

        if (is_callable(self::$shutdownCallback)) {
            call_user_func(self::$shutdownCallback, self::$errors);
        } else {
            echo json_encode(self::$errors);
        }

        // prevent other shutdown functions
        exit();
    }

    /**
     * Close all buffers and return content
     *
     * @param bool $getContent If true it returns buffer content, otherwise it is discarded
     *
     * @return string
     */
    protected static function obCleanAll($getContent = true)
    {
        $result = '';
        for ($i = 0; $i < ob_get_level(); $i++) {
            if ($getContent) {
                $result .= ob_get_contents();
            }
            ob_clean();
        }
        return $result;
    }

    /**
     * @param array $elem normalize error element
     * @return array
     */
    public static function normalizeTraceElement($elem)
    {
        if (!is_array($elem)) {
            $elem = array();
        }

        unset($elem['args']);
        unset($elem['object']);

        return array_merge(array(
            'file'     => '',
            'line'     => -1,
            'function' => '',
            'class'    => '',
            'type'     => ''), $elem);
    }

    /**
     *
     * @param int $errno error number
     * @return string
     */
    public static function getErrorCategoryFromErrno($errno)
    {
        switch ($errno) {
            case E_PARSE:
            case E_ERROR:
            case E_CORE_ERROR:
            case E_COMPILE_ERROR:
            case E_USER_ERROR:
            case self::ERRNO_EXCEPTION:
                return self::ERR_TYPE_ERROR;
            case E_WARNING:
            case E_USER_WARNING:
            case E_COMPILE_WARNING:
                return self::ERR_TYPE_WARNING;
            case E_NOTICE:
            case E_USER_NOTICE:
                return self::ERR_TYPE_NOTICE;
            default:
                break;
        }
        if (defined('E_STRICT') && $errno === E_STRICT) {
            return self::ERR_TYPE_WARNING;
        }
        if (defined('E_RECOVERABLE_ERROR') && $errno === E_RECOVERABLE_ERROR) {
            return self::ERR_TYPE_WARNING;
        }
        if (defined('E_DEPRECATED') && $errno === E_DEPRECATED) {
            return self::ERR_TYPE_DEPRECATED;
        }
        if (defined('E_USER_DEPRECATED') && $errno === E_USER_DEPRECATED) {
            return self::ERR_TYPE_DEPRECATED;
        }
        return self::ERR_TYPE_WARNING;
    }

    /**
     *
     * @param int $errno error number
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
}
