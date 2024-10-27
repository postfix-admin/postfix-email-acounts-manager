<?php
session_start();
require '../Controllers/UserManager.php';
require '../config/config.php';

$userManager = new UserManager($pdo);
// first of all install //sudo apt-get install sshpass
// sudo chown -R www-data:www-data /var/www/.ssh
//


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = (string) filter_input(INPUT_POST, 'username', FILTER_SANITIZE_SPECIAL_CHARS);
 
    if (!empty($username) && $userManager->resetPasswordRequest($username)) {
      header('Location: /?reset_password=yes&success=if an account is associated with this email, than you will receive an email');
      exit;
    }else{
      header('Location: /?reset_password=yes&success=if an account is associated with this email, than you will receive an email');
      exit;   
    }
}else{
    exit('<h2 style="text-align:center;">404 Page not found!</h2>');
}