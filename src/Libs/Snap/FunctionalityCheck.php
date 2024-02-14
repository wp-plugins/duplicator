<?php

/**
 *
 * @package   Duplicator
 * @copyright (c) 2022, Snap Creek LLC
 */

namespace Duplicator\Libs\Snap;

use Exception;

/**
 * Functionality to check
 */
class FunctionalityCheck
{
    const TYPE_FUNCTION = 1;
    const TYPE_CLASS    = 2;

    /** @var int Enum type */
    protected $type = 0;
    /** @var string item key to test */
    protected $itemKey = '';
    /** @var bool true if this item is required */
    protected $required = false;
    /** @var string link to documntation */
    public $link = '';
    /** @var string html troubleshoot */
    public $troubleshoot = '';
    /** @var ?callable if is set is called when check fail */
    protected $failCallback = null;


    /**
     * Class contructor
     *
     * @param int    $type         Enum type
     * @param string $key          item key to test
     * @param bool   $required     true if this item is required
     * @param string $link         link to documntation
     * @param string $troubleshoot html troubleshoot
     */
    public function __construct($type, $key, $required = false, $link = '', $troubleshoot = '')
    {
        switch ($type) {
            case self::TYPE_FUNCTION:
            case self::TYPE_CLASS:
                $this->type =  $type;
                break;
            default:
                throw new Exception('Invalid item type');
        }

        if (strlen($key) == 0) {
            throw new Exception('Key can\'t be empty');
        }
        $this->required     = $required;
        $this->itemKey      = (string) $key;
        $this->link         = (string) $link;
        $this->troubleshoot = (string) $troubleshoot;
    }

    /**
     * Get the value of type
     *
     * @return int
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Get the value of itemKey
     *
     * @return string
     */
    public function getItemKey()
    {
        return $this->itemKey;
    }

    /**
     * true if is required
     *
     * @return bool
     */
    public function isRequired()
    {
        return $this->required;
    }

    /**
     * Check if item exists
     *
     * @return bool
     */
    public function check()
    {
        $result = false;

        switch ($this->type) {
            case self::TYPE_FUNCTION:
                $result = function_exists($this->itemKey);
                break;
            case self::TYPE_CLASS:
                $result = SnapUtil::classExists($this->itemKey);
                break;
            default:
                throw new Exception('Invalid item type');
        }

        if ($result == false && is_callable($this->failCallback)) {
            call_user_func($this->failCallback, $this);
        }
        return $result;
    }

    /**
     * Set the value of failCallback
     *
     * @param callable $failCallback fail callback function
     *
     * @return void
     */
    public function setFailCallback($failCallback)
    {
        $this->failCallback = $failCallback;
    }

    /**
     * Check all Functionalities in list
     *
     * @param self[] $funcs        Functionalities list
     * @param bool   $requiredOnly if true skip functs not required
     * @param self[] $notPassList  list of items that not have pass the test
     *
     * @return bool
     */
    public static function checkList($funcs, $requiredOnly = false, &$notPassList = array())
    {
        if (!is_array($funcs)) {
            throw new Exception('funcs must be an array');
        }

        $notPassList = array();

        foreach ($funcs as $func) {
            if ($requiredOnly && !$func->isRequired()) {
                continue;
            }

            if ($func->check() === false) {
                $notPassList[] = $func;
            }
        }

        return (count($notPassList) === 0);
    }
}
