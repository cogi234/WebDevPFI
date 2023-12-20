<?php
require 'php/sessionManager.php';
require 'DAL/PhotosCloudDB.php';

userAccess();
if (!isset($_GET["photoId"])) 
    redirect("photosList.php");

$photoId = (int)$_GET["photoId"];
$userId = (int)$_SESSION["currentUserId"];

$like = LikesTable()->selectWhere("PhotoId = $photoId AND UserId = $userId")[0];


if (isset($like)){
    LikesTable()->delete($like->Id);
} else {
LikesTable()->insert(new Like(["UserId" => $userId, "PhotoId" => $photoId, "CreationDate" => date("Y-m-d H:i:s")]));
}

LikesTable()->updatePhotoLikeCount($photoId);

redirect($_SESSION["redirect"]);