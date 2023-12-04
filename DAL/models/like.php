<?php

include_once 'DAL/models/record.php';
class Like extends Record
{
    public $Id;
    public $UserId; 
    public $PhotoId;
    public $CreationDate;
   
    public function __construct($recordData = null)
    {
        $this->Id = 0;
        $this->UserId = 0;
        $this->PhotoId = 0;
        $this->CreationDate = date("Y-m-d H:i:s");
        $this->setCompareKey('CreationDate');
        parent::__construct($recordData);
    }
    public function setUserId($userId)
    {
        $this->UserId = (int) $userId;
    }
    public function setPhotoId($photoId)
    {
        $this->PhotoId = (int) $photoId;
    }
    public function setCreationDate($creationDate)
    {
        $this->CreationDate = $creationDate;
    }
}