<?php
session_start();
require '../Controllers/UserManager.php';
require '../config/config.php';

$userManager = new UserManager($pdo);
// first of all install //sudo apt-get install sshpass
// sudo chown -R www-data:www-data /var/www/.ssh
//


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $reset_token = (string) filter_input(INPUT_POST, 'reset_token', FILTER_SANITIZE_SPECIAL_CHARS);
    $password    = (string) filter_input(INPUT_POST, 'password', FILTER_SANITIZE_SPECIAL_CHARS);
    
    if (!empty($password) && $userManager->checkResetToken($reset_token)) {
      $userManager->updatePassword($reset_token, $password);
      header('Location: /?success=Your password has been updated');
      exit;
    }else{
      header('Location: /?success=Your password has been updated');
      exit;   
    }
}else{
    exit('<h2 style="text-align:center;">404 Page not found!</h2>');
}