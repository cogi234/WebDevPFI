<?php
include 'php/sessionManager.php';
require 'DAL/PhotosCloudDB.php';

userAccess();
$currentUserId = (int) $_SESSION["currentUserId"];
UsersTable()->delete($currentUserId);
redirect('loginForm.php');