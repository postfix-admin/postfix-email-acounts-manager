<?php
class UserManager {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function validateUser($username, $password) {
        $stmt = $this->pdo->prepare('SELECT password FROM virtual_admins WHERE email = ?');
        $stmt->execute([$username]);
        $user = $stmt->fetch();
        //use this to store password :  password_hash($password, PASSWORD_DEFAULT);
        $valid = $user && password_verify($password, $user['password']);
        
        if($valid){
            $token = $this->generateRandomString(80);
            $_SESSION['loggedin'] = $token;
            $sql = $this->pdo->prepare('UPDATE virtual_admins SET login_token = ? WHERE email = ?');
            $sql->execute([$token, $username]);
            return true;
        }
        return false;
    }
    
    public function newInstalation() {
       $stmt = $this->pdo->prepare('SELECT COUNT(*) FROM virtual_admins');
       $stmt->execute([]);
       return $stmt->fetchColumn() > 0;  
    }
    

    public function isLoggedIn() {
        $loginToken = (string)($_SESSION['loggedin'] ?? "");
        if(!empty($loginToken) && strlen($loginToken)>70){
            $stmt = $this->pdo->prepare('SELECT COUNT(*) FROM virtual_admins WHERE login_token = ?');
            $stmt->execute([$loginToken]);
            return $stmt->fetchColumn() > 0;
        }
        
        return false;  
    }
    
    public function changePassword($id, $email, $password) {
        $sql = $this->pdo->prepare('UPDATE virtual_admins SET email = ?, password = ? WHERE id = ?');
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT); // Encrypt the password
        return $sql->execute([$email, $hashedPassword, $id]);
    }
    
    public function createNewUser($email, $password) {
       if(!$this->newInstalation()){
            $sql = $this->pdo->prepare('INSERT INTO virtual_admins (email, password) VALUES (?, ?)');
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT); // Encrypt the password
            return $sql->execute([$email, $hashedPassword]);
       }else{
            return false;   
       }
       
    }
    
    public function getAllUsers() {
        $stmt = $this->pdo->query('SELECT id,email FROM virtual_admins');
        return $stmt->fetchAll();
    }
    
    
    public function resetPasswordRequest($email) {
    // Check if the email exists
        $stmt = $this->pdo->prepare('SELECT COUNT(*) FROM virtual_admins WHERE email = ?');
        $stmt->execute([$email]);
        $emailExists = $stmt->fetchColumn() > 0;

        if ($emailExists) {    
          $token = $this->generateRandomString(70);  
          $sql   = $this->pdo->prepare('UPDATE virtual_admins SET reset_token = ? WHERE email = ?');
          $this->sendMail($email, $token);

          return $sql->execute([$token, $email]);
        }else {
          return false;
        }  
    }
    
    public function checkResetToken($token) {
       // Check if the reset_token exists
        $stmt = $this->pdo->prepare('SELECT COUNT(*) FROM virtual_admins WHERE reset_token = ?');
        $stmt->execute([$token]);
        return $stmt->fetchColumn() > 0; 
    }
    
    public function updatePassword($token, $password) {
      $sql = $this->pdo->prepare('UPDATE virtual_admins SET password = ? WHERE reset_token = ?');
      $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
      return $sql->execute([$hashedPassword, $token]);
    }
    
    private function sendMail($to, $token) {
        $hostName = filter_input(INPUT_SERVER, 'HTTP_HOST', FILTER_SANITIZE_STRING);
        $url     = "http://".$hostName."/?reset_token=".$token;
        $subject = 'Requested password reset';
        $message = '<p>
                      <span style="font-size: 18px;">You are receiving this email because we received a password reset request for your account.
                      Please click on the link below to reset your password.</span>
                    </p>
                    <p>&nbsp;'.$url.'</p>
                    <p>
                      <span style="font-size: 18px;">No further action is required if you did not request a password reset.</span>
                    </p>';
        
        $headers = 'From: password@'.$hostName. "\r\n" .
                   'X-Mailer: PHP/' . phpversion();
        $headers .= "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: text/html; charset=UTF-8\r\n";    

        mail($to, $subject, $message, $headers);
    }
    
    public function generateRandomString($length = 60) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';

        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }

        return $randomString;
    }
    
}
