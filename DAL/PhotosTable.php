<?php

include_once 'DAl/models/photo.php';
include_once "DAL/MySQLDataBase.php";
include_once 'php/imageFiles.php';
include_once 'DAL/LikesTable.php';

const photosPath = "data/images/photos/";

final class PhotosTable extends MySQLTable
{
    public function __construct()
    {
        parent::__construct(DB(), new Photo());
    }
    public function insert($photo)
    {
        $photo->setImage(saveImage(photosPath, $photo->Image));
        parent::insert($photo);
    }
    public function update($photo)
    {
        $photoToUpdate = $this->get($photo->Id);
        if ($photoToUpdate != null) {
            $photo->setImage(saveImage(photosPath, $photo->Image, $photoToUpdate->Image));
            parent::update($photo);
        }
    }
    public function delete($id)
    {
        $photoToRemove = $this->get($id);
        if ($photoToRemove != null) {
            LikesTable()->deleteWhere("PhotoId = $id");
            unlink($photoToRemove->Image);
            return parent::delete($id);
        }
        return false;
    }
}
