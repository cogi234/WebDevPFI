<?php
include_once 'DAL/models/user.php';
include_once "DAL/MySQLDataBase.php";
include_once 'php/imageFiles.php';
include_once 'DAL/PhotosTable.php';
include_once 'DAL/LikesTable.php';

const avatarsPath = "data/images/avatars/";

final class UsersTable extends MySQLTable
{
    public function __construct()
    {
        parent::__construct(DB(), new User());
    }
    public function emailExist($email)
    {
        $user = $this->selectWhere("Email = '$email'");
        return isset($user[0]);
    }
    public function findByEmail($email)
    {
        $user = $this->selectWhere("Email = '$email'");
        if (isset($user[0]))
            return $user[0];
        return null;
    }
    public function userValid($email, $password)
    {
        $user = $this->selectWhere("Email = '$email'");
        if (isset($user[0])) {
            return password_verify($password, $user[0]->Password);
        }
        return false;
    }
    public function insert($user)
    {
        $user->setAvatar(saveImage(avatarsPath, $user->Avatar));
        parent::insert($user);
    }
    public function update($user)
    {
        $userToUpdate = $this->get($user->Id);
        if ($user->Password == "")
            $user->Password = $userToUpdate->Password;
        if ($userToUpdate != null) {
            $user->setAvatar(saveImage(avatarsPath, $user->Avatar, $userToUpdate->Avatar));
            parent::update($user);
        }
    }
    public function delete($id)
    {
        $userToRemove = $this->get($id);
        if ($userToRemove != null) {
            $userId = $userToRemove->Id;
            LikesTable()->deleteWhere("UserId = $userId");
            PhotosTable()->deleteWhere("OwnerId = $userId");
            LikesTable()->updatePhotosLikesCount();
            unlink($userToRemove->Avatar);
            return parent::delete($id);
        }
        return false;
    }
}