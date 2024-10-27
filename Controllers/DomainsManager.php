<?php 
class DomainsManager {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function getAllDomains() {
        $stmt = $this->pdo->query('SELECT * FROM  virtual_domains');
        return $stmt->fetchAll();
    }

    public function addDomain($host="", $user="", $password="", $domain="", $privateKeyString="") {
        $stmt    = $this->pdo->prepare('INSERT INTO virtual_domains (name) VALUES (?)');
        $execute = $stmt->execute([$domain]);
        
        if(!empty($host)&& !empty($user)){
         $this->executeScript($domain, $host, $user, $password, $privateKeyString );
        }
        
        return $execute;
    }
 
    public function deleteDomain($id) {
        $stmt = $this->pdo->prepare('DELETE FROM virtual_domains WHERE id = ?');
        return $stmt->execute([$id]);
    }
    
    private function executeScript($domain, $host, $username, $password, $privateKeyString = "") {
        
         // if it's pem file
         if(!empty($privateKeyString) && empty($password)){  
            // Create a temporary file for the PEM key
            $tempKeyFile = tempnam(sys_get_temp_dir(), 'ssh_key');
            file_put_contents($tempKeyFile, $privateKeyString);
            chmod($tempKeyFile, 0400); // Set permissions
         }
        
        $connection = ssh2_connect($host);

        if (!$connection) {
            die('Connection failed');
        }
 
        if(!empty($privateKeyString) && empty($password)){
            // Authenticate using the temporary PEM file
           if (!ssh2_auth_pubkey_file($connection, $username, $tempKeyFile, $tempKeyFile, '')) {
             unlink($tempKeyFile); // Clean up the temp file
             die('Public Key Authentication Failed');
           }
        }else{
            // Authenticate using password (or use public key authentication)
           if (!ssh2_auth_password($connection, $username, $password)) {
               die('Authentication failed');
           }
        }
         
       // For DKIM
        $domainLine = ssh2_exec($connection, "echo $password | sudo -S sed -n '/^Domain/p' /etc/opendkim.conf");
        stream_set_blocking($domainLine, true);
        $currentDomainLine = stream_get_contents($domainLine);
        fclose($domainLine);

        if (!empty($currentDomainLine) && strpos($currentDomainLine, $domain) === false) {
            $currentDomainLine = str_replace("Domain", "", $currentDomainLine);
            $currentDomainLine = preg_replace('/\s+/', '', $currentDomainLine);

            // Split and filter the domain line
            $domainsArray = array_filter(array_map('trim', explode(',', $currentDomainLine)));
            $domainsArray[] = $domain;
            $str = implode(', ', $domainsArray);

            // Update the configuration file
            $command = "sed -i '/^Domain/s/.*/Domain                  $str/' /etc/opendkim.conf";
            ssh2_exec($connection, "echo $password | sudo -S ".$command);
        } elseif (empty($currentDomainLine)) {
            // Insert the new domain if no domain line is present
            $command = "sed -i '22iDomain                  $domain' /etc/opendkim.conf";
            ssh2_exec($connection, "echo $password | sudo -S ".$command);
        }
        
        // For postsrsd
        $postsrsdLine = ssh2_exec($connection, "echo $password | sudo -S sed -n '/^SRS_DOMAIN/p' /etc/default/postsrsd");
        stream_set_blocking($postsrsdLine, true);
        $currentDomainLine = stream_get_contents($postsrsdLine);
        fclose($postsrsdLine); 
        
        if (!empty($currentDomainLine) && strpos($currentDomainLine, $domain) === false) {
            $currentDomainLine = str_replace("SRS_DOMAIN", "", $currentDomainLine);
            $currentDomainLine = str_replace("=", "", $currentDomainLine);
            $currentDomainLine = preg_replace('/\s+/', '', $currentDomainLine); 
             

            // Split and filter the domain line
            $domainsArray = array_filter(array_map('trim', explode(',', $currentDomainLine)));
            $domainsArray[] = $domain;
            $str = implode(', ', $domainsArray);
            
            // Update the configuration file
            $command = "sed -i '/^SRS_DOMAIN/s/.*/SRS_DOMAIN=".$str."/' /etc/default/postsrsd";
            ssh2_exec($connection, "echo $password | sudo -S ".$command);
        }else if(empty($currentDomainLine)){
            $command = "sed -i '22iSRS_DOMAIN=".$domain."' /etc/default/postsrsd";
            ssh2_exec($connection, "echo $password | sudo -S ".$command);
        }
        
        
        
        // Close the connection
        ssh2_disconnect($connection);
       
        if(!empty($privateKeyString) && empty($password))
         unlink($tempKeyFile); // Remove the temporary PEM file
        
    }
 
    
}
