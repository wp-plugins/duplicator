<?php

/**
 * param descriptor
 *
 * Standard: PSR-2
 * @link http://www.php-fig.org/psr/psr-2 Full Documentation
 *
 * @package SC\DUPX\U
 *
 */

namespace Duplicator\Installer\Core\Params\Items;

use Duplicator\Installer\Utils\Log\Log;

/**
 * this class describes the value of a parameter.
 * therefore the type of data is sanitization and validation.
 * In addition to other features such as, for example, if it is a persistent parameter.
 *
 */
class ParamItem
{
    const INPUT_GET             = 'g';
    const INPUT_POST            = 'p';
    const INPUT_REQUEST         = 'r';
    const INPUT_COOKIE          = 'c';
    const INPUT_SERVER          = 's';
    const INPUT_ENV             = 'e';
    const TYPE_STRING           = 'str';
    const TYPE_ARRAY_STRING     = 'arr_str';
    const TYPE_ARRAY_MIXED      = 'arr_mix';
    const TYPE_INT              = 'int';
    const TYPE_ARRAY_INT        = 'arr_int';
    const TYPE_BOOL             = 'bool';
    const STATUS_INIT           = 'init';
    const STATUS_OVERWRITE      = 'owr';
    const STATUS_UPD_FROM_INPUT = 'updinp';

    /**
     *  validate regexes for test input
     */
    const VALIDATE_REGEX_INT_NUMBER          = '/^[\+\-]?[0-9]+$/';
    const VALIDATE_REGEX_INT_NUMBER_EMPTY    = '/^[\+\-]?[0-9]*$/'; // can be empty
    const VALIDATE_REGEX_AZ_NUMBER           = '/^[A-Za-z0-9]+$/';
    const VALIDATE_REGEX_AZ_NUMBER_EMPTY     = '/^[A-Za-z0-9]*$/'; // can be empty
    const VALIDATE_REGEX_AZ_NUMBER_SEP       = '/^[A-Za-z0-9_\-]+$/'; // laddate Az 09 plus - and _
    const VALIDATE_REGEX_AZ_NUMBER_SEP_EMPTY = '/^[A-Za-z0-9_\-]*$/'; // laddate Az 09 plus - and _, can be empty
    const VALIDATE_REGEX_DIR_PATH            = '/^([a-zA-Z]:[\\\\\/]|\/|\\\\\\\\|\/\/)[^<>\0]+$/';
    const VALIDATE_REGEX_FILE_PATH           = '/^([a-zA-Z]:[\\\\\/]|\/|\\\\\\\\|\/\/)[^<>\0]+$/';

    protected $name   = null;
    protected $type   = null;
    protected $attr   = array();
    protected $value  = null;
    protected $status = self::STATUS_INIT;

    /**
     * Class constructor
     *
     * @param string $name param identifier
     * @param string $type TYPE_STRING | TYPE_ARRAY_STRING | ...
     * @param array  $attr list of attributes
     */
    public function __construct($name, $type, $attr = null)
    {
        if (empty($name) || strlen($name) < 4) {
            throw new \Exception('the name can\'t be empty or len can\'t be minor of 4');
        }
        $this->type = $type;
        $this->attr = array_merge(static::getDefaultAttrForType($type), (array) $attr);
        if ($type == self::TYPE_ARRAY_STRING || $type == self::TYPE_ARRAY_INT || $type == self::TYPE_ARRAY_MIXED) {
            $this->attr['default'] = (array) $this->attr['default'];
        }
        $this->name  = $name;
        $this->value = $this->getSanitizeValue($this->attr['default']);

        if (is_null($this->attr['defaultFromInput'])) {
            $this->attr['defaultFromInput'] = $this->attr['default'];
        } else {
            if ($type == self::TYPE_ARRAY_STRING || $type == self::TYPE_ARRAY_INT || $type == self::TYPE_ARRAY_MIXED) {
                $this->attr['defaultFromInput'] = (array) $this->attr['defaultFromInput'];
            }
        }
    }

    /**
     * this funtion return the discursive label
     *
     * @return string
     */
    public function getLabel()
    {
        return $this->name;
    }

    /**
     * get current item identifier
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     *  get current item value
     *
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     *
     * @return string // STATUS_INIT | STATUS_OVERWRITE | STATUS_UPD_FROM_INPUT
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set item status with overwrite value
     *
     * @return void
     */
    public function setOveriteStatus()
    {
        $this->status = self::STATUS_OVERWRITE;
    }

    /**
     * if it is true, this object is defined as persistent and will be saved in the parameter persistence file otherwise the param manager
     * will not save this value and at each call of the script the parameter will assume the default value.
     *
     * @return bool
     */
    public function isPersistent()
    {
        return $this->attr['persistence'];
    }

    /**
     * return the invalid param message or empty string
     *
     * @return string
     */
    public function getInvalidMessage()
    {
        if (is_callable($this->attr['invalidMessage'])) {
            return call_user_func($this->attr['invalidMessage'], $this);
        } else {
            return (string) $this->attr['invalidMessage'];
        }
    }

    /**
     * Set the invalid param message
     *
     * @param $message invalid message
     *
     * @return string
     */
    public function setInvalidMessage($message)
    {
        $this->attr['invalidMessage'] = (string) $message;
    }

    /**
     * Update item attribute
     *
     * @param string $key   attribute key
     * @param mixed  $value value
     *
     * @return void
     */
    public function setAttr($key, $value)
    {
        $this->attr[$key] = $value;
    }

    /**
     * Set param value
     *
     * @param mixed $value value to set
     *
     * @return boolean false if value isn't validated
     */
    public function setValue($value)
    {
        $validateValue = null;
        if (!$this->isValid($value, $validateValue)) {
            return false;
        }

        $this->value = $validateValue;
        return true;
    }

    /**
     * Get super object from method
     *
     * @param string $method query string method
     *
     * @return array return the reference
     */
    protected static function getSuperObjectByMethod($method)
    {
        $superObject = array();
        switch ($method) {
            case self::INPUT_GET:
                $superObject = &$_GET;
                break;
            case self::INPUT_POST:
                $superObject = &$_POST;
                break;
            case self::INPUT_REQUEST:
                $superObject = &$_REQUEST;
                break;
            case self::INPUT_COOKIE:
                $superObject = &$_COOKIE;
                break;
            case self::INPUT_SERVER:
                $superObject = &$_SERVER;
                break;
            case self::INPUT_ENV:
                $superObject = &$_ENV;
                break;
            default:
                throw new \Exception('INVALID SUPER OBJECT METHOD  ' . Log::v2str($method));
        }
        return $superObject;
    }

    /**
     * Return true if value is in input method
     *
     * @param array $superObject query string super object
     *
     * @return bool
     */
    protected function isValueInInput($superObject)
    {
        return isset($superObject[$this->name]);
    }

    /**
     * update the value from input if exists ot set the default
     * sanitation and validation are performed
     *
     * @param string $method query string method
     *
     * @return boolean false if value isn't validated
     */
    public function setValueFromInput($method = self::INPUT_POST)
    {
        $superObject = self::getSuperObjectByMethod($method);

        Log::info(
            'SET VALUE FROM INPUT KEY [' . $this->name . '] VALUE[' .
            Log::v2str(isset($superObject[$this->name]) ? $superObject[$this->name] : '') .
            ']',
            Log::LV_DEBUG
        );

        if (!$this->isValueInInput($superObject)) {
            $inputValue = $this->attr['defaultFromInput'];
        } else {
            // get value from input
            $inputValue = $this->getValueFilter($superObject);
            // sanitize value
            $inputValue = $this->getSanitizeValue($inputValue);
        }

        if (($result = $this->setValue($inputValue)) === false) {
            $msg = 'PARAM [' . $this->name . '] ERROR: Invalid value ' . Log::v2str($inputValue);
            Log::info($msg);
            return false;
        } else {
            $this->status = self::STATUS_UPD_FROM_INPUT;
        }

        return $result;
    }

    /**
     * Check if input value is valid
     *
     * @param mixed $value         input value
     * @param mixed $validateValue variable passed by reference. Updated to validated value in the case, the value is a valid value.
     *
     * @return bool true if is a valid value for this object
     */
    public function isValid($value, &$validateValue = null)
    {
        switch ($this->type) {
            case self::TYPE_STRING:
            case self::TYPE_BOOL:
            case self::TYPE_INT:
                return $this->isValidScalar($value, $validateValue);
            case self::TYPE_ARRAY_STRING:
            case self::TYPE_ARRAY_INT:
            case self::TYPE_ARRAY_MIXED:
                return $this->isValidArray($value, $validateValue);
            default:
                throw new \Exception('ITEM ERROR invalid type ' . $this->type);
        }
    }

    /**
     * Validate function for scalar value
     *
     * @param mixed $value         input value
     * @param mixed $validateValue variable passed by reference. Updated to validated value in the case, the value is a valid value.
     *
     * @return boolean false if value isn't a valid value
     */
    protected function isValidScalar($value, &$validateValue = null)
    {
        if (!is_null($value) && !is_scalar($value)) {
            return false;
        }

        $result = true;
        switch ($this->type) {
            case self::TYPE_STRING:
            case self::TYPE_ARRAY_STRING:
                $validateValue = (string) $value;
                if (strlen($validateValue) < $this->attr['min_len']) {
                    $this->setInvalidMessage('Must have ' . $this->attr['min_len'] . ' or more characters');
                    $result = false;
                }

                if ($this->attr['max_len'] > 0 && strlen($validateValue) > $this->attr['max_len']) {
                    $this->setInvalidMessage('Must have max ' . $this->attr['mimax_lenn_len'] . ' characters');
                    $result = false;
                }

                if (!empty($this->attr['validateRegex']) && preg_match($this->attr['validateRegex'], $validateValue) !== 1) {
                    $this->setInvalidMessage('String isn\'t valid');
                    $result = false;
                }
                break;
            case self::TYPE_INT:
            case self::TYPE_ARRAY_INT:
                $validateValue = filter_var($value, FILTER_VALIDATE_INT, array(
                    'options' => array(
                        'default'   => false, // value to return if the filter fails
                        'min_range' => $this->attr['min_range'],
                        'max_range' => $this->attr['max_range'],
                    )
                ));

                if ($validateValue === false) {
                    $this->setInvalidMessage('Isn\'t a valid number');
                    $result = false;
                }
                break;
            case self::TYPE_BOOL:
                $validateValue = is_bool($value) ? $value : filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
                if (($result = !is_null($validateValue)) === false) {
                    $this->setInvalidMessage('Isn\'t a valid value');
                }
                break;
            default:
                throw new \Exception('ITEM ERROR ' . $this->name . ' Invalid type ' . $this->type);
        }

        if ($result == true) {
            $acceptValues = $this->getAcceptValues();
            if (empty($acceptValues)) {
                $result = $this->callValidateCallback($validateValue);
            } else {
                if (in_array($validateValue, $acceptValues)) {
                    $result = true;
                } else {
                    $this->setInvalidMessage('Isn\'t a accepted value');
                    $result = false;
                }
            }
        }

        if ($result === false) {
            $validateValue = null;
        }

        return $result;
    }

    /**
     * Validate function for array value
     *
     * @param mixed $value         input value
     * @param mixed $validateValue variable passed by reference. Updated to validated value in the case, the value is a valid value.
     *
     * @return boolean false if value isn't a valid value
     */
    protected function isValidArray($value, &$validateValue = null)
    {
        $newValues     = (array) $value;
        $validateValue = array();
        $validValue    = null;

        if ($this->type == self::TYPE_ARRAY_MIXED) {
            $validateValue = $newValues;
            return $this->callValidateCallback($newValues);
        } else {
            foreach ($newValues as $key => $newValue) {
                if (!$this->isValidScalar($newValue, $validValue)) {
                    return false;
                }
                $validateValue[$key] = $validValue;
            }
        }
        return true;
    }

    /**
     * Call attribute validate callback
     *
     * @param mixed $value input value
     *
     * @return mixed
     */
    protected function callValidateCallback($value)
    {
        if (is_callable($this->attr['validateCallback'])) {
            return call_user_func($this->attr['validateCallback'], $value, $this);
        } elseif (!is_null($this->attr['validateCallback'])) {
            throw new \Exception('PARAM ' . $this->name . ' validateCallback isn\'t null and isn\'t callable');
        } else {
            return true;
        }
    }

    /**
     * this function is calle before sanitization.
     * Is use in extendend classs and transform value before the sanitization and validation process
     *
     * @param array $superObject query string super object
     *
     * @return mixed
     */
    protected function getValueFilter($superObject)
    {
        if (isset($superObject[$this->name])) {
            return $superObject[$this->name];
        } else {
            return null;
        }
    }

    /**
     * Return sanitized value
     *
     * @param mixed $value input value
     *
     * @return mixed
     */
    public function getSanitizeValue($value)
    {
        switch ($this->type) {
            case self::TYPE_STRING:
            case self::TYPE_BOOL:
            case self::TYPE_INT:
                return $this->getSanitizeValueScalar($value);
            case self::TYPE_ARRAY_STRING:
            case self::TYPE_ARRAY_INT:
                return $this->getSanitizeValueArray($value);
            case self::TYPE_ARRAY_MIXED:
                // global sanitize for mixed
                return $this->getSanitizeValueScalar($value);
            default:
                throw new \Exception('ITEM ERROR invalid type ' . $this->type);
        }
    }

    /**
     * If sanitizeCallback is apply sanitizeCallback at current value else return value.
     *
     * @param mixed $value input value
     *
     * @return mixed
     */
    protected function getSanitizeValueScalar($value)
    {
        if (is_callable($this->attr['sanitizeCallback'])) {
            return call_user_func($this->attr['sanitizeCallback'], $value);
        } elseif (!is_null($this->attr['sanitizeCallback'])) {
            throw new \Exception('PARAM ' . $this->name . ' sanitizeCallback isn\'t null and isn\'t callable');
        } else {
            return $value;
        }
    }

    /**
     * If sanitizeCallback is apply sanitizeCallback at each value of array.
     *
     * @param mixed $value input value
     *
     * @return array
     */
    protected function getSanitizeValueArray($value)
    {
        $newValues      = (array) $value;
        $sanitizeValues = array();

        foreach ($newValues as $key => $newValue) {
            $sanitizeValues[$key] = $this->getSanitizeValueScalar($newValue);
        }

        return $sanitizeValues;
    }

    /**
     * get accept values
     *
     * @return array
     */
    public function getAcceptValues()
    {
        if (is_callable($this->attr['acceptValues'])) {
            return call_user_func($this->attr['acceptValues'], $this);
        } else {
            return $this->attr['acceptValues'];
        }
    }

    /**
     * Set value from array. This function is used to set data from json array
     *
     * @param array $data param data
     *
     * @return boolean
     */
    public function fromArrayData($data)
    {
        $data = (array) $data;
        if (isset($data['status'])) {
            $this->status = $data['status'];
        }

        // only if value is different from current value
        if (isset($data['value']) && $data['value'] !== $this->value) {
            $sanitizedVal = $this->getSanitizeValue($data['value']);
            return $this->setValue($sanitizedVal);
        } else {
            return true;
        }
    }

    /**
     * return array dato to store in json array data
     * @return array
     */
    public function toArrayData()
    {
        return array(
            'value'  => $this->value,
            'status' => $this->status
        );
    }

    /**
     * Return a copy of this object with a new name ad overwrite attr
     *
     * @param string $newName new name
     * @param array  $attr    overwrite attributes
     *
     * @return self
     */
    public function getCopyWithNewName($newName, $attr = array())
    {
        $copy    = clone $this;
        $reflect = new \ReflectionObject($copy);

        $nameProp = $reflect->getProperty('name');
        $nameProp->setAccessible(true);
        $nameProp->setValue($copy, $newName);

        $attrProp = $reflect->getProperty('attr');
        $attrProp->setAccessible(true);
        $newAttr = array_merge($attrProp->getValue($copy), $attr);
        $attrProp->setValue($copy, $newAttr);

        $valueProp = $reflect->getProperty('value');
        $valueProp->setAccessible(true);
        $valueProp->setValue($copy, $newAttr['default']);

        return $copy;
    }

    /**
     * This function return the default attr for each type.
     * in the constructor an array merge is made between the result of this function and the parameters passed.
     * In this way the values ​​in $ this -> ['attr'] are always consistent.
     *
     * @param string $type param value type
     *
     * @return array
     */
    protected static function getDefaultAttrForType($type)
    {
        $attrs = array(
            'default'          => null, // the default value on init
            'defaultFromInput' => null, // if value isn't set in query form when setValueFromInput is called set this valus.
                                        // (normally defaultFromInput is equal to default)
            'acceptValues'     => array(), // if not empty accept only values in list | callback
            'sanitizeCallback' => null, // function (ParamItem $obj, $inputValue)
            'validateCallback' => null, // function (ParamItem $obj, $validateValue, $originalValue)
            'persistence'      => true, // if false don't store value in persistence file
            'invalidMessage'   => '' //this message is added at next step validation error message if not empty
        );

        switch ($type) {
            case self::TYPE_STRING:     // value type is a string
                $attrs['min_len']       = 0; // min string len. used in validation
                $attrs['max_len']       = 0; // max string len. used in validation
                $attrs['default']       = ''; // set default at empty string
                $attrs['validateRegex'] = null; // if isn;t null this regex is called to pass for validation.
                                                   //Can be combined with validateCallback. If both are active, the validation must pass both.
                break;
            case self::TYPE_ARRAY_STRING: // value type is array of string
                $attrs['min_len']       = 0; // min string len. used in validation
                $attrs['max_len']       = 0; // max string len. used in validation
                $attrs['default']       = array();  // set default at empty array
                $attrs['validateRegex'] = null; // if isn;t null this regex is called to pass for validation.
                                                   // Can be combined with validateCallback. If both are active, the validation must pass both.
                break;
            case self::TYPE_INT:    // value type is a int
                $attrs['min_range'] = PHP_INT_MAX * -1;
                $attrs['max_range'] = PHP_INT_MAX;
                $attrs['default']   = 0; // set default at 0
                break;
            case self::TYPE_ARRAY_INT:  // value type is an array of int
                $attrs['min_range'] = PHP_INT_MAX * -1;
                $attrs['max_range'] = PHP_INT_MAX;
                $attrs['default']   = array(); // set default at empty array
                break;
            case self::TYPE_BOOL:
                $attrs['default']          = false; // set default fals
                $attrs['defaultFromInput'] = false; // if value isn't set in input the default must be false for bool values
                break;
            case self::TYPE_ARRAY_MIXED:
                break;
            default:
            // accepts unknown values ​​because this class can be extended
        }
        return $attrs;
    }
}
