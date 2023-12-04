<?php
require 'php/sessionManager.php';
require 'DAL/PhotosCloudDB.php';

anonymousAccess();
UsersTable()->insert(new User($_POST));
redirect('loginForm.php'); 