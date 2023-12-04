<?php

include_once 'DAl/models/like.php';
include_once 'DAl/PhotosTable.php';
include_once "DAL/MySQLDataBase.php";

final class LikesTable extends MySQLTable
{
    public function __construct()
    {
        parent::__construct(DB(), new Like());
    }
    public function insert($like)
    {
        parent::insert($like);
        $this->updatePhotoLikeCount($like->PhotoId);
    }
    public function delete($id)
    {
        $likeToRemove = $this->get($id);
        if ($likeToRemove != null) {
            parent::delete($id);
            $this->updatePhotoLikeCount($likeToRemove->PhotoId);
            return true;
        }
        return false;
    }
    public function updatePhotoLikeCount($id)
    {
        $photo = PhotosTable()->get($id);
        if ($photo != null) {
            $likes = $this->selectWhere("PhotoId = $id");
            $photo->Likes = count($likes);
            PhotosTable()->update($photo);
        }
    }
    public function updatePhotosLikesCount()
    {
        $photos = PhotosTable()->selectAll();
        foreach ($photos as $photo) {
            $photoId = $photo->Id;
            $likes = $this->selectWhere("PhotoId = $photoId");
            $photo->Likes = count($likes);
            PhotosTable()->update($photo);
        }
    }
}
