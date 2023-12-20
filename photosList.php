<?php
include 'php/sessionManager.php';
include 'php/formUtilities.php';
include 'php/date.php';
require 'DAL/PhotosCloudDB.php';

$viewName = "photoList";
userAccess();
$viewTitle = "Photos";

$viewContent = "<div class='photosLayout'>";
$userId = (int) $_SESSION["currentUserId"];
$isAdmin = (bool) $_SESSION["isAdmin"];
$ownerPhotos = false;
if (isset($_GET["sort"]))
    $_SESSION["photoSortType"] = $_GET["sort"];

$sortType = $_SESSION["photoSortType"];
if (isset($_GET["keywords"]))
    $_SESSION["keywords"] = $_GET["keywords"];

$_SESSION["redirect"] = $_SERVER["REQUEST_URI"];


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
function compareLikes($a, $b)
{
    $la = $a->Likes;
    $lb = $b->Likes;

    return $lb - $la;
}
function createCondition($string)
{
    return "(Title LIKE('%$string%') OR Description LIKE('%$string%'))";
}

switch ($sortType) {
    case "date":
        $list = PhotosTable()->get();
        usort($list, 'compareDate');
        break;
    case "likes":
        $list = PhotosTable()->get();
        usort($list, 'compareLikes');
        break;
    case "keywords":
        if (isset($_SESSION["keywords"])) {
            $keyword = $_SESSION["keywords"];
            $keywords = explode(" ", $keyword);
            $keywords = array_map('createCondition', $keywords);
            
            $condition = implode(' OR ', $keywords);
            $list = PhotosTable()->selectWhere($condition);
        }
        else
        {
            $list = PhotosTable()->get();
        }


        break;
    case "owners":
        // todo
        break;
    case "owner":
        $list = PhotosTable()->get();
        $ownerPhotos = true;
        usort($list, 'compareDate');
        break;

    default:
        $list = PhotosTable()->get();
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

        $userLike = count(LikesTable()->selectWhere("UserId = $userId AND PhotoId = $id")) > 0;
        $photoLikedByConnectedUser = $userLike ? "fa" : "fa-regular";

        //Find every user who liked
        $likesUsersList = [];
        $likers = LikesTable()->selectWhere("PhotoId = $id");
        foreach ($likers as $liker) {
            array_push($likesUsersList, UsersTable()->selectById($liker->UserId)[0]->Name);
        }
        $likesUsersString = implode("\n", $likesUsersList);


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
                            <div class="likesSummary" title="$likesUsersString">
                                $likes
                                <a href="togglePhotoLike.php?photoId=$id" class="cmdIconSmall $photoLikedByConnectedUser fa-thumbs-up" id="addRemoveLikeCmd" title="$likesUsersString" ></a> 
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
