<?php
session_start();
require 'Controllers/UserManager.php';
require 'config/config.php';

$userManager = new UserManager($pdo);


if (!$userManager->isLoggedIn()) {
    header('Location: /');
    exit;
}
 $users = $userManager->getAllUsers();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update'])) {
        $id       = htmlspecialchars(trim($_POST['id']));
        $email    = htmlspecialchars(trim($_POST['email']));
        $password = htmlspecialchars(trim($_POST['password']));
        $userManager->changePassword($id, $email, $password);
        header('Location: settings.php');
        exit;
    } elseif (isset($_POST['delete'])) {
        header('Location: settings.php');
        exit;
    } 
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Emails</title>
    <link rel="stylesheet" href="assets/bootstrap.min.css">
    <link rel="shortcut icon" href="assets/favicon.png" type="image/png"/>
</head>
<body>
<?php require 'header.php';?>
<div class="container mt-5">
 <h2>Settings</h2>
 <?php foreach ($users as $user): ?>
  <form method="post" class="mb-4">
      <input type="hidden" name="id" value="<?php echo $user['id'];?>">
   <div class="content">
     <div class="row">
         <div class="form-group col-md-4">
           <label for="email">Email</label>
           <input type="email" class="form-control" id="email" name="email" value="<?php echo $user['email'];?>" placeholder="account@domain.com" required>
         </div>
         <div class="form-group col-md-4">
           <label for="password">Password</label>
           <input type="password" class="form-control" id="password" name="password" placeholder="*****" required>
         </div>
         <div class="form-group col-md-3">
           <label for="password" style="width:100%;">&nbsp;</label>
           <button type="submit" name="update" class="btn btn-primary">Update</button> 
         </div>
      </div>
   </div>    
  </form>
 <?php endforeach; ?> 
</div>
<footer class="my-5 pt-5 text-body-secondary text-center text-small">
   <p class="mb-1">&copy; <?php echo date("Y");?> Postfix Email Manager | <a href="https://simplessh.com" target="_blank">simplessh.com</a></p>
</footer>
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>