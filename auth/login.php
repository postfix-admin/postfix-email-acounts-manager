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
    $password = (string) filter_input(INPUT_POST, 'password', FILTER_SANITIZE_SPECIAL_CHARS);
 
    if (!empty($username) && !empty($password) && $userManager->validateUser($username, $password)) {
        $_SESSION['username'] = $username;
        header('Location: /');
        exit;
    } else {
        header('Location: /?message=Invalid username or password, try again');
        exit;
    }
}else{
    exit('<h2 style="text-align:center;">404 Page not found!</h2>');
}