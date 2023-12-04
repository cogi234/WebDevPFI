<?php
include 'php/sessionManager.php';
require 'DAL/PhotosCloudDB.php';

adminAccess();

$viewTitle = "Gestion des usagers";
$list = UsersTable()->get();
$viewContent = "";

foreach ($list as $User) {
    if ($User->Id != (int)$_SESSION["currentUserId"]) {
        $id = strval($User->Id);
        $name = $User->Name;
        $email = $User->Email;
        $avatar = $User->Avatar;
        $typeIcon = $User->AccessType == 0?"fas fa-user-alt":"fas fa-user-cog";
        $typeTitle = $User->AccessType == 0?"Octroyer le droit administrateur à":"Retirer le droit administrateur à";
        $blockedClass = $User->isBlocked() ? "class='cmdIconVisible fa fa-ban redCmd'": "class='cmdIconVisible fa-regular fa-circle greenCmd'";
        $blockedTitle = $User->isBlocked() ? "Débloquer $name": "Bloquer $name";
        $UserHTML = <<<HTML
        <div class="UserRow" User_id="$id">
            <div class="UserContainer noselect">
                <div class="UserLayout">
                    <div class="UserAvatar" style="background-image:url('$avatar')"></div>
                    <div class="UserInfo">
                        <span class="UserName">$name</span>
                        <a href="mailto:$email" class="UserEmail" target="_blank" >$email</a>
                    </div>
                </div>
                <div class="UserCommandPanel">
                    <a href="promoteUser.php?id=$id" class="cmdIconVisible $typeIcon" title="$typeTitle $name"></a>
                    <a href="blockUser.php?id=$id" $blockedClass title="$blockedTitle"></a>
                    <a href="confirmDeleteUser.php?id=$id" class="cmdIconVisible fas fa-user-slash goldenrodCmd" title="Effacer $name"></a>
                </div>
            </div>
        </div>           
        HTML;
        $viewContent = $viewContent . $UserHTML;
    }
}

$viewScript = <<<HTML
    <script src='js/session.js'></script>
    <script defer>
        $("#addPhotoCmd").hide();
    </script>
HTML;

include "views/master.php";
