<?php
require 'php/sessionManager.php';
require 'DAL/PhotosCloudDB.php';

userAccess();
if (!isset($_GET["photoId"])) 
    redirect("photosList.php");

$photoId = (int)$_GET["photoId"];
$userId = (int)$_SESSION["currentUserId"];
// todo
redirect("photoDetails.php?id=$photoId");