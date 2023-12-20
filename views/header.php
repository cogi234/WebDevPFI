<?php
$pageTitle = "Photos Cloud";
if (!isset($viewTitle))
    $viewTitle = "";
if (!isset($viewHeadCustom))
    $viewHeadCustom = "";
if (!isset($viewName))
    $viewName = "";

$loggedUserMenu = "";
$connectedUserAvatar = "";

if (isset($_SESSION["validUser"])) {

    $avatar = $_SESSION["avatar"];
    $userName = $_SESSION["userName"];
    $loggedUserMenu = "";
    if (isset($_SESSION["isAdmin"]) && (bool) $_SESSION["isAdmin"]) {
        $loggedUserMenu = <<<HTML
            <a href="manageUsers.php" class="dropdown-item">
                <i class="menuIcon fas fa-user-cog mx-2"></i> Gestion des usagers
            </a>
            <div class="dropdown-divider"></div>
        HTML;
    }

    $loggedUserMenu .= <<<HTML
        <a href="logout.php" class="dropdown-item">
            <i class="menuIcon fa fa-sign-out mx-2"></i> Déconnexion
        </a>
        <a href="editProfilForm.php" class="dropdown-item">
            <i class="menuIcon fa fa-user-edit mx-2"></i> Modifier votre profil
        </a>
        <div class="dropdown-divider"></div>
        <a href="photosList.php" class="dropdown-item">
            <i class="menuIcon fa fa-image mx-2"></i> Liste des photos
        </a>
    HTML;
    $connectedUserAvatar = <<<HTML
        <div class="UserAvatarSmall" style="background-image:url('$avatar')" title="$userName"></div>
    HTML;
} else {
    $loggedUserMenu = <<<HTML
        <a href="loginForm.php" class="dropdown-item" id="loginCmd">
            <i class="menuIcon fa fa-sign-in mx-2"></i> Connexion
        </a> 
    HTML;
    $connectedUserAvatar = <<<HTML
        <div>&nbsp;</div>
    HTML;
}



$viewMenu = "";
if (strcmp($viewName, "photoList") == 0) {
    $sortType = isset($_SESSION["photoSortType"]) ? $_SESSION["photoSortType"] : "date";
    $checkIcon = '<i class="menuIcon fa fa-check mx-2"></i>';
    $uncheckIcon = '<i class="menuIcon fa fa-fw mx-2"></i>';
    $sortByDateCheck = ($sortType == "date") ? $checkIcon : $uncheckIcon;
    $sortByLikeCheck = ($sortType == "likes") ? $checkIcon : $uncheckIcon;
    $sortByOwners = ($sortType == "owners") ? $checkIcon : $uncheckIcon;
    $sortByKeywords = ($sortType == "keywords") ? $checkIcon : $uncheckIcon;
    $ownerOnly = ($sortType == "owner") ? $checkIcon : $uncheckIcon;
    $viewMenu = <<<HTML
         <a href="photosList.php?sort=date" class="dropdown-item">
            $sortByDateCheck <i class="menuIcon fa fa-calendar mx-2"></i>Photos par date de création
         </a>
         <a href="photosList.php?sort=likes" class="dropdown-item">
            $sortByLikeCheck <i class="menuIcon fa fa-heart mx-2"></i>Photos les plus aimées
         </a>
         <a href="photosList.php?sort=keywords" class="dropdown-item">
            $sortByKeywords <i class="menuIcon fa fa-search mx-2"></i>Photos par mots-clés
         </a> 
         <a href="photosList.php?sort=owners" class="dropdown-item">
            $sortByOwners <i class="menuIcon fa fa-users mx-2"></i>Photos par créateur
         </a>
         <a href="photosList.php?sort=owner" class="dropdown-item">
            $ownerOnly <i class="menuIcon fa fa-user mx-2"></i>Mes photos
         </a>
        HTML;
    if ($sortType == "owners") {
        $users = UsersTable()->get();
        $userOptions = "";
        $selected = -1;
        if (isset($_GET["owner"]))
            $selected = $_GET["owner"];

        foreach ($users as $u) {
            if ($u->Id == $selected)
                $userOptions .= "<option selected=\"selected\" value=" . $u->Id . "> " . $u->Name . "</option>\n";
            else
                $userOptions .= "<option value=" . $u->Id . "> " . $u->Name . "</option>\n";
        }

        $viewHeadCustom = <<<HTML
            <div class="searchContainer">
                <select class="form-select userSelector" id="userSelector"> 
                    <option value="-1"> Tous les usagers</option>
                    $userOptions
                </select>
                <i class="cmdIcon fa fa-search" id="setPhotoOwnerSearchIdCmd"></i>
            </div>
        HTML;
    }
    if ($sortType == "keywords") {
        $viewHeadCustom = <<<HTML
           <div class="searchContainer">
                <input type="search" class="form-control" placeholder="Recherche par mots-clés" id="keywords" />
                <i class="cmdIcon fa fa-search" id="setSearchKeywordsCmd"></i>
            </div>
        HTML;
    }
}

$viewHead = <<<HTML
        <a href="photosList.php" title="Liste des photos"><img src="images/PhotoCloudLogo.png" class="appLogo"></a>
        <span class="viewTitle">$viewTitle 
            <a href="newPhotoForm.php" class="cmdIcon fa fa-plus" id="addPhotoCmd" title="Ajouter une photo"></a>
        </span>
        
        <div class="headerMenusContainer">
            <span>&nbsp</span> <!--filler-->
            <a href="editProfilForm.php" title="Modifier votre profil"> $connectedUserAvatar </a>         
            <div class="dropdown ms-auto dropdownLayout">
                <div data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="cmdIcon fa fa-ellipsis-vertical"></i>
                </div>
                <div class="dropdown-menu noselect">
                    $loggedUserMenu
                    $viewMenu
                    <div class="dropdown-divider"></div>
                    <a href="about.php" class="dropdown-item" id="aboutCmd">
                        <i class="menuIcon fa fa-info-circle mx-2"></i> À propos...
                    </a>
                </div>
            </div>

        </div>
    HTML;

