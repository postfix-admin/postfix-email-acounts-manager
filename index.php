<?php
session_start();

require 'Controllers/UserManager.php';
require 'Controllers/EmailManager.php';
require 'config/config.php';
define('BASE_PATH', __DIR__);

$userManager = new UserManager($pdo);
$emailManager = new EmailManager($pdo);

$emails = [];
if ($userManager->isLoggedIn()) {
    $emails = $emailManager->getAllEmails();
}

$success        = (string) filter_input(INPUT_GET, 'success', FILTER_SANITIZE_SPECIAL_CHARS);
$error          = (string) filter_input(INPUT_GET, 'message', FILTER_SANITIZE_SPECIAL_CHARS);
$reset_password = (string) filter_input(INPUT_GET, 'reset_password', FILTER_SANITIZE_SPECIAL_CHARS);
$reset_token    = (string) filter_input(INPUT_GET, 'reset_token', FILTER_SANITIZE_SPECIAL_CHARS);
$newPassword    = !empty($reset_token) ?  $userManager->checkResetToken($reset_token) : false;
$newUser        = $userManager->newInstalation();


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Simplessh.com Postfix Email Accounts Manager</title>
    <link rel="stylesheet" href="assets/bootstrap.min.css">
    <link rel="shortcut icon" href="assets/favicon.png" type="image/png"/>
    <style>
         .form-signin {
            max-width: 390px;
            background: #fafafa;
            border-radius: 20px;
            padding: 20px;
            margin-top: 28vh !important;
          }

          .form-signin .form-floating:focus-within {
            z-index: 2;
          }

          .form-signin input[type="email"] {
            margin-bottom: -1px;
            border-bottom-right-radius: 0;
            border-bottom-left-radius: 0;
          }

          .form-signin input[type="password"] {
            margin-bottom: 10px;
            border-top-left-radius: 0;
            border-top-right-radius: 0;
          }
    </style>
</head>
<body>
<?php if ($userManager->isLoggedIn()): ?>
    <?php require 'emails.php';?>
<?php else: ?>
   <div class="container mt-5">
     <main class="form-signin w-100 m-auto">
     <?php if ($reset_password=="yes"): ?>  
            <form action="auth/reset.php" method="post">
             <h1 class="h3 mb-3 fw-normal">Reset password</h1>
              <?php if (isset($success) && !empty($success)): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
              <?php endif; ?>
              <div class="form-floating">
               <input type="email" class="form-control" id="floatingInput" placeholder="name@example.com" name="username" required>
               <label for="floatingInput">Email address</label>
             </div>
             <button class="btn btn-primary w-100 py-2" type="submit">Reset password</button>
             <div style="text-align: center;">
                 <a href="/">Sign in</a>
             </div>
            </form>
        
        <?php elseif($newPassword): ?>
           <form action="auth/new.php" method="post">
              <input type="hidden"  name="reset_token" value="<?php echo $reset_token;?>">  
             <h1 class="h3 mb-3 fw-normal">Reset your password</h1>
              <?php if (isset($success) && !empty($success)): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
              <?php endif; ?>
              <div class="form-floating">
               <input type="password" class="form-control" id="floatingInput" placeholder="Enter new password" name="password" required>
               <label for="floatingInput">Enter new password</label>
             </div>
             <div class="form-floating">
               <input type="password" class="form-control" id="floatingInput" placeholder="Repeat password" name="repeatpassword" required>
               <label for="floatingInput">Repeat password</label>
             </div>   
             <button class="btn btn-primary w-100 py-2" type="submit">Update password</button>
             <div style="text-align: center;">
                 <a href="/">Sign in</a>
             </div>
            </form>
       
        <?php elseif(!$newUser): ?>
           <form action="auth/newuser.php" method="post">
              <h1 class="h3 mb-3 fw-normal">Create an admin</h1>
              <?php if (isset($success) && !empty($success)): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
              <?php endif; ?>
                
              <div class="form-floating">
               <input type="email" class="form-control" id="floatingInput" placeholder="name@example.com" name="username" required>
               <label for="floatingInput">Email address</label>
              </div>
                
              <div class="form-floating">
               <input type="password" class="form-control" id="floatingInput" placeholder="Enter new password" name="password" required>
               <label for="floatingInput">Enter new password</label>
             </div>
                 
             <button class="btn btn-primary w-100 py-2" type="submit">Create an admin</button>
              
            </form>  
        
        <?php else: ?>
            <form action="auth/login.php" method="post">
             <h1 class="h3 mb-3 fw-normal">Please sign in</h1>
              <?php if (isset($error) && !empty($error)): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
              <?php endif; ?>
              <?php if (isset($success) && !empty($success)): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
              <?php endif; ?>
             <div class="form-floating">
               <input type="email" class="form-control" id="floatingInput" placeholder="name@example.com" name="username" required>
               <label for="floatingInput">Email address</label>
             </div>
             <div class="form-floating">
               <input type="password" class="form-control" id="floatingPassword" placeholder="Password" name="password" required>
               <label for="floatingPassword">Password</label>
             </div>

             <button class="btn btn-primary w-100 py-2" type="submit">Sign in</button>
             <div class="clearboth"></div>
              <br/>
             <div style="text-align: center;">
                 <a href="/?reset_password=yes">Reset password</a>
             </div>
           </form>
     <?php endif; ?>
     </main>
   </div>
<?php endif; ?>
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
