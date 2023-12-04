<?php
include 'php/sessionManager.php';
require 'DAL/PhotosCloudDB.php';

adminAccess();

if (!isset($_GET["id"]))
    redirect("illegalAction.php");

$id = (int) $_GET["id"];
$user = UsersTable()->get($id);
if (!$user)
    redirect("illegalAction.php");

UsersTable()->delete($id);
redirect('manageUsers.php');