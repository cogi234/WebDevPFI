<?php
include 'php/sessionManager.php';
include 'php/formUtilities.php';
require 'DAL/PhotosCloudDB.php';

$id = 0;
$password = null;
$avatar = "images/no-avatar.png";
$userName = "";
$isAdmin = 0;
$isBlocked = 0;
function EmailExist($email)
{
    if (isset($email)) {
        $user = UsersTable()->findByEmail($email);
        if ($user == null)
            return false;
        $GLOBALS["id"] = $user->Id;
        $GLOBALS["userName"] = $user->Name;
        $GLOBALS["avatar"] = $user->Avatar;
        $GLOBALS["password"] = $user->Password;
        $GLOBALS["isAdmin"] = $user->isAdmin();
        $GLOBALS["isBlocked"] = $user->isBlocked();
        return true;
    }
    return false;
}
function passwordOk($password)
{
   return UsersTable()->userValid($_POST['Email'], $password);
}

if (isset($_POST['submit'])) {
    $validUser = true;
    $_SESSION['Email'] = sanitizeString($_POST['Email']);
    if (!EmailExist($_SESSION['Email'])) {
        $validUser = false;
        $_SESSION['EmailError'] = 'Ce courriel n\'existe pas';
    }
    if ($isBlocked) {
        $validUser = false;
        $_SESSION['EmailError'] = 'Votre compte a été blocké par le modérateur';
    }
    if (!passwordOk(sanitizeString($_POST['Password']))) {
        $validUser = false;
        $_SESSION['passwordError'] = 'Mot de passe incorrect';
    }
    if ($validUser) {
        $_SESSION['validUser'] = true;
        $_SESSION['isAdmin'] = $isAdmin;
        $_SESSION['currentUserId'] = $id;
        $_SESSION['userName'] = $userName;
        $_SESSION['avatar'] = $avatar;
        $_SESSION["photoSortType"] = "date";
        redirect('photosList.php');
    }
}

redirect('loginForm.php');
