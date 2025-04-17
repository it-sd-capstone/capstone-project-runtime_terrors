<?php
class User {
    private $db;
    
    public function __construct($db) {
        $this->db = $db;
    }
    
    public function authenticate($email, $password) {
        try {
            if ($this->db instanceof mysqli) {
                // MySQLi implementation
                $query = "SELECT user_id, email, password_hash, first_name, last_name, role 
                          FROM users 
                          WHERE email = ? AND is_active = 1";
                
                $stmt = $this->db->prepare($query);
                $stmt->bind_param("s", $email);
                $stmt->execute();
                
                $result = $stmt->get_result();
                $user = $result->fetch_assoc();
                
            } elseif ($this->db instanceof PDO) {
                // PDO implementation
                $query = "SELECT user_id, email, password_hash, first_name, last_name, role 
                          FROM users 
                          WHERE email = :email AND is_active = 1";
                
                $stmt = $this->db->prepare($query);
                $stmt->bindParam(':email', $email, PDO::PARAM_STR);
                $stmt->execute();
                
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
            } else {
                throw new Exception("Unsupported database connection type");
            }
            
            // For demonstration/testing purposes
            // In production, always use password_verify with hashed passwords
            if ($user) {
                // First try with password_verify (secure approach)
                if (password_verify($password, $user['password_hash'])) {
                    return $user;
                }
                
                // For development: accept 'test123' for any user
                // REMOVE THIS IN PRODUCTION!
                if ($password === 'test123') {
                    return $user;
                }
            }
            
            return false;
            
        } catch (Exception $e) {
            error_log("Authentication error: " . $e->getMessage());
            throw $e;
        }
    }
    
    public function register($email, $password, $firstName, $lastName, $phone, $role = 'patient') {
        try {
            // Hash the password
            $passwordHash = password_hash($password, PASSWORD_DEFAULT);
            
            if ($this->db instanceof mysqli) {
                // MySQLi implementation
                $query = "INSERT INTO users (email, password_hash, first_name, last_name, phone, role) 
                          VALUES (?, ?, ?, ?, ?, ?)";
                
                $stmt = $this->db->prepare($query);
                $stmt->bind_param("ssssss", $email, $passwordHash, $firstName, $lastName, $phone, $role);
                $stmt->execute();
                
                return $stmt->insert_id;
                
            } elseif ($this->db instanceof PDO) {
                // PDO implementation
                $query = "INSERT INTO users (email, password_hash, first_name, last_name, phone, role) 
                          VALUES (:email, :password_hash, :first_name, :last_name, :phone, :role)";
                
                $stmt = $this->db->prepare($query);
                $stmt->bindParam(':email', $email, PDO::PARAM_STR);
                $stmt->bindParam(':password_hash', $passwordHash, PDO::PARAM_STR);
                $stmt->bindParam(':first_name', $firstName, PDO::PARAM_STR);
                $stmt->bindParam(':last_name', $lastName, PDO::PARAM_STR);
                $stmt->bindParam(':phone', $phone, PDO::PARAM_STR);
                $stmt->bindParam(':role', $role, PDO::PARAM_STR);
                $stmt->execute();
                
                return $this->db->lastInsertId();
            } else {
                throw new Exception("Unsupported database connection type");
            }
            
        } catch (Exception $e) {
            error_log("Registration error: " . $e->getMessage());
            throw $e;
        }
    }
    
    public function emailExists($email) {
        try {
            if ($this->db instanceof mysqli) {
                // MySQLi implementation
                $query = "SELECT COUNT(*) as count FROM users WHERE email = ?";
                
                $stmt = $this->db->prepare($query);
                $stmt->bind_param("s", $email);
                $stmt->execute();
                
                $result = $stmt->get_result();
                $row = $result->fetch_assoc();
                
                return $row['count'] > 0;
                
            } elseif ($this->db instanceof PDO) {
                // PDO implementation
                $query = "SELECT COUNT(*) as count FROM users WHERE email = :email";
                
                $stmt = $this->db->prepare($query);
                $stmt->bindParam(':email', $email, PDO::PARAM_STR);
                $stmt->execute();
                
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                return $row['count'] > 0;
            } else {
                throw new Exception("Unsupported database connection type");
            }
            
        } catch (Exception $e) {
            error_log("Email check error: " . $e->getMessage());
            throw $e;
        }
    }
}
?>
