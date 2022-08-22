<?php

/**
 * plugin custom actions
 *
 * Standard: PSR-2
 * @link http://www.php-fig.org/psr/psr-2 Full Documentation
 *
 * @package SC\DUPX\U
 *
 */

defined('ABSPATH') || defined('DUPXABSPATH') || exit;

class DUPX_Plugin_custom_actions
{
    const BY_DEFAULT_AUTO     = 'auto';
    const BY_DEFAULT_DISABLED = 'disabled';
    const BY_DEFAULT_ENABLED  = 'enabled';

    /** @var string */
    protected $slug = null;
    /** @var bool|callable */
    protected $byDefaultStatus = self::BY_DEFAULT_AUTO;
    /** @var bool|callable */
    protected $enableAfterLogin = false;
    /** @var string */
    protected $byDefaultMessage = '';

    /**
     * Class constructor
     *
     * @param string          $slug             plugin slug
     * @param string|callable $byDefaultStatus  set plugin status
     * @param bool|callable   $enableAfterLogin enable plugin after login
     * @param string|callable $byDefaultMessage message if status change
     */
    public function __construct(
        $slug,
        $byDefaultStatus = self::BY_DEFAULT_AUTO,
        $enableAfterLogin = false,
        $byDefaultMessage = ''
    ) {
        $this->slug             = $slug;
        $this->byDefaultStatus  = $byDefaultStatus;
        $this->enableAfterLogin = $enableAfterLogin;
        $this->byDefaultMessage = $byDefaultMessage;
    }

    /**
     * Return by defualt status
     *
     * @return string by default enum
     */
    public function byDefaultStatus()
    {
        if (is_callable($this->byDefaultStatus)) {
            return call_user_func($this->byDefaultStatus, $this);
        } else {
            return $this->byDefaultStatus;
        }
    }

    /**
     * return true if plugin must be enabled after login
     *
     * @return boolean
     */
    public function isEnableAfterLogin()
    {
        if (is_callable($this->enableAfterLogin)) {
            return call_user_func($this->enableAfterLogin, $this);
        } else {
            return $this->enableAfterLogin;
        }
    }

    /**
     * By default message
     *
     * @return string
     */
    public function byDefaultMessage()
    {
        if (is_callable($this->byDefaultMessage)) {
            return call_user_func($this->byDefaultMessage, $this);
        } else {
            return $this->byDefaultMessage;
        }
    }
}
