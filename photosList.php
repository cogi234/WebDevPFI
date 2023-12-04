<?php
include 'php/sessionManager.php';
include 'php/formUtilities.php';
include 'php/date.php';
require 'DAL/PhotosCloudDB.php';

$viewName = "photoList";
userAccess();
$viewTitle = "Photos";
$list = PhotosTable()->get();
$viewContent = "<div class='photosLayout'>";
$isAdmin = (bool) $_SESSION["isAdmin"];
$ownerPhotos = false;
if (isset($_GET["sort"]))
    $_SESSION["photoSortType"] = $_GET["sort"];
$sortType = $_SESSION["photoSortType"];
function compareDate($a, $b)
{
    $dateA = strtotime($a->CreationDate);
    $dateB = strtotime($b->CreationDate);

    return $dateB - $dateA; // décroissant
    /* 
        if ($dateA == $dateB)
            return 0;
        return ($dateA < $dateB) ? 1 : -1;
    */
}
function compareOwner($a, $b)
{
    $ownerName_A = no_Hyphens(UsersTable()->get($a->OwnerId)->Name);
    $ownerName_B = no_Hyphens(UsersTable()->get($b->OwnerId)->Name);
    return strcmp($ownerName_A, $ownerName_B);
}

switch ($sortType) {
    case "date":
        usort($list, 'compareDate');
        break;
    case "likes":
        // todo
        break;
    case "keywords":
        // todo
        break;
    case "owners":
        // todo
        break;
    case "owner":
        $ownerPhotos = true;
        usort($list, 'compareDate');
        break;
}

foreach ($list as $photo) {
    if ($ownerPhotos && ($photo->OwnerId == (int) $_SESSION["currentUserId"]) || !$ownerPhotos) {
        $id = strval($photo->Id);
        $title = $photo->Title;
        $description = $photo->Description;
        $image = $photo->Image;
        $owner = UsersTable()->Get($photo->OwnerId);
        $ownerName = $owner->Name;
        $ownerAvatar = $owner->Avatar;
        $shared = $photo->Shared == "true";
        $creationDate = timeStampToFullDate(strtotime($photo->CreationDate));
        $sharedIndicator = "";
        $editCmd = "";
        $likes = $photo->Likes;
        $visible = $shared || $isAdmin;

        if (($photo->OwnerId == (int) $_SESSION["currentUserId"]) || $isAdmin) {
            $visible = true;
            $editCmd = <<<HTML
                <a href="editPhotoForm.php?id=$id" class="cmdIconSmall fa fa-pencil" title="Editer $title"> </a>
                <a href="confirmDeletePhoto.php?id=$id"class="cmdIconSmall fa fa-trash" title="Effacer $title"> </a>
            HTML;
            if ($shared) {
                $sharedIndicator = <<<HTML
                    <div class="UserAvatarSmall transparentBackground" style="background-image:url('images/shared.png')" title="partagée"></div>
                HTML;
            }
        }
        if ($visible) {
            $photoHTML = <<<HTML
                <div class="photoLayout" photo_id="$id">
                    <div class="photoTitleContainer" title="$description">
                        <div class="photoTitle ellipsis">$title</div>
                        $editCmd
                    </div>
                    <a href="photoDetails.php?id=$id">
                        <div class="photoImage" style="background-image:url('$image')">
                            <div class="UserAvatarSmall transparentBackground" style="background-image:url('$ownerAvatar')" title="$ownerName"></div>
                            $sharedIndicator
                        </div>
                        <div class="photoCreationDate"> 
                            $creationDate
                            <div class="likesSummary">
                                $likes
                                <i class="cmdIconSmall fa-regular fa-thumbs-up"></i> 
                            </div>
                        </div>
                    </a>
                </div>           
            HTML;
            $viewContent = $viewContent . $photoHTML;
        }
    }
}
$viewContent = $viewContent . "</div>";
$viewScript = <<<HTML
    <script defer>
        $("#setPhotoOwnerSearchIdCmd").on("click", function() {
            window.location = "setPhotoOwnerSearchId.php?id=" + $("#userSelector").val();
        });
        $("#setSearchKeywordsCmd").on("click", function() {
            window.location = "setSearchKeywords.php?keywords=" + $("#keywords").val();
        });
    </script>
HTML;
include "views/master.php";
