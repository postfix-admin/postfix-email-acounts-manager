<?php
class EmailManager {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function getAllDomains() {
        $stmt = $this->pdo->query('SELECT * FROM  virtual_domains ORDER BY id DESC');
        return $stmt->fetchAll();
    }
    
    public function getAllEmails($currentPage = 1, $itemsPerPage = 10) {
        // Calculate the offset
        $offset = ($currentPage - 1) * $itemsPerPage;

        // Prepare the SQL query with pagination
        $stmt = $this->pdo->prepare('
            SELECT vu.*, va.id as idSource, va.destination 
            FROM virtual_users AS vu 
            LEFT JOIN virtual_aliases AS va ON vu.email = va.source 
            ORDER BY vu.id DESC 
            LIMIT :limit OFFSET :offset
        ');

        // Bind parameters
        $stmt->bindValue(':limit', $itemsPerPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);

        // Execute the statement
        $stmt->execute();

        return $stmt->fetchAll();
    }
    
    public function getTotalEmailsCount() {
        $stmt = $this->pdo->query('SELECT COUNT(*) FROM virtual_users');
        return $stmt->fetchColumn();
    }

    public function addEmail($idDomain, $email, $password, $forward = "") {
        $stmt = $this->pdo->prepare('INSERT INTO virtual_users (domain_id, email, password) VALUES (?, ?, ?)');
        $salt = "$6$" . $this->getAlphaNumericString(16) . "$"; // Generate the salt
        $hashedPassword = crypt($password, $salt); // Encrypt the password
        
        $fullEmail = $email."@".$this->getDomainNameByID($idDomain);
        $stmt->execute([$idDomain, $fullEmail, $hashedPassword]);
         
        if(!empty($forward)){
          $stmt = $this->pdo->prepare('INSERT INTO virtual_aliases (domain_id, source, destination) VALUES (?, ?, ?)');
          $stmt->execute([$idDomain, $fullEmail, $forward]);   
        }
    }

    public function updateEmail($id, $domainID, $email, $password="", $forward = "") {
        if(!empty($password)){ 
          $stmt = $this->pdo->prepare('UPDATE virtual_users SET password = ? WHERE id = ?');
          $salt = "$6$" . $this->getAlphaNumericString(16) . "$"; // Generate the salt
          $hashedPassword = crypt($password, $salt); // Encrypt the password
          $stmt->execute([$hashedPassword, $id]);
        }
        
        if(empty($forward)){
          $forwardDel = $this->pdo->prepare('DELETE FROM virtual_aliases WHERE source = ?');
          $forwardDel->execute([$email]);  
        }else{
          $forwardGet = $this->pdo->prepare('SELECT * FROM virtual_aliases WHERE source = ?');
          $forwardGet->execute([$email]);
          if($forwardGet->fetchColumn() > 0){
              $forwardUpd = $this->pdo->prepare('UPDATE virtual_aliases SET destination = ?  WHERE source = ?');
              $forwardUpd->execute([$forward, $email]);
          }else{
              $forwardIns = $this->pdo->prepare('INSERT INTO virtual_aliases (domain_id, source, destination) VALUES (?, ?, ?)');
              $forwardIns->execute([$domainID, $email, $forward]);   
          }
        }
        
    }

    public function deleteEmail($id, $email) {
        $user = $this->pdo->prepare('DELETE FROM virtual_users WHERE id = ?');
        $user->execute([$id]);
        
        $forward = $this->pdo->prepare('DELETE FROM virtual_aliases WHERE source = ?');
        $forward->execute([$email]);
    }

    public function getEmailById($id) {
        $stmt = $this->pdo->prepare('SELECT * FROM virtual_users WHERE id = ?');
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    
    private function getDomainNameByID($domainID = 0) {
        $stmtDomain = $this->pdo->prepare('SELECT name FROM virtual_domains WHERE id = ?');
        $stmtDomain->execute([$domainID]);
        $domain = $stmtDomain->fetch();
        return $domain['name'];
    }
 
    private function getAlphaNumericString($length) {
        // Generate a random alphanumeric string of the specified length
        return bin2hex(random_bytes($length / 2));
    }
    
     
    
}
