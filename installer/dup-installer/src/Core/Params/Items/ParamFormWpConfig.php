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
 * this class manages a password type input with the hide / show passwrd button
 */
class ParamFormWpConfig extends ParamForm
{
    const IN_WP_CONF_POSTFIX = '_inwpc';

    /**
     * Class constructor
     *
     * @param string $name     param identifier
     * @param string $type     TYPE_STRING | TYPE_ARRAY_STRING | ...
     * @param string $formType FORM_TYPE_HIDDEN | FORM_TYPE_TEXT | ...
     * @param array  $attr     list of attributes
     * @param array  $formAttr list of form attributes
     */
    public function __construct($name, $type, $formType, $attr = null, $formAttr = array())
    {
        parent::__construct($name, $type, $formType, $attr, $formAttr);
        $this->attr['defaultFromInput']               = $this->attr['default'];
        $this->attr['defaultFromInput']['inWpConfig'] = false;

        if ($type === self::TYPE_BOOL) {
            $this->attr['defaultFromInput']['value'] = false;
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
        $result = array(
            'value'      => parent::getValueFilter($superObject),
            'inWpConfig' => filter_var($superObject[$this->name . self::IN_WP_CONF_POSTFIX], FILTER_VALIDATE_BOOLEAN)
        );

        if (!parent::isValueInInput($superObject)) {
            $result['value'] = $this->attr['defaultFromInput']['value'];
        }

        return $result;
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
        $result          = (array) $value;
        $result['value'] = parent::getSanitizeValue($result['value']);
        return $result;
    }

    /**
     * Get value info from value
     *
     * @return string
     */
    protected function valueToInfo()
    {
        if ($this->value['inWpConfig']) {
            return 'Set in wp config with value ' . parent::valueToInfo();
        } else {
            return 'Not set in wp config';
        }
    }

    /**
     * Return input value
     *
     * @return mixed
     */
    protected function getInputValue()
    {
        return $this->value['value'];
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
        return parent::isValueInInput($superObject) || isset($superObject[$this->name . self::IN_WP_CONF_POSTFIX]);
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
        if (!is_array($value) || !isset($value['value']) || !isset($value['inWpConfig'])) {
            Log::info('WP CONFIG INVALID ARRAY VAL:' . Log::v2str($value));
            return false;
        }

        // IF isn't in wp config the value isn't validate
        if ($value['inWpConfig'] === false) {
            $validateValue = $value;
            return true;
        } else {
            $confValidValue = $value['value'];
            if (parent::isValid($value['value'], $confValidValue) === false) {
                Log::info('WP CONFIG INVALID VALUE:' . Log::v2str($confValidValue));
                return false;
            } else {
                $validateValue          = $value;
                $validateValue['value'] = $confValidValue;
                return true;
            }
        }
    }

    /**
     * Render HTML input before content
     *
     * @return void
     */
    protected function htmlInputContBefore()
    {
        if ($this->getFormStatus() == self::STATUS_INFO_ONLY) {
            return;
        }

        if (!$this->value['inWpConfig']) {
            $this->formAttr['inputContainerClasses'][] = 'no-display';
            if ($this->formAttr['status'] == self::STATUS_ENABLED) {
                $this->formAttr['status'] = self::STATUS_DISABLED;
            }
        }

        $inputAttrs = array(
            'name'  => $this->name . self::IN_WP_CONF_POSTFIX,
            'value' => 1
        );
        if ($this->value['inWpConfig']) {
            $inputAttrs['checked'] = 'checked';
        }
        echo '<span class="wpinconf-check-wrapper" >';
        \DUPX_U_Html::checkboxSwitch(
            $inputAttrs,
            array(
                'title' => 'Add in wp config'
            )
        );
        echo '</span>';
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
        $attrs        = parent::getDefaultAttrForType($type);
        $valFromInput = $attrs['defaultFromInput'];

        $attrs['defaultFromInput'] = array(
            'value'      => $valFromInput,
            'inWpConfig' => false
        );
        return $attrs;
    }

    /**
     * this function return the default formAttr for each type.
     * in the constructor an array merge is made between the result of this function and the parameters passed.
     * In this way the values ​​in $ this -> ['attr'] are always consistent.
     *
     * @param string $formType form type
     *
     * @return array
     */
    protected static function getDefaultAttrForFormType($formType)
    {
        $attrs = parent::getDefaultAttrForFormType($formType);

        $attrs['wrapperClasses'][]    = 'wp-config-item';
        $attrs['wrapperContainerTag'] = 'div';
        return $attrs;
    }
}
