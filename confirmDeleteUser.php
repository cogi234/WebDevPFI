<?php
    include 'php/sessionManager.php';
    require 'DAL/PhotosCloudDB.php';
    $viewTitle = "Retrait de compte";
    
    adminAccess(200);

    if (!isset($_GET["id"]))
        redirect("illegalAction.php");

    $id = (int) $_GET["id"];
    $user = UsersTable()->get($id);
    if (!$user)
        redirect("illegalAction.php");

    $name = $user->Name;
    $email = $user->Email;
    $avatar = $user->Avatar;

    $UserHTML = <<<HTML
    <div class="form UserRow ">
        <div class="UserContainer noselect">
            <div class="UserLayout">
                <div class="UserAvatar" style="background-image:url('$avatar')"></div>
                <div class="UserInfo">
                    <span class="UserName">$name</span>
                    <a href="mailto:$email" class="UserEmail" target="_blank" >$email</a>
                </div>
            </div>
        </div>
    </div>           
    HTML;

    $viewContent = <<<HTML
    <div class="content loginForm">
        
        <br>
        <h3> Voulez-vous vraiment effacer cet <br> usager et toutes ses photos? </h3>
        $UserHTML
        <div class="form">
            <a href="deleteUser.php?id=$id"><button class="form-control btn-danger">Effacer</button>
            <br>
            <a href="manageUsers.php" class="form-control btn-secondary">Annuler</a>
        </div>
    </div>
    HTML;
    $viewScript = <<<HTML
        <script defer>
            $("#addPhotoCmd").hide();
        </script>
    HTML;
    include "views/master.php";