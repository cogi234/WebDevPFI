<?php
require 'php/sessionManager.php';
require 'DAL/PhotosCloudDB.php';
userAccess();
UsersTable()->update(new User($_POST));
$user = UsersTable()->get($_SESSION['currentUserId']);
$_SESSION["name"] = $user->Name;
$_SESSION["avatar"] = $user->Avatar;
$_SESSION['Email'] = $user->Email;
redirect('photosList.php');