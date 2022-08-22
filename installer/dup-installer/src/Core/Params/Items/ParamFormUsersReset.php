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

use Duplicator\Installer\Core\Params\PrmMng;
use Duplicator\Installer\Core\Params\Descriptors\ParamDescUsers;

/**
 * this class manages a password type input with the hide / show passwrd button
 */
class ParamFormUsersReset extends ParamFormPass
{
    const FORM_TYPE_USERS_PWD_RESET = 'usrpwdreset';

    protected $currentUserId = -1;

    /**
     * Get html form option of current item
     *
     * @param bool $echo if true echo html
     *
     * @return string
     */
    public function getHtml($echo = true)
    {
        if ($this->formType == self::FORM_TYPE_USERS_PWD_RESET) {
            $result = '';
            $users  = \DUPX_ArchiveConfig::getInstance()->getUsersLists();

            $mainInputId = $this->formAttr['id'];
            foreach ($users as $userId => $login) {
                $this->currentUserId     = $userId;
                $this->formAttr['id']    = $mainInputId . '_' . $this->currentUserId;
                $this->formAttr['label'] = $login;
                $result                 .= parent::getHtml($echo);
            }
            $this->currentUserId  = -1;
            $this->formAttr['id'] = $mainInputId;
            return $result;
        } else {
            return parent::getHtml($echo);
        }
    }

    /**
     * Display the html input of current item
     *
     * @return void
     */
    protected function htmlItem()
    {
        if ($this->formType == self::FORM_TYPE_USERS_PWD_RESET) {
            $this->pwdToggleHtml();
        } else {
            parent::htmlItem();
        }
    }

    /**
     * Return attribute name
     *
     * @return string
     */
    protected function getAttrName()
    {
        return $this->name . '[' . $this->currentUserId . ']';
    }

    /**
     * Return input value
     *
     * @return mixed
     */
    protected function getInputValue()
    {
        return isset($this->value[$this->currentUserId]) ? $this->value[$this->currentUserId] : '';
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
        if ($formType == self::FORM_TYPE_USERS_PWD_RESET) {
            $attrs['maxLength'] = null;     // if null have no limit
            $attrs['size']      = null;
        }
        return $attrs;
    }
}
