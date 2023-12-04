<?php
require 'DAL/PhotosCloudDB.php';

$result = UsersTable()->Conflict($_GET['Email'], $_GET['Id']);

header('Content-type: application/json');
echo json_encode($result);