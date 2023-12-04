<?php
include 'php/sessionManager.php';
require 'DAL/PhotosCloudDB.php';
userAccess();
PhotosTable()->update(new Photo($_POST));
redirect('photosList.php');
