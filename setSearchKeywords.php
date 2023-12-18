<?php
require 'php/sessionManager.php';
require 'DAL/PhotosCloudDB.php';
userAccess();

var_dump($_GET);
$_SESSION["photoSortType"] ="keywords";
redirect('photosList.php?keywords='.$_GET["keywords"]); 