<?php
include_once 'DAL/MySQLDataBase.php';
include_once 'DAL/UsersTable.php';
include_once 'DAL/PhotosTable.php';
include_once 'DAL/LikesTable.php';
function DB()
{
    return MySQLDataBase::getInstance('epiz_...._myappdb', 'sql112.infinityfree.com', 'epiz_....', 'mot de passe');
}
function UsersTable()
{
    return new UsersTable();
}
function PhotosTable()
{
    return new PhotosTable();
}
function LikesTable()
{
    return new LikesTable();
}