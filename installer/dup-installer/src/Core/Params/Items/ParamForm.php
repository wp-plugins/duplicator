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

use Duplicator\Libs\Upsell;
use DUPX_U;
use Exception;

/**
 * This class extends ParamItem describing how the parameter should be handled within the form.
 *
 */
class ParamForm extends ParamItem
{
    const FORM_TYPE_HIDDEN     = 'hidden';
    const FORM_TYPE_TEXT       = 'text';
    const FORM_TYPE_NUMBER     = 'number';
    const FORM_TYPE_SELECT     = 'sel';
    const FORM_TYPE_CHECKBOX   = 'check';
    const FORM_TYPE_SWITCH     = 'switch';
    const FORM_TYPE_M_CHECKBOX = 'mcheck';
    const FORM_TYPE_RADIO      = 'radio';
    const FORM_TYPE_BGROUP     = 'bgroup';
    const STATUS_ENABLED       = 'st_enabled';
    const STATUS_READONLY      = 'st_readonly';
    const STATUS_DISABLED      = 'st_disabled';
    const STATUS_INFO_ONLY     = 'st_infoonly';
    const STATUS_SKIP          = 'st_skip';

    protected $formType = null;
    protected $formAttr = array();

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
        if ($type == self::TYPE_ARRAY_MIXED) {
            throw new \Exception('array myxed can\'t be a ParamForm');
        }

        parent::__construct($name, $type, $attr);

        $defaultAttr    = static::getDefaultAttrForFormType($formType);
        $this->formAttr = array_merge($defaultAttr, (array) $formAttr);

        if (isset($formAttr['classes'])) {
            $this->formAttr['classes'] = array_merge($defaultAttr['classes'], (array) $formAttr['classes']);
        }

        if (isset($formAttr['labelClasses'])) {
            $this->formAttr['labelClasses'] = array_merge($defaultAttr['labelClasses'], (array) $formAttr['labelClasses']);
        }

        if (isset($formAttr['wrapperClasses'])) {
            $this->formAttr['wrapperClasses'] = array_merge($defaultAttr['wrapperClasses'], (array) $formAttr['wrapperClasses']);
        }

        if (isset($formAttr['inputContainerClasses'])) {
            $this->formAttr['inputContainerClasses'] = array_merge($defaultAttr['inputContainerClasses'], (array) $formAttr['inputContainerClasses']);
        }

        if (strlen($this->formAttr['label']) == 0) {
            throw new Exception('Param ' . $name . ' must have label (user renderLabel to hide it)');
        }

        if ($this->formAttr['renderLabel']) {
            $this->formAttr['wrapperClasses'][] = 'has-main-label';
        }

        $this->formType = $formType;

        if (empty($this->formAttr['id'])) {
            $this->formAttr['id'] = 'param_item_' . $name;
        }

        if (empty($this->formAttr['wrapperId'])) {
            $this->formAttr['wrapperId'] = 'wrapper_item_' . $name;
        }

        //Log::infoObject('PARAM INIZIALIZED ['.$this->name.']', $this, Log::LV_DEFAULT);
    }

    /**
     * Return param label
     *
     * @return string
     */
    public function getLabel()
    {
        if (!empty($this->formAttr['label'])) {
            return $this->formAttr['label'];
        } else {
            return parent::getLabel();
        }
    }

    /**
     * get the input id (input, select ... )
     * normally it depends on the name of the object but can be perosnalizzato through formAttrs
     *
     * @return string
     */
    public function getFormItemId()
    {
        return $this->formAttr['id'];
    }

    /**
     * return the input wrapper id if isn't empty or false
     * normally it depends on the name of the object but can be perosnalizzato through formAttrs
     *
     * @return string
     */
    /**
     * return the input wrapper id if isn't empty or false
     * normally it depends on the name of the object but can be perosnalizzato through formAttrs
     *
     * @return string
     */
    public function getFormWrapperId()
    {
        return empty($this->formAttr['wrapperId']) ? false : $this->formAttr['wrapperId'];
    }

    /**
     * return current form status
     *
     * @return string // STATUS_ENABLED | STATUS_READONLY ...
     */
    public function getFormStatus()
    {
        if (is_callable($this->formAttr['status'])) {
            return call_user_func($this->formAttr['status'], $this);
        } else {
            return $this->formAttr['status'];
        }
    }

    /**
     * Add html class at param wrapper container
     *
     * @param string $class class string
     *
     * @return void
     */
    public function addWrapperClass($class)
    {
        if (!in_array($class, $this->formAttr['wrapperClasses'])) {
            $this->formAttr['wrapperClasses'][] = $class;
        }
    }

    /**
     * Remove html class at param wrapper container
     *
     * @param string $class class string
     *
     * @return void
     */
    public function removeWrapperClass($class)
    {
        if (in_array($class, $this->formAttr['wrapperClasses'])) {
            unset($this->formAttr['wrapperClasses'][$class]);
        }
    }

    /**
     * This function can be extended in child classes
     *
     * @return string
     */
    protected function getAttrName()
    {
        return $this->name;
    }

    /**
     * this function renturn a hidden name for support checkbox input
     *
     * @return string
     */
    protected function getAttrHiddenName()
    {
        return $this->getAttrName() . '_is_input';
    }

    /**
     * this function can be extended in child classes
     *
     * @return mixed
     */
    protected function getInputValue()
    {
        return $this->value;
    }

    /**
     * Return a copy of this object with a new name ad overwrite attr
     *
     * @param string $newName  new param name
     * @param array  $attr     param attributes
     * @param array  $formAttr form attributes
     *
     * @return self
     */
    public function getCopyWithNewName($newName, $attr = array(), $formAttr = array())
    {
        $copy = parent::getCopyWithNewName($newName, $attr);

        $reflect = new \ReflectionObject($copy);

        $formAttrProp = $reflect->getProperty('formAttr');
        $formAttrProp->setAccessible(true);

        $newAttr              = $formAttrProp->getValue($copy);
        $newAttr['id']        = 'param_item_' . $newName;
        $newAttr['wrapperId'] = 'wrapper_item_' . $newName;
        $newAttr              = array_merge($newAttr, $formAttr);

        if (isset($newAttr['options']) && is_array($newAttr['options'])) {
            $options            = $newAttr['options'];
            $newAttr['options'] = array();
            foreach ($options as $key => $option) {
                if (is_object($option)) {
                    $newAttr['options'][$key] = clone $option;
                } else {
                    $newAttr['options'][$key] = $option;
                }
            }
        }

        $formAttrProp->setValue($copy, $newAttr);

        return $copy;
    }

    /**
     *
     * @return bool
     */
    public function isEnabled()
    {
        return $this->getFormStatus() == self::STATUS_ENABLED;
    }

    /**
     *
     * @return bool
     */
    public function isSkip()
    {
        return $this->getFormStatus() == self::STATUS_SKIP;
    }

    /**
     *
     * @return bool
     */
    public function isDisabled()
    {
        return $this->getFormStatus() == self::STATUS_DISABLED;
    }

    /**
     *
     * @return bool
     */
    public function isReadonly()
    {
        return $this->getFormStatus() == self::STATUS_READONLY;
    }

    /**
     * Return true if the passed value is in current value if type is array or equal if is scalar
     *
     * @param mixed $value      value to check
     * @param mixed $inputValue current selected value/s
     *
     * @return bool
     */
    protected static function isValueInValue($value, $inputValue)
    {
        if (is_null($inputValue) || is_scalar($inputValue)) {
            return $value == $inputValue;
        } else {
            return in_array($value, $inputValue);
        }
    }

    /**
     * Display the html input of current item
     *
     * @return void
     */
    protected function htmlItem()
    {
        switch ($this->formType) {
            case self::FORM_TYPE_HIDDEN:
                $this->hiddenHtml();
                break;
            case self::FORM_TYPE_TEXT:
                $this->inputHtml('text');
                break;
            case self::FORM_TYPE_NUMBER:
                $this->inputHtml('number');
                break;
            case self::FORM_TYPE_SELECT:
                $this->selectHtml();
                break;
            case self::FORM_TYPE_CHECKBOX:
                $this->checkBoxHtml(false);
                break;
            case self::FORM_TYPE_SWITCH:
                $this->checkBoxHtml(true);
                break;
            case self::FORM_TYPE_M_CHECKBOX:
                $this->mCheckBoxHtml();
                break;
            case self::FORM_TYPE_RADIO:
                $this->radioHtml();
                break;
            case self::FORM_TYPE_BGROUP:
                $this->bgroupHtml();
                break;
            default:
                throw new \Exception('ITEM ERROR ' . $this->name . ' Invalid form type ' . $this->formType);
        }
    }

    /**
     * Return form attribute
     *
     * @param string $key form attribute key
     *
     * @return mixed
     */
    public function getFormAttr($key)
    {
        return $this->formAttr[$key];
    }

    /**
     * Set form attribute
     *
     * @param string $key   form attribute key
     * @param mixed  $value value
     *
     * @return void
     */
    public function setFormAttr($key, $value)
    {
        $this->formAttr[$key] = $value;
    }

    /**
     * Get param options
     *
     * @return ParamOption[]
     */
    protected function getOptions()
    {
        if (!isset($this->formAttr['options'])) {
            return array();
        } elseif (is_callable($this->formAttr['options'])) {
            return call_user_func($this->formAttr['options'], $this);
        } else {
            return $this->formAttr['options'];
        }
    }

    /**
     * Update option status
     *
     * @param int    $index  option index
     * @param string $status option status
     *
     * @return void
     */
    public function setOptionStatus($index, $status)
    {
        if (is_array($this->formAttr['options']) && isset($this->formAttr['options'][$index])) {
            $this->formAttr['options'][$index]->setStatus($status);
        }
    }

    /**
     * Html of current item if the status if info only
     *
     * @return void
     */
    protected function infoOnlyHtml()
    {
        $attrs          = array(
            'id' => $this->formAttr['id']
        );
        $classes        = array_merge(array('input-info-only'), $this->formAttr['classes']);
        $attrs['class'] = implode(' ', $classes);
        ?>
        <span <?php echo \DUPX_U_Html::arrayAttrToHtml($attrs); ?> >
            <?php echo \DUPX_U::esc_html($this->valueToInfo()); ?>
        </span>
        <?php
    }

    /**
     * return the text of current object fot info only status
     *
     * @return string
     */
    protected function valueToInfo()
    {
        switch ($this->formType) {
            case self::FORM_TYPE_SELECT:
            case self::FORM_TYPE_M_CHECKBOX:
                $optionsLabels = array();
                foreach ($this->getOptions() as $option) {
                    if (self::isValueInValue($option->value, $this->getInputValue())) {
                        $optionsLabels[] = $option->label;
                    }
                }
                return implode(', ', $optionsLabels);
            case self::FORM_TYPE_CHECKBOX:
                $result = '';
                if (self::isValueInValue($this->formAttr['checkedValue'], $this->getInputValue())) {
                    $result = '[enabled]';
                } else {
                    $result = '[disabled]';
                }
                return $result . ' ' . $this->formAttr['checkboxLabel'];
            case self::FORM_TYPE_RADIO:
            case self::FORM_TYPE_BGROUP:
                $optionsLabels = array();
                foreach ($this->getOptions() as $option) {
                    if (self::isValueInValue($option->value, $this->getInputValue())) {
                        return $option->label;
                    }
                }
                return '[disabled]';
            case self::FORM_TYPE_HIDDEN:
            case self::FORM_TYPE_TEXT:
            case self::FORM_TYPE_NUMBER:
            default:
                if (is_null($this->getInputValue()) || is_scalar($this->getInputValue())) {
                    return \DUPX_U::esc_html($this->getInputValue());
                } else {
                    return \DUPX_U::esc_html(implode(',', $this->getInputValue()));
                }
        }
    }

    /**
     * Get html form option of current item
     *
     * @param bool $echo if true echo html
     *
     * @return string
     */
    public function getHtml($echo = true)
    {
        if ($this->isSkip() === true) {
            return '';
        }
        ob_start();

        try {
            $this->htmlItemBefore();
            if ($this->getFormStatus() == self::STATUS_INFO_ONLY) {
                $this->infoOnlyHtml();
            } else {
                $this->htmlItem();
            }
            $this->htmlItemAfter();
        } catch (\Exception $e) {
            ob_end_flush();
            throw $e;
        }


        if ($echo) {
            ob_end_flush();
            return '';
        } else {
            return ob_get_clean();
        }
    }

    /**
     * Input item before (wrapper input and label)
     *
     * @return void
     */
    protected function htmlItemBefore()
    {
        if (!empty($this->formAttr['wrapperTag'])) {
            $wrapperAttrs = array();
            if (!empty($this->formAttr['wrapperId'])) {
                $wrapperAttrs['id'] = $this->formAttr['wrapperId'];
            }

            $tmpWrapperClasses = $this->formAttr['wrapperClasses'];
            if ($this->isDisabled()) {
                $tmpWrapperClasses[] = 'param-wrapper-disabled';
            }

            if ($this->isReadonly()) {
                $tmpWrapperClasses[] = 'param-wrapper-readonly';
            }

            if ($this->isEnabled()) {
                $tmpWrapperClasses[] = 'param-wrapper-enabled';
            }

            if (!empty($tmpWrapperClasses)) {
                $wrapperAttrs['class'] = implode(' ', $tmpWrapperClasses);
            }

            foreach ($this->formAttr['wrapperAttr'] as $attrName => $attrVal) {
                $wrapperAttrs[$attrName] = $attrVal;
            }

            echo '<' . $this->formAttr['wrapperTag'] . ' ' . \DUPX_U_Html::arrayAttrToHtml($wrapperAttrs) . ' >';

            if (!empty($this->formAttr['wrapperContainerTag'])) {
                echo '<' . $this->formAttr['wrapperContainerTag'] . ' class="container" >';
            }
        }

        $this->getLabelHtml();
        $this->htmlInputContBefore();
        if (!empty($this->formAttr['inputContainerTag'])) {
            echo '<span class="' . implode(' ', $this->formAttr['inputContainerClasses']) . '">';
        }
    }

    /**
     * function called  between label and input container
     * used on extended classes
     *
     * @return void
     */
    protected function htmlInputContBefore()
    {
    }

    /**
     * function calle after input container
     * used on extended classes
     *
     * @return void
     */
    protected function htmlInputContAfter()
    {
    }

    /**
     * input item after (close wrapper)
     *
     * @return void
     */
    protected function htmlItemAfter()
    {
        if (!empty($this->formAttr['inputContainerTag'])) {
            echo '</span>';
        }

        $this->htmlInputContAfter();
        if (!empty($this->formAttr['wrapperTag'])) {
            if (!empty($this->formAttr['wrapperContainerTag'])) {
                echo '</' . $this->formAttr['wrapperContainerTag'] . '>';
            }
            echo $this->getSubNote();
            echo '</' . $this->formAttr['wrapperTag'] . '>';
        } else {
            echo $this->getSubNote();
        }
    }

    /**
     *
     * @return string
     */
    protected function getSubNote()
    {

        if (is_callable($this->formAttr['subNote'])) {
            $subNote = call_user_func($this->formAttr['subNote'], $this);
        } else {
            $subNote = $this->formAttr['subNote'];
        }

        return empty($subNote) ? '' : '<div class="sub-note" >' . $subNote . '</div>';
    }

    /**
     * Return postfix element data
     *
     * @param array $data postix element data
     *
     * @return array
     */
    protected function prefixPostfixElem($data)
    {
        $default = array(
            'type'      => 'none',
            'label'     => null,
            'id'        => null,
            'btnAction' => null,
            'attrs'     => array()
        );

        if (is_callable($data)) {
            $element = call_user_func($data, $this);
        } else {
            $element = $data;
        }

        if (is_array($element)) {
            return array_merge($default, $element);
        } else {
            return $default;
        }
    }

    /**
     * Return prefix element data
     *
     * @return array
     */
    protected function getPrefix()
    {
        return $this->prefixPostfixElem($this->formAttr['prefix']);
    }

    /**
     * Return postifx element data
     *
     * @return array
     */
    protected function getPostfix()
    {
        return $this->prefixPostfixElem($this->formAttr['postfix']);
    }

    /**
     * html if type is hidden
     *
     * @return void
     */
    protected function hiddenHtml()
    {
        $attrs = array(
            'id'    => $this->formAttr['id'],
            'name'  => $this->getAttrName(),
            'value' => $this->getInputValue()
        );

        if ($this->isDisabled()) {
            $attrs['disabled'] = 'disabled';
        }

        if (!empty($this->formAttr['classes'])) {
            $attrs['class'] = implode(' ', $this->formAttr['classes']);
        }

        $attrs = array_merge($attrs, $this->formAttr['attr']);
        ?>
        <input type="hidden" <?php echo \DUPX_U_Html::arrayAttrToHtml($attrs); ?> >
        <?php
    }

    /**
     * HTML if type is input (text/number)
     *
     * @param string $type input type
     *
     * @return void
     */
    protected function inputHtml($type)
    {
        $attrs = array(
            'type'  => $type,
            'id'    => $this->formAttr['id'],
            'name'  => $this->getAttrName(),
            'value' => $this->getInputValue()
        );

        if ($this->isDisabled()) {
            $attrs['disabled'] = 'disabled';
        }

        if ($this->isReadonly()) {
            $attrs['readonly'] = 'readonly';
        }

        if (!is_null($this->formAttr['maxLength'])) {
            $attrs['maxLength'] = $this->formAttr['maxLength'];
        }

        if (!is_null($this->formAttr['size'])) {
            $attrs['size'] = $this->formAttr['size'];
        }

        if (isset($this->formAttr['min']) && !is_null($this->formAttr['min'])) {
            $attrs['min'] = $this->formAttr['min'];
        }

        if (isset($this->formAttr['max']) && !is_null($this->formAttr['max'])) {
            $attrs['max'] = $this->formAttr['max'];
        }

        if (isset($this->formAttr['step']) && !is_null($this->formAttr['step'])) {
            $attrs['step'] = $this->formAttr['step'];
        }

        if (!empty($this->formAttr['classes'])) {
            $attrs['class'] = implode(' ', $this->formAttr['classes']);
        }

        $prefixHtml   = self::getPrefixPostfixHtml($this->getPrefix(), 'prefix');
        $postfixHtml  = self::getPrefixPostfixHtml($this->getPostfix(), 'postfix');
        $isPrePostFix = (strlen($prefixHtml) > 0 || strlen($postfixHtml) > 0);

        $attrs = array_merge($attrs, $this->formAttr['attr']);
        if ($isPrePostFix) {
            ?>
            <span class="input-postfix-btn-group">
                <?php
        }
        echo $prefixHtml;
        ?>
        <input <?php echo \DUPX_U_Html::arrayAttrToHtml($attrs); ?> >
        <?php
        echo $postfixHtml;
        if ($isPrePostFix) {
            ?>
        </span>
            <?php
        }
    }

    /**
     * Get pre-post fix element html
     *
     * @param array  $element element data
     * @param string $class   element class
     *
     * @return string
     */
    protected static function getPrefixPostfixHtml($element, $class)
    {
        if ($element['type'] == 'none') {
            return '';
        }

        $attrs = array('class' => $class);
        switch ($element['type']) {
            case 'button':
                $tag           = 'button';
                $attrs['type'] = 'button';
                if (!empty($element['btnAction'])) {
                    $attrs['onclick'] = $element['btnAction'];
                }
                break;
            case 'label':
            default:
                $tag = 'span';
                break;
        }
        if (!empty($element['id'])) {
            $attrs['id'] = $element['id'];
        }
        $attrs = array_merge($attrs, $element['attrs']);
        return '<' . $tag . ' ' . \DUPX_U_Html::arrayAttrToHtml($attrs) . '>' . $element['label'] . '</' . $tag . '>';
    }

    /**
     * HTML if type is select
     *
     * @return void
     */
    protected function selectHtml()
    {
        $attrs = array(
            'id'   => $this->formAttr['id'],
            'name' => $this->getAttrName() . ($this->formAttr['multiple'] ? '[]' : ''),
        );

        if (!empty($this->formAttr['classes'])) {
            $attrs['class'] = implode(' ', $this->formAttr['classes']);
        }

        if ($this->isDisabled()) {
            $attrs['disabled'] = 'disabled';
        }

        if ($this->isReadonly()) {
            $attrs['readonly'] = 'readonly';
        }

        if ($this->formAttr['multiple']) {
            $attrs['multiple'] = '';
        }

        $attrs['size'] = $this->formAttr['size'] == 0 ? count($this->getOptions()) : $this->formAttr['size'];

        $attrs = array_merge($attrs, $this->formAttr['attr']);
        ?>
        <select <?php echo \DUPX_U_Html::arrayAttrToHtml($attrs); ?> >
            <?php self::renderSelectOptions($this->getOptions(), $this->getInputValue()); ?>
        </select>
        <?php
    }

    /**
     * Render select options
     *
     * @param ParamOption[] $options    options list
     * @param mixed         $inputValue selected values
     *
     * @return void
     */
    protected static function renderSelectOptions($options, $inputValue)
    {
        $lastOptGroup    = '';
        $autoSelectFirst = (count($options) == 1);

        foreach ($options as $option) {
            if ($option->isHidden()) {
                continue;
            }

            if ($lastOptGroup !== $option->getOptGroup()) {
                if (strlen($lastOptGroup) > 0) {
                    ?></optgroup><?php
                }
                if (strlen($option->getOptGroup()) > 0) {
                    ?><optgroup label="<?php echo DUPX_U::esc_attr($option->getOptGroup()); ?>"><?php
                }
                $lastOptGroup = $option->getOptGroup();
            }

            $optAttr = array(
                'value' => $option->value
            );

            if ($option->isDisabled()) {
                $optAttr['disabled'] = 'disabled';
            } elseif (
                self::isValueInValue($option->value, $inputValue) ||
                $autoSelectFirst
            ) {
                // can't be selected if is disabled
                $optAttr['selected'] = 'selected';
            }

            $optAttr = array_merge($optAttr, (array) $option->attrs);
            ?>
            <option <?php echo \DUPX_U_Html::arrayAttrToHtml($optAttr); ?> >
                <?php echo $option->label; ?>
            </option>
            <?php
        }
        if (strlen($lastOptGroup) > 0) {
            ?></optgroup><?php
        }
    }

    /**
     * Render checkbox element
     *
     * @param bool $switch if true render switch else input checkbox
     *
     * @return void
     */
    protected function checkBoxHtml($switch)
    {
        $attrs = array(
            'id'    => $this->formAttr['id'],
            'name'  => $this->getAttrName(),
            'value' => $this->formAttr['checkedValue']
        );

        if (!empty($this->formAttr['classes'])) {
            $attrs['class'] = implode(' ', $this->formAttr['classes']);
        }

        if ($this->isDisabled()) {
            $attrs['disabled'] = 'disabled';
        }

        if ($this->isReadonly()) {
            $attrs['readonly'] = 'readonly';
        }

        if (self::isValueInValue($this->formAttr['checkedValue'], $this->getInputValue())) {
            $attrs['checked'] = 'checked';
        }

        $attrs = array_merge($attrs, $this->formAttr['attr']);

        $hiddenAttrs = array(
            'name'  => $this->getAttrHiddenName(),
            'value' => true
        );

        if ($switch) {
            \DUPX_U_Html::checkboxSwitch($attrs);
        } else {
            ?>
            <input type="checkbox" <?php echo \DUPX_U_Html::arrayAttrToHtml($attrs); ?> >
        <?php } ?>
        <span class="label-checkbox" ><?php echo $this->formAttr['checkboxLabel']; ?></span>
        <input type="hidden" <?php echo \DUPX_U_Html::arrayAttrToHtml($hiddenAttrs); ?> >
        <?php
    }

    /**
     *  html if type is multiple checkboxes
     *
     * @return void
     */
    protected function mCheckBoxHtml()
    {
        /*
         * for radio don't use global attr but option attr
         * $attrs = array_merge($attrs, $this->formAttr['attr']);
         */
        foreach ($this->getOptions() as $index => $option) {
            $attrs = array(
                'id'    => $this->formAttr['id'] . '_' . $index,
                'name'  => $this->getAttrName() . '[]',
                'value' => $option->value
            );

            if (!empty($this->formAttr['classes'])) {
                $attrs['class'] = implode(' ', $this->formAttr['classes']);
            }

            if (self::isValueInValue($option->value, $this->getInputValue())) {
                $attrs['checked'] = 'checked';
            }

            if ($this->isReadonly()) {
                $attrs['readonly'] = 'readonly';
            }

            if ($this->isDisabled() || $option->isDisabled()) {
                $attrs['disabled'] = 'disabled';
            }

            $attrs = array_merge($attrs, $option->attrs);
            if (!empty($attrs['title'])) {
                $labelTtile = ' title="' . \DUPX_U::esc_attr($attrs['title']) . '"';
                unset($attrs['title']);
            } else {
                $labelTtile = '';
            }
            ?>
            <label class="option-group" <?php echo $labelTtile; ?>>
                <input type="checkbox" <?php echo \DUPX_U_Html::arrayAttrToHtml($attrs); ?> > 
                <span class="label-checkbox" >
                    <?php echo $option->label; ?>
                </span>
            </label>
            <?php
        }
    }

    /**
     *  html if type is radio
     *
     * @return void
     */
    protected function radioHtml()
    {
        /*
         * for radio don't use global attr but option attr
         * $attrs = array_merge($attrs, $this->formAttr['attr']);
         */
        foreach ($this->getOptions() as $index => $option) {
            if ($option->isHidden()) {
                continue;
            }

            $attrs = array(
                'id'    => $this->formAttr['id'] . '_' . $index,
                'name'  => $this->getAttrName(),
                'value' => $option->value
            );

            $optionGroupClasses = 'option-group';

            if (!empty($this->formAttr['classes'])) {
                $attrs['class'] = implode(' ', $this->formAttr['classes']);
            }

            if (self::isValueInValue($option->value, $this->getInputValue())) {
                $attrs['checked'] = 'checked';
            }

            if ($this->isReadonly()) {
                $attrs['readonly'] = 'readonly';
            }

            if ($this->isDisabled() || $option->isDisabled()) {
                $attrs['disabled'] = 'disabled';
            }

            if ($option->isDisabled()) {
                $optionGroupClasses .= ' option-disabled';
            }

            $attrs = array_merge($attrs, $option->attrs);
            if (!empty($attrs['title'])) {
                $labelTtile = ' title="' . \DUPX_U::esc_attr($attrs['title']) . '"';
                unset($attrs['title']);
            } else {
                $labelTtile = '';
            }
            ?>
            <label class="<?php echo $optionGroupClasses; ?>" <?php echo $labelTtile; ?>>
                <input type="radio" <?php echo \DUPX_U_Html::arrayAttrToHtml($attrs); ?> > 
                <span class="label-checkbox" >
                    <?php echo $option->label; ?>
                </span>
                <?php echo $option->getNote(); ?>
            </label>
            <?php
        }
    }

    /**
     *  html if type is button
     *
     * @return void
     */
    protected function bgroupHtml()
    {
        /*
         * for radio don't use global attr but option attr
         * $attrs = array_merge($attrs, $this->formAttr['attr']);
         */
        $this->hiddenHtml();
        foreach ($this->getOptions() as $index => $option) {
            if ($option->isHidden()) {
                continue;
            }

            $attrs = array(
                'id'    => $this->formAttr['id'] . '_' . $index,
                'value' => $option->value,
                'class' => $this->formAttr['id'] . '_button ' . implode(' ', $this->formAttr['classes'])
            );

            if (self::isValueInValue($option->value, $this->getInputValue())) {
                $attrs['class'] .= ' active';
            }

            if ($this->isReadonly()) {
                $attrs['readonly'] = 'readonly';
            }

            if ($this->isDisabled() || $option->isDisabled()) {
                $attrs['disabled'] = 'disabled';
            }

            $attrs = array_merge($attrs, $option->attrs);
            if (!empty($attrs['title'])) {
                $labelTtile = ' title="' . \DUPX_U::esc_attr($attrs['title']) . '"';
                unset($attrs['title']);
            } else {
                $labelTtile = '';
            }
            ?>
            <button type="button"  <?php echo \DUPX_U_Html::arrayAttrToHtml($attrs); ?>>
                <?php echo $option->label; ?>
            </button>
            <?php
        }
    }

    /**
     * get current label html
     *
     * @param bool $echo if true echo HTML
     * @return string
     */
    protected function getLabelHtml($echo = true)
    {
        if ($this->formAttr['renderLabel'] == false) {
            return '';
        }

        $attrs = array();
        if (!empty($this->formAttr['labelClasses'])) {
            $attrs['class'] = implode(' ', $this->formAttr['labelClasses']);
        }
        ob_start();
        ?>
        <span <?php echo \DUPX_U_Html::arrayAttrToHtml($attrs); ?> >
            <?php
            echo (strlen($this->formAttr['label']) == 0 ? '&nbsp;' : \DUPX_U::esc_html($this->formAttr['label']));
            if (strlen($this->formAttr['inlineHelp']) > 0) {
                $helpTitle = (strlen($this->formAttr['inlineHelpTitle']) ? $this->formAttr['inlineHelpTitle'] : rtrim($this->formAttr['label'], ':'));
                ?>
                <i 
                    class="fas fa-question-circle fa-sm param-inline-help-icon" 
                    data-tooltip-title="<?php echo DUPX_U::esc_attr($helpTitle);?>" 
                    data-tooltip="<?php echo DUPX_U::esc_attr($this->formAttr['inlineHelp']); ?>" 
                ></i>
            <?php }
            if (strlen($this->formAttr['proFlag']) > 0) {
                $flagTitle = (strlen($this->formAttr['proFlagTitle']) ? $this->formAttr['proFlagTitle'] : rtrim($this->formAttr['label'], ':'));
                ?><sup
                    class="pro-flag" 
                    data-tooltip-title="<?php echo DUPX_U::esc_attr($flagTitle);?>" 
                    data-tooltip="<?php echo DUPX_U::esc_attr($this->formAttr['proFlag'] .
                        Upsell::getCampaignTooltipHTML(array('utm_medium' => 'installer', 'utm_content' => 'option_' . $this->name))); ?>"
                >*</sup>
            <?php } ?>
        </span>
        <?php
        if ($echo) {
            ob_end_flush();
            return '';
        } else {
            return ob_get_clean();
        }
    }

    /**
     * Set value from array. This function is used to set data from json array
     *
     * @param array $data form data
     *
     * @return boolean
     */
    public function fromArrayData($data)
    {
        $result = parent::fromArrayData($data);
        if (isset($data['formStatus'])) {
            $this->formAttr['status'] = $data['formStatus'];
        }
        return $result;
    }

    /**
     * return array dato to store in json array data
     * @return array
     */
    public function toArrayData()
    {
        $result = parent::toArrayData();
        if (!is_callable($this->formAttr['status'])) {
            $result['formStatus'] = $this->getFormStatus();
        }
        return $result;
    }

    /**
     * update the value from input if exists ot set the default
     * sanitation and validation are performed
     * skip set value if current status is disabled, info only or skip
     *
     * @param string $method query string method (POST, GET)
     *
     * @return boolean false if value isn't validated
     */
    public function setValueFromInput($method = self::INPUT_POST)
    {
        // if input is disabled don't reads from input.
        if (
            $this->getFormStatus() == self::STATUS_INFO_ONLY ||
            $this->getFormStatus() == self::STATUS_SKIP
        ) {
            return true;
        }

        // prevent overwrite by default if item is disable and isn't enable in client by js
        $superObject = self::getSuperObjectByMethod($method);
        if ($this->getFormStatus() == self::STATUS_DISABLED && !$this->isValueInInput($superObject)) {
            return true;
        }

        // prevent overwrite by default if checkbox isn't in form
        if (($this->formType === self::FORM_TYPE_CHECKBOX || $this->formType === self::FORM_TYPE_SWITCH) && !isset($superObject[$this->getAttrName()])) {
            if (!isset($superObject[$this->getAttrHiddenName()]) || !$superObject[$this->getAttrHiddenName()]) {
                return true;
            }
        } elseif (!isset($superObject[$this->name])) {
            return true;
        }

        return parent::setValueFromInput($method);
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
        $attrs = array(
            'label'                 => null, // input main label,
            'renderLabel'           => true, // if false don\'t render label
            'labelClasses'          => array('label', 'main-label'), // label classes (the label html is <span class="classes" >label</span>
            'id'                    => null, // input id , if null the default is 'param_item_'.$name
            'classes'               => array('input-item'), // input classes
            'status'                => self::STATUS_ENABLED, // form status
            'title'                 => null, // input title
            'attr'                  => array(), // custom input attributes key="VALUE"
            'inlineHelpTitle'       => '',
            'inlineHelp'            => '',
            'proFlagTitle'          => '',
            'proFlag'               => '',
            'subNote'               => null, // sub note container (html string),
            'wrapperTag'            => 'div', // if null the input haven't the wrapper tag.
                                              // input tag wrapper, wrapper html is
                                              // ~ <TAG class="classes" ><CONTAINER><LABEL><INPUT CONTAINER></CONTAINER></TAG>
            'wrapperId'             => null, // input wrapper id, if null the default is 'wrapper_item_'.$name
            'wrapperClasses'        => array(// wrapper classes, param-wrapper generic class plus 'param-form-type-'.$formType type class
                'param-wrapper',
                'param-form-type-' . $formType),
            'wrapperAttr'           => array(), // custom wrapper attributes key="VALUE"
            'wrapperContainerTag'   => 'label',
            'inputContainerTag'     => 'span',
            'inputContainerClasses' => array('input-container'),
        );

        switch ($formType) {
            case self::FORM_TYPE_HIDDEN:
                $attrs['wrapperTag']          = null; // disable wrapper for hidden inputs
                $attrs['wrapperContainerTag'] = null;
                $attrs['inputContainerTag']   = null;
                break;
            case self::FORM_TYPE_NUMBER:
                $attrs['min']  = null; // attr min
                $attrs['max']  = null; // attr max
                $attrs['step'] = null; // attr step
            // continue form type text
            case self::FORM_TYPE_TEXT:
                $attrs['maxLength'] = null;     // if null have no limit
                $attrs['size']      = null;
                $attrs['prefix']    = array(
                    'type' => 'none', // none | button | label
                    'label' => null,
                    'id' => null,
                    'btnAction' => null,
                    'attrs' => array()
                );
                $attrs['postfix']   = array(
                    'type' => 'none', // none | button | label
                    'label' => null,
                    'id' => null,
                    'btnAction' => null,
                    'attrs' => array()
                );
                break;
            case self::FORM_TYPE_SELECT:
                $attrs['classes'][] = 'js-select';
                $attrs['multiple']  = false;
                $attrs['options']   = array();  // ParamOption[] | callback
                $attrs['size']      = 1;        // select size if 0 get num options
                break;
            case self::FORM_TYPE_CHECKBOX:
            case self::FORM_TYPE_SWITCH:
                $attrs['checkboxLabel'] = null;
                $attrs['checkedValue']  = true;
                break;
            case self::FORM_TYPE_M_CHECKBOX:
                $attrs['options']             = array();  // ParamOption[]  | callback
                $attrs['wrapperContainerTag'] = 'div';
                break;
            case self::FORM_TYPE_RADIO:
                $attrs['options']             = array();  // ParamOption[]  | callback
                $attrs['wrapperContainerTag'] = 'div';
                break;
            case self::FORM_TYPE_BGROUP:
                $attrs['options']               = array();  // ParamOption[]  | callback
                $attrs['wrapperContainerTag']   = 'span';
                $attrs['inputContainerClasses'] = array('input-container', 'btn-group');
                break;
            default:
            // accepts unknown values ​​because this class can be extended
        }

        return $attrs;
    }
}
