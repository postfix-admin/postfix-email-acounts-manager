<?php
if (!defined('BASE_PATH')) {
    // Redirect to the home page
    header('Location: /');
    exit; // Ensure no further code is executed
}

if (empty($userManager) || !$userManager->isLoggedIn()) {
    header('Location: /');
    exit;
}

$pageGet     = filter_input(INPUT_GET, 'page', FILTER_SANITIZE_SPECIAL_CHARS); // Example: page number
$currentPage = empty($pageGet) ? 1 : $pageGet;

$perPage = 70; // Example: items per page
$emails  = $emailManager->getAllEmails($currentPage, $perPage);
$domains = $emailManager->getAllDomains();

// Fetch total count
$totalCount = $emailManager->getTotalEmailsCount();
$totalPages = ceil($totalCount / $perPage);


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add'])) {
        $iddomain = htmlspecialchars(trim($_POST['iddomain'])); 
        $email    = htmlspecialchars(trim($_POST['email']));
        $password = htmlspecialchars(trim($_POST['password']));
        $forward  = htmlspecialchars(trim($_POST['forward']));
        $emailManager->addEmail($iddomain, $email, $password, $forward);
        header('Location: /');
        exit;
    } elseif (isset($_POST['delete'])) {
        $id    = htmlspecialchars(trim($_POST['id']));
        $email = htmlspecialchars(trim($_POST['email']));
        $emailManager->deleteEmail($id, $email);
        header('Location: /');
        exit;
    } elseif (isset($_POST['edit'])) {
        $id       = htmlspecialchars(trim($_POST['id']));
        $email    = htmlspecialchars(trim($_POST['email']));
        $domainID = htmlspecialchars(trim($_POST['domainID']));
        $password = htmlspecialchars(trim($_POST['password']));
        $forward  = htmlspecialchars(trim($_POST['forward']));
        $emailManager->updateEmail($id, $domainID, $email, $password, $forward);
        header('Location: /');
        exit;
    }
}
?>

<?php require 'header.php';?>
<div class="container mt-5">
  <h2>Manage Emails</h2>
  <form method="post" class="mb-4">
   <div class="content">
     <div class="row">
       <div class="form-group col-md-3">
            <label for="email">Domain</label>
            <select class="form-control" name="iddomain" required="">
                <option value="">Select domain</option>
                <?php foreach ($domains as $domain): ?>
                <option value="<?php echo $domain['id']; ?>"><?php echo $domain['name']; ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group col-md-3">
          <label for="email">Email Name (without @)</label>
          <input type="text" class="form-control" id="email" placeholder="support" name="email" required>
        </div>
        <div class="form-group col-md-3">
          <label for="password">Password</label>
          <input type="password" class="form-control" id="password" name="password" required>
        </div>
          <div class="form-group col-md-3">
          <label for="forward">Forward to(leave empty if you don't need)</label>
          <input type="email" class="form-control" id="forward" placeholder="smith@gmail.com" name="forward">
        </div>
        <div class="form-group col-md-3">
          <label for="password" style="width:100%;">&nbsp;</label>
          <button type="submit" name="add" class="btn btn-primary">Add Email</button> 
        </div>
     </div>
   </div>    
  </form>

  <h3>Existing Emails</h3>
  <table class="table">
    <thead>
        <tr>
            <th style='width:50px'>ID</th>
            <th>Email</th>
            <th>Forward</th>
            <th style="text-align: right;">Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($emails as $email): ?>
            <tr>
                <td><?php echo $email['id']; ?></td>
                <td><?php echo htmlspecialchars($email['email']); ?></td>
                <td><?php echo $email['destination']??''; ?></td>
                <td style="text-align: right;">
                    <button type="button" class="btn btn-link" data-toggle="modal" data-target="#editModal<?php echo $email['id']; ?>">
                      <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-pencil-square" viewBox="0 0 16 16">
                        <path d="M15.502 1.94a.5.5 0 0 1 0 .706L14.459 3.69l-2-2L13.502.646a.5.5 0 0 1 .707 0l1.293 1.293zm-1.75 2.456-2-2L4.939 9.21a.5.5 0 0 0-.121.196l-.805 2.414a.25.25 0 0 0 .316.316l2.414-.805a.5.5 0 0 0 .196-.12l6.813-6.814z"/>
                        <path fill-rule="evenodd" d="M1 13.5A1.5 1.5 0 0 0 2.5 15h11a1.5 1.5 0 0 0 1.5-1.5v-6a.5.5 0 0 0-1 0v6a.5.5 0 0 1-.5.5h-11a.5.5 0 0 1-.5-.5v-11a.5.5 0 0 1 .5-.5H9a.5.5 0 0 0 0-1H2.5A1.5 1.5 0 0 0 1 2.5z"/>
                      </svg>
                    </button>

                    <!-- Edit Modal -->
                    <div class="modal fade"  style="text-align: left;" id="editModal<?php echo $email['id']; ?>" tabindex="-1" role="dialog" aria-labelledby="editModalLabel" aria-hidden="true">
                        <div class="modal-dialog" role="document">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="editModalLabel">Edit Email</h5>
                                    <button type="button" class="btn-close" data-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <form method="post">
                                    <div class="modal-body">
                                        <input type="hidden" name="id" value="<?php echo $email['id']; ?>">
                                        <input type="hidden" name="domainID" value="<?php echo $email['domain_id']; ?>">
                                        <div class="form-group">
                                            <label for="editEmail">Email</label>
                                            <input type="email" class="form-control" name="email"  autocomplete="off"  readonly value="<?php echo htmlspecialchars($email['email']); ?>" required>
                                        </div> 
                                        <div class="form-group">
                                            <label for="editPassword">Password</label>
                                            <input type="password" class="form-control" name="password" autocomplete="off">
                                        </div>
                                        <div class="form-group">
                                            <label>Forward to</label>
                                            <input type="email" class="form-control" name="forward"  autocomplete="off" value="<?php echo htmlspecialchars($email['destination']??''); ?>" >
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                        <button type="submit" name="edit" class="btn btn-primary">Save changes</button>
                                    </div>
                                </form> 
                            </div>
                        </div>
                    </div>

                    <form method="post" class="d-inline" onsubmit="return confirm('Are you sure you want to delete: <?php echo htmlspecialchars($email['email']); ?>?') ? true : false;">
                        <input type="hidden" name="id" value="<?php echo $email['id']; ?>">
                        <input type="hidden" name="email" value="<?php echo $email['email']; ?>">
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
   
  <nav aria-label="Page navigation">
    <ul class="pagination">
        <?php if ($currentPage > 1): ?>
            <li class="page-item">
                <a class="page-link" href="/?page=<?php echo $currentPage - 1; ?>" aria-label="Previous">
                    <span aria-hidden="true">&laquo;</span>
                </a>
            </li>
        <?php endif; ?>

        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
            <li class="page-item <?php echo $i == $currentPage ? 'active' : ''; ?>">
                <a class="page-link" href="/?page=<?php echo $i; ?>"><?php echo $i; ?></a>
            </li>
        <?php endfor; ?>

        <?php if ($currentPage < $totalPages): ?>
            <li class="page-item">
                <a class="page-link" href="/?page=<?php echo $currentPage + 1; ?>" aria-label="Next">
                    <span aria-hidden="true">&raquo;</span>
                </a>
            </li>
        <?php endif; ?>
    </ul>
</nav>
</div>

<footer class="my-5 pt-5 text-body-secondary text-center text-small">
        <p class="mb-1">&copy; <?php echo date("Y");?> Postfix Email Manager | <a href="https://simplessh.com" target="_blank">simplessh.com</a></p>
</footer>