<?php
require 'php/sessionManager.php';
require 'DAL/PhotosCloudDB.php';

userAccess();
PhotosTable()->insert(new Photo($_POST));
redirect('photosList.php'); 