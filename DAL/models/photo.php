<?php

include_once 'DAL/models/record.php';
class Photo extends Record
{
    public $Id;
    public $OwnerId; // Id du propriétaire de la photo
    public $Title; // Titre de la photo
    public $Description; // Description de la photo
    public $CreationDate; // Date de création
    /** VARCHAR(5) */
    public $Shared; // Indicateur de partage ("true" ou "false")
    public $Image; // Url relatif de l'image
    public $Likes;

    public function __construct($recordData = null)
    {
        $this->Id = 0;
        $this->OwnerId = 0;
        $this->Title = '';
        $this->Description = '';
        $this->CreationDate = date("Y-m-d H:i:s");
        $this->Shared = "false";
        $this->Image = '';
        $this->Likes = 0;
        $this->setCompareKey('CreationDate');
        parent::__construct($recordData);
    }
    public function setOwnerId($ownerId)
    {
        $this->OwnerId = (int) $ownerId;
    }
    public function setTitle($title)
    {
        $this->Title = $title;
    }
    public function setDescription($description)
    {
        $this->Description = $description;
    }
    public function setShared($shared)
    {
        if ($shared == "on")
            $this->Shared = "true";
        else
            $this->Shared = $shared == "true" ? "true" : "false";
    }
    public function setImage($image)
    {
        $this->Image = $image;
    }
    public function setCreationDate($creationDate)
    {
        $this->CreationDate = $creationDate;
    }
    public function setLikes($likes)
    {
        $this->Likes = $likes;
    }
}