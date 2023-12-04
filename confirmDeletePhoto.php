<?php
include 'php/sessionManager.php';
require 'DAL/PhotosCloudDB.php';
$viewTitle = "Retrait de photo";

userAccess(200);

if (!isset($_GET["id"]))
    redirect("illegalAction.php");
$id = (int) $_GET["id"];

$photo = PhotosTable()->get($id);
if ($photo == null)
    redirect("illegalAction.php");

$isAdmin = (bool) $_SESSION["isAdmin"];
if (!$isAdmin && ($photo->OwnerId != (int) $_SESSION["currentUserId"]))
    redirect("illegalAction.php");

$title = $photo->Title;
$description = $photo->Description;
$image = $photo->Image;

$photoHTML = <<<HTML
    <div class="photoLayout" photo_id="$id">
        <div class="photoTitle" title="$description">$title</div>
        <div class="photoImage" style="background-image:url('$image')"> </div>
    </div>           
    HTML;

$viewContent = <<<HTML
    <div class="content">
        <br>
        <div class="confirmForm">
            <h4> Voulez-vous vraiment effacer cette photo? </h4>
            <br>
            $photoHTML
            <br>
            <a href="deletePhoto.php?id=$id"><button class="form-control btn-danger">Effacer la photo</button>
            <br>
            <a href="photosList.php" >
                <button class="form-control btn-secondary">Annuler</button>
            </a>
        </div>
    </div>
    HTML;
$viewScript = <<<HTML
        <script defer>
            $("#addPhotoCmd").hide();
        </script>
    HTML;
include "views/master.php";