<?php
include 'php/sessionManager.php';
require 'DAL/PhotosCloudDB.php';
$viewTitle = "Retrait de photo";

userAccess();

if(!isset($_GET["id"]))
    redirect("illegalAction.php");

$id = (int) $_GET["id"];

$photo = PhotosTable()->get($id);
if ($photo == null)
    redirect("illegalAction.php");

if (($photo->OwnerId != (int) $_SESSION["currentUserId"]) AND (!$_SESSION["isAdmin"]))
    redirect("illegalAction.php");

PhotosTable()->delete($id);
redirect("photosList.php");