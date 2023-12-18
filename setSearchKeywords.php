<?php
require 'php/sessionManager.php';
require 'DAL/PhotosCloudDB.php';
userAccess();


redirect('photosList.php?keywords='.$_GET["keywords"]."&sort=keywords"); 