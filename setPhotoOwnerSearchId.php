<?php
require 'php/sessionManager.php';
require 'DAL/PhotosCloudDB.php';
userAccess();


redirect('photosList.php?owner='.$_GET["id"]."&sort=owners"); 