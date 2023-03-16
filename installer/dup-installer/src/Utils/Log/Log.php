<?php

/**
 * Class used to log information
 *
 * Standard: PSR-2
 *
 * @link http://www.php-fig.org/psr/psr-2 Full Documentation
 *
 * @package SC\DUPX\Log
 */

namespace Duplicator\Installer\Utils\Log;

use Duplicator\Installer\Core\Params\PrmMng;
use Duplicator\Libs\Snap\SnapIO;

/**
 * Log
 * Class used to log information  */
class Log
{
    /**
     * Maximum length of the log on the log.
     * Prevents uncontrolled increase in log size. This dimension should never be reached
     */
    const MAX_LENGTH_FWRITE = 50000;
    const LV_DEFAULT        = 1;
    const LV_DETAILED       = 2;
    const LV_DEBUG          = 3;
    const LV_HARD_DEBUG     = 4;

    /**
     * if true throw exception on error else die on error
     *
     * @var bool
     */
    private static $thowExceptionOnError = false;

    /**
     * log level
     *
     * @var int
     */
    private static $logLevel = self::LV_DEFAULT;

    /**
     * num of \t before log string.
     *
     * @var int
     */
    private static $indentation = 0;

    /**
     *
     * @var float
     */
    private static $microtimeStart = 0;

    /**
     *
     * @var callable
     */
    private static $postprocessCallback = null;

    /**
     * @var callable
     */
    private static $afterFatalErrorCallback = null;

    /**
     *
     * @var null|resource
     */
    private static $logHandle = null;

    /**
     * set log level from param manager
     *
     * @return void
     */
    public static function setLogLevel()
    {
        self::$logLevel = PrmMng::getInstance()->getValue(PrmMng::PARAM_LOGGING);
    }

    /**
     * Used to write debug info to the text log file
     *
     * @param string $msg     Any text data
     * @param int    $logging Log level
     * @param bool   $flush   if true flush file log
     *
     * @return void
     */
    public static function info($msg, $logging = self::LV_DEFAULT, $flush = false)
    {
        if ($logging > self::$logLevel) {
            return;
        }

        $preLog = '';
        if (self::$indentation) {
            $preLog .= str_repeat("\t", self::$indentation);
        }
        if (self::$logLevel >= self::LV_DETAILED) {
            $preLog .= sprintf('[DELTA:%10.5f] ', microtime(true) - self::$microtimeStart);
        }
        if (is_callable(self::$postprocessCallback)) {
            $msg = call_user_func(self::$postprocessCallback, $msg);
        }

        @fwrite(self::getFileHandle(), $preLog . $msg . "\n", self::MAX_LENGTH_FWRITE);

        if ($flush) {
            self::flush();
        }
    }

    /**
     *
     * @return bool <p>Returns <b><code>true</code></b> on success or <b><code>false</code></b> on failure.</p>
     */
    public static function clearLog()
    {
        self::close();
        if (file_exists(self::getLogFilePath())) {
            return unlink(self::getLogFilePath());
        } else {
            return true;
        }
    }

    /**
     *
     * @return string
     */
    protected static function getLogFileName()
    {
        return 'dup-installer-log__' . \DUPX_Security::getInstance()->getSecondaryPackageHash() . '.txt';
    }

    /**
     *
     * @return string
     */
    public static function getLogFilePath()
    {
        return DUPX_INIT . '/' . self::getLogFileName();
    }

    /**
     *
     * @return string
     */
    public static function getLogFileUrl()
    {
        return DUPX_INIT_URL . '/' . self::getLogFileName() . '?now=' . $GLOBALS['NOW_TIME'];
    }

    /**
     * Get trace string
     *
     * @param array $callers   result of debug_backtrace
     * @param int   $fromLevel level to start
     *
     * @return string
     */
    public static function traceToString($callers, $fromLevel = 0)
    {
        $result = '';
        for ($i = $fromLevel; $i < count($callers); $i++) {
            $trace = $callers[$i];
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
     * Set post process callback
     *
     * @param callable $callback callback function
     *
     * @return void
     */
    public static function setPostProcessCallback($callback)
    {
        self::$postprocessCallback = is_callable($callback) ? $callback : null;
    }

    /**
     * Set after fatal error callback
     *
     * @param callable $callback callback function
     *
     * @return void
     */
    public static function setAfterFatalErrorCallback($callback)
    {
        self::$afterFatalErrorCallback = is_callable($callback) ? $callback : null;
    }

    /**
     * Reset time counter
     *
     * @param int     $logging  log level
     * @param boolean $fileInfo Log file info (file, line)
     *
     * @return void
     */
    public static function resetTime($logging = self::LV_DEFAULT, $fileInfo = true)
    {
        self::$microtimeStart = microtime(true);
        if ($logging > self::$logLevel) {
            return;
        }
        $callers = debug_backtrace();
        $file    = $callers[0]['file'];
        $line    = $callers[0]['line'];
        Log::info('LOG-TIME' . ($fileInfo ? '[' . $file . ':' . $line . ']' : '') . ' RESET TIME', $logging);
    }

    /**
     * Log time delta from last resetTime call
     *
     * @param string $msg      message
     * @param int    $logging  log level
     * @param bool   $fileInfo if strue write file info (file, line)
     *
     * @return void
     */
    public static function logTime($msg = '', $logging = self::LV_DEFAULT, $fileInfo = true)
    {
        if ($logging > self::$logLevel) {
            return;
        }
        $callers = debug_backtrace();
        $file    = $callers[0]['file'];
        $line    = $callers[0]['line'];

        if ($fileInfo) {
            Log::info(
                sprintf('LOG-TIME[%s:%s][DELTA:%10.5f] ', $file, $line, microtime(true) - self::$microtimeStart) . (empty($msg) ? '' : ' MESSAGE:' . $msg),
                $logging
            );
        } else {
            Log::info(sprintf('LOG-TIME[DELTA:%10.5f] ', microtime(true) - self::$microtimeStart) . (empty($msg) ? '' : ' MESSAGE:' . $msg), $logging);
        }
    }

    /**
     * Increment indentation
     *
     * @return void
     */
    public static function incIndent()
    {
        self::$indentation++;
    }

    /**
     * Decrease indentation
     *
     * @return void
     */
    public static function decIndent()
    {
        if (self::$indentation > 0) {
            self::$indentation--;
        }
    }

    /**
     * Reset indentation
     *
     * @return void
     */
    public static function resetIndent()
    {
        self::$indentation = 0;
    }

    /**
     * Return true if log level is >= of loggin level
     *
     * @param int $logging log level
     *
     * @return boolean
     */
    public static function isLevel($logging)
    {
        return $logging <= self::$logLevel;
    }

    /**
     * Log passed object
     *
     * @param string $msg     log message
     * @param mixed  $object  object to log
     * @param int    $logging log level
     *
     * @return void
     */
    public static function infoObject($msg, &$object, $logging = self::LV_DEFAULT)
    {
        $msg = $msg . "\n" . print_r($object, true);
        self::info($msg, $logging);
    }

    /**
     * Flush log file
     *
     * @return void
     */
    public static function flush()
    {
        if (is_resource(self::$logHandle)) {
            fflush(self::$logHandle);
        }
    }

    /**
     * Close log file
     *
     * @return void
     */
    public static function close()
    {
        if (is_null(self::$logHandle)) {
            return true;
        }

        if (is_resource(self::$logHandle)) {
            fclose(self::$logHandle);
        }
        self::$logHandle = null;
        return true;
    }

    /**
     * Get log file stream
     *
     * @return resource
     */
    public static function getFileHandle()
    {
        if (is_resource(self::$logHandle)) {
            return self::$logHandle;
        }

        if (!is_writable(dirname(self::getLogFilePath()))) {
            throw new \Exception('Can\'t write in dup-installer folder, please check the dup-installer permission folder');
        }

        if (file_exists(self::getLogFilePath())) {
            SnapIO::chmod(self::getLogFilePath(), 'u+rw');
        }

        if ((self::$logHandle = fopen(self::getLogFilePath(), "a+")) === false) {
            self::$logHandle = null;
            throw new \Exception('Can\'t open the log file, please check the dup-installer permission folder');
        }

        SnapIO::chmod(self::getLogFilePath(), 'u+rw');

        return self::$logHandle;
    }

    /**
     * Log error and die or thore exception if self::$thowExceptionOnError is true
     *
     * @param string $errorMessage error message
     *
     * @return void
     */
    public static function error($errorMessage)
    {
        $breaks  = array("<br />", "<br>", "<br/>");
        $spaces  = array("&nbsp;");
        $log_msg = str_ireplace($breaks, "\r\n", $errorMessage);
        $log_msg = str_ireplace($spaces, " ", $log_msg);
        $log_msg = strip_tags($log_msg);

        self::info("\nINSTALLER ERROR:\n{$log_msg}\n");

        if (is_callable(self::$afterFatalErrorCallback)) {
            call_user_func(self::$afterFatalErrorCallback);
        }

        if (self::$thowExceptionOnError) {
            throw new \Exception($errorMessage);
        } else {
            self::close();
            die("<div class='dupx-ui-error'><hr size='1' /><b style='color:#B80000;'>INSTALL ERROR!</b><br/><pre>{$errorMessage}</pre></div>");
        }
    }

    /**
     * Get log exception string
     *
     * @param Exception $e     exception object
     * @param string    $title log message
     *
     * @return string
     */
    public static function getLogException($e, $title = 'EXCEPTION ERROR: ')
    {
        return $title . ' ' . $e->getMessage() . "\n" .
            "\tFILE:" . $e->getFile() . '[' . $e->getLIne() . "]\n" .
            "\tTRACE:\n" . $e->getTraceAsString();
    }

    /**
     * Log exception
     *
     * @param Exception $e       exception object
     * @param int       $logging log level
     * @param string    $title   log message
     *
     * @return void
     */
    public static function logException($e, $logging = self::LV_DEFAULT, $title = 'EXCEPTION ERROR: ')
    {
        if ($logging <= self::$logLevel) {
            Log::info("\n" . self::getLogException($e, $title) . "\n");
        }
    }

    /**
     * If set true error function thorw exception instead die
     *
     * @param boolean $set enable/disable thorw exception
     *
     * @return void
     */
    public static function setThrowExceptionOnError($set)
    {
        self::$thowExceptionOnError = (bool) $set;
    }

    /**
     * Get string from generic value
     *
     * @param mixed $var           value
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
}
