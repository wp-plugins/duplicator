<?php

namespace Duplicator\Installer\Models;

class ImportUser
{
    /** @var int */
    protected $id = 0;
    /** @var string */
    protected $login = '';
    /** @var string */
    protected $mail = '';
    /** @var int */
    protected $oldId = 0;
    /** @var string */
    protected $oldLogin = '';
    /** @var bool */
    protected $isAdded = false;

    /**
     * Class contructor
     *
     * @param int     $id       user id
     * @param string  $login    user login
     * @param string  $mail     user e-mail
     * @param integer $oldId    old user id, if 0 isn't changed
     * @param string  $oldLogin old user login, if empty isn't changed
     * @param boolean $isAdded  if true this is new user
     */
    public function __construct($id, $login, $mail, $oldId = 0, $oldLogin = '', $isAdded = false)
    {
        $this->id       = (int) $id;
        $this->login    = $login;
        $this->mail     = $mail;
        $this->oldId    = (int) $oldId;
        $this->oldLogin = $oldLogin;
        $this->isAdded  = $isAdded;

        if ($this->id == $this->oldId) {
            $this->oldId = 0;
        }
        if ($this->login == $this->oldLogin) {
            $this->oldLogin = '';
        }
    }

    /**
     * Return CSV report columns title
     *
     * @return string[]
     */
    public static function getArrayReportTitles()
    {
        return array(
            'e-mail',
            'original login',
            'new login',
            'original id',
            'new id'
        );
    }

    /**
     * Return array for CSV report
     *
     * @return array
     */
    public function getArrayReport()
    {
        $result = array($this->mail);
        if (strlen($this->oldLogin) == 0) {
            $result[] = $this->login;
            $result[] = '';
        } else {
            $result[] = $this->oldLogin;
            $result[] = $this->login;
        }

        if ($this->oldId == 0) {
            $result[] = $this->id;
            $result[] = '';
        } else {
            $result[] = $this->oldId;
            $result[] = $this->id;
        }

        return $result;
    }

    /**
     * Get the value of id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get the value of login
     *
     * @return string
     */
    public function getLogin()
    {
        return $this->login;
    }

    /**
     * Get the value of mail
     *
     * @return string
     */
    public function getMail()
    {
        return $this->mail;
    }

    /**
     * Get the value of oldId
     *
     * @return int
     */
    public function getOldId()
    {
        return ($this->oldId == 0 ? $this->id : $this->oldId);
    }

    /**
     * Set the value of oldId
     *
     * @param int $oldId old mapped id
     *
     * @return void
     */
    public function setOldId($oldId)
    {
        $this->oldId = (int) ($this->id == $oldId ? 0 : $oldId);
    }

    /**
     * Get the value of oldLogin
     *
     * @return string
     */
    public function getOldLogin()
    {
        return (strlen($this->oldLogin) == 0 ? $this->login : $this->oldLogin);
    }

    /**
     * Set the value of oldLogin
     *
     * @param string $oldLogin old login
     *
     * @return  void
     */
    public function setOldLogin($oldLogin)
    {
        $this->oldLogin = ($this->login == $oldLogin ? '' : $oldLogin);
    }

    /**
     * True if current user have changed values (login or id)
     *
     * @return boolean
     */
    public function isChanged()
    {
        return ($this->oldId > 0 || strlen($this->oldLogin) > 0);
    }

    /**
     * True if current user is added
     *
     * @return bool
     */
    public function isAdded()
    {
        return $this->isAdded;
    }
}
