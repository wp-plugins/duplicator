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

/**
 * this class manages a password type input with the hide / show password button
 */
class ParamFormPass extends ParamForm
{
    const FORM_TYPE_PWD_TOGGLE = 'pwdtoggle';

    /**
     * Render HTML
     *
     * @return void
     */
    protected function htmlItem()
    {
        if ($this->formType == self::FORM_TYPE_PWD_TOGGLE) {
            $this->pwdToggleHtml();
        } else {
            parent::htmlItem();
        }
    }

    /**
     * return the text of current object for info only status
     *
     * @return string
     */
    protected function valueToInfo()
    {
        return '**********';
    }

    /**
     * Render PWD toggle element
     *
     * @return void
     */
    protected function pwdToggleHtml()
    {
        $attrs = array(
            'value' => $this->getInputValue(),
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

        $attrs = array_merge($attrs, $this->formAttr['attr']);

        \DUPX_U_Html::inputPasswordToggle($this->getAttrName(), $this->formAttr['id'], $this->formAttr['classes'], $attrs, true);
    }

    /**
     * Get default form attributes
     *
     * @param string $formType form type
     *
     * @return array
     */
    protected static function getDefaultAttrForFormType($formType)
    {
        $attrs = parent::getDefaultAttrForFormType($formType);
        if ($formType == self::FORM_TYPE_PWD_TOGGLE) {
            $attrs['maxLength'] = null;     // if null have no limit
            $attrs['size']      = null;
        }
        return $attrs;
    }
}
