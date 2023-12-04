<?php

include_once 'DAL/models/record.php';

class User extends Record
{
    public $Name;
    public $Email;
    public $Password;
    public $Avatar;
    public $AccessType = 0; // user => 0 , admin => 1
    public $Blocked = 0; // not blocked => 0 , blocked => 1
    public function __construct($recordData = null)
    {
        $this->Name = "";
        $this->Email = "";
        $this->Password = "";
        $this->Avatar = "";
        $this->AccessType = 0;
        $this->Blocked = 0;
        $this->setCompareKey('Name');
        $this->setUniqueKey('Email');
        parent::__construct($recordData);
    }
    public function setName($name)
    {
        $this->Name = $name;
    }
    public function setEmail($email)
    {
        $this->Email = $email;
    }
    public function setPassword($password)
    {
        $this->Password = $password;
    }
    public function setAvatar($avatar)
    {
        $this->Avatar = $avatar;
    }
    public function setAccessType($accessType)
    {
        $this->AccessType = (int) $accessType;
    }
    public function setBlocked($blocked)
    {
        $this->Blocked = (int) $blocked;
    }
    public function isAdmin()
    {
        return $this->AccessType == 1;
    }

    public function isBlocked()
    {
        return $this->Blocked == 1;
    }
}