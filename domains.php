<?php
session_start();
require 'Controllers/UserManager.php';
require 'Controllers/DomainsManager.php';
require 'config/config.php';

$userManager = new UserManager($pdo);


if (!$userManager->isLoggedIn()) {
    header('Location: /');
    exit;
}

$domainsManager = new DomainsManager($pdo);
$domains = $domainsManager->getAllDomains();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add'])) {
        $host     = htmlspecialchars(trim($_POST['host'])); 
        $user     = htmlspecialchars(trim($_POST['user']));
        $password = htmlspecialchars(trim($_POST['password']));
        $domain   = htmlspecialchars(trim($_POST['domain']));
        
        $domainsManager->addDomain($host, $user, $password, $domain);
        header('Location: domains.php');
        exit;
    } elseif (isset($_POST['delete'])) {
        $id = htmlspecialchars(trim($_POST['id']));
        $domainsManager->deleteDomain($id);
        header('Location: domains.php');
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
        <h2>Manage Emails</h2>
        <form method="post" class="mb-4">
          <div class="content">
            <div class="row">
                <div class=" col-md-12">
                  Enter the ssh access, this is require to add domain also in DKIM and POSTSRSD, the ssh will not be saved, it is used only once
                </div>
                <div class="form-group col-md-4">
                  <label for="host">SSH Host</label>
                  <input type="text" class="form-control" id="host" name="host" placeholder="192.168.0.1" required>
                </div>
                <div class="form-group col-md-4">
                  <label for="user">SSH User</label>
                  <input type="text" class="form-control" id="user" name="user" placeholder="root" required>
                </div>
                <div class="form-group col-md-4">
                  <label for="password">SSH password</label>
                  <input type="text" class="form-control" id="password"  placeholder="*****" name="password" required>
                </div>
                
                <div class="form-group col-md-4">
                  <label for="email">Domain Name</label>
                  <input type="text" class="form-control" id="email" name="domain" placeholder="example.com" required>
                </div>
                
                <div class="form-group col-md-3">
                  <label for="password" style="width:100%;">&nbsp;</label>
                  <button type="submit" name="add" class="btn btn-primary">Add Domain</button> 
                </div>
             </div>
          </div>    
        </form>
        
        <h3>Existing Emails</h3>
        <table class="table">
            <thead>
                <tr>
                    <th style="width:50px">ID</th>
                    <th>Domain  Name</th>
                    <th style="text-align: right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($domains as $domain): ?>
                    <tr>
                        <td><?php echo $domain['id']; ?></td>
                        <td><?php echo $domain['name']; ?></td>
                         
                        <td style="text-align: right;">
                            <form method="post" class="d-inline" onsubmit="return confirm('Are you sure you want to delete: <?php echo htmlspecialchars($domain['name']); ?>?') ? true : false;">
                                <input type="hidden" name="id" value="<?php echo $domain['id']; ?>">
                                <button type="submit" name="delete" class="btn btn-link"> 
                                   <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-x-circle-fill" viewBox="0 0 16 16">
                                     <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0M5.354 4.646a.5.5 0 1 0-.708.708L7.293 8l-2.647 2.646a.5.5 0 0 0 .708.708L8 8.707l2.646 2.647a.5.5 0 0 0 .708-.708L8.707 8l2.647-2.646a.5.5 0 0 0-.708-.708L8 7.293z"/>
                                   </svg>
                                </button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <footer class="my-5 pt-5 text-body-secondary text-center text-small">
        <p class="mb-1">&copy; <?php echo date("Y");?> Postfix Email Manager | <a href="https://simplessh.com" target="_blank">simplessh.com</a></p>
    </footer>
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>