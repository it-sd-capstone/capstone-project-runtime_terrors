<?php
class User {
    private $db;
    
    public function __construct($db) {
        $this->db = $db;
    }
    
    /**
     * Authenticate a user with secure password verification
     */
    public function authenticate($email, $password) {
        try {
            if ($this->db instanceof mysqli) {
                // MySQLi implementation
                $query = "SELECT user_id, email, password_hash, first_name, last_name, role, password_change_required
                        FROM users
                        WHERE email = ? AND is_active = 1";
                
                $stmt = $this->db->prepare($query);
                $stmt->bind_param("s", $email);
                $stmt->execute();
                
                $result = $stmt->get_result();
                $user = $result->fetch_assoc();
                error_log("User query result: " . ($user ? "User found" : "No user found"));
                if ($user) {
                    error_log("Password verification: " . (password_verify($password, $user['password_hash']) ? "Success" : "Failed"));
                }
                
            } elseif ($this->db instanceof PDO) {
                // PDO implementation
                $query = "SELECT user_id, email, password_hash, first_name, last_name, role, password_change_required
                        FROM users
                        WHERE email = :email AND is_active = 1";
                
                $stmt = $this->db->prepare($query);
                $stmt->bindParam(':email', $email, PDO::PARAM_STR);
                $stmt->execute();
                
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
            } else {
                throw new Exception("Unsupported database connection type");
            }
            
            // Add your debug log separately before the conditional
            if ($user) {
                error_log("Attempting to verify password: '" . substr($password, 0, 3) . "***' against hash: '" . $user['password_hash'] . "'");
            }
            
            // Secure password verification using PHP's built-in function
            if ($user && password_verify($password, $user['password_hash'])) {
                // Check if password needs rehashing (if PHP's default algorithm has been updated)
                if (password_needs_rehash($user['password_hash'], PASSWORD_DEFAULT)) {
                    $this->updatePasswordHash($user['user_id'], $password);
                }
                
                // Update last login time
                $this->updateLastLogin($user['user_id']);
                
                return $user;
            }
            
            return false;
            
        } catch (Exception $e) {
            error_log("Authentication error: " . $e->getMessage());
            throw $e;
        }
    }
    
    public function toggleStatus($userId) {
        $sql = "UPDATE users SET is_active = NOT is_active WHERE user_id = ?";
        $stmt = $this->db->prepare($sql);
        
        if (!$stmt) {
            error_log("Prepare failed: " . $this->db->error);
            return false;
        }
        
        $stmt->bind_param('i', $userId);
        $result = $stmt->execute();
        
        return $result && $stmt->affected_rows > 0;
    }
    
    /**
     * Update the password hash if the algorithm has changed
     */
    private function updatePasswordHash($userId, $password) {
        $newHash = password_hash($password, PASSWORD_DEFAULT);
        try {
            if ($this->db instanceof mysqli) {
                $stmt = $this->db->prepare("UPDATE users SET password_hash = ? WHERE user_id = ?");
                $stmt->bind_param("si", $newHash, $userId);
                $stmt->execute();
            } elseif ($this->db instanceof PDO) {
                $stmt = $this->db->prepare("UPDATE users SET password_hash = :hash WHERE user_id = :id");
                $stmt->bindParam(':hash', $newHash, PDO::PARAM_STR);
                $stmt->bindParam(':id', $userId, PDO::PARAM_INT);
                $stmt->execute();
            }
        } catch (Exception $e) {
            error_log("Failed to update password hash: " . $e->getMessage());
        }
    }
    
    /**
     * Update last login timestamp
     */
    private function updateLastLogin($userId) {
        try {
            if ($this->db instanceof mysqli) {
                $stmt = $this->db->prepare("UPDATE users SET last_login = NOW() WHERE user_id = ?");
                $stmt->bind_param("i", $userId);
                $stmt->execute();
            } elseif ($this->db instanceof PDO) {
                $stmt = $this->db->prepare("UPDATE users SET last_login = NOW() WHERE user_id = :id");
                $stmt->bindParam(':id', $userId, PDO::PARAM_INT);
                $stmt->execute();
            }
        } catch (Exception $e) {
            error_log("Failed to update last login: " . $e->getMessage());
        }
    }
    
    /**
     * Register a new user with secure password hashing and validation
     */
    public function register($email, $password, $firstName, $lastName, $phone, $role = 'patient') {
        try {
            // Validate email format
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                return ['error' => 'Invalid email format'];
            }
            
            // Check if email already exists
            if ($this->emailExists($email)) {
                return ['error' => 'Email already registered'];
            }
            
            // Validate password strength
            $passwordValidation = $this->validatePasswordStrength($password);
            if ($passwordValidation !== true) {
                return ['error' => $passwordValidation];
            }
            
            // Use already hashed password
            $passwordHash = $password;
            
            if ($this->db instanceof mysqli) {
                // Start transaction
                $this->db->begin_transaction();
                
                // MySQLi implementation
                $query = "INSERT INTO users (email, password_hash, first_name, last_name, phone, role)
                          VALUES (?, ?, ?, ?, ?, ?)";
                
                $stmt = $this->db->prepare($query);
                $stmt->bind_param("ssssss", $email, $passwordHash, $firstName, $lastName, $phone, $role);
                $stmt->execute();
                
                $userId = $stmt->insert_id;
                
                // Create appropriate profile based on role
                if ($role === 'patient') {
                    $profileStmt = $this->db->prepare("INSERT INTO patient_profiles (patient_id) VALUES (?)");
                    $profileStmt->bind_param("i", $userId);
                    $profileStmt->execute();
                } elseif ($role === 'provider') {
                    $profileStmt = $this->db->prepare("INSERT INTO provider_profiles (provider_id) VALUES (?)");
                    $profileStmt->bind_param("i", $userId);
                    $profileStmt->execute();
                }
                
                $this->db->commit();
                return ['user_id' => $userId];
                
            } elseif ($this->db instanceof PDO) {
                // Start transaction
                $this->db->beginTransaction();
                
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
                
                $userId = $this->db->lastInsertId();
                
                // Create appropriate profile based on role
                if ($role === 'patient') {
                    $profileStmt = $this->db->prepare("INSERT INTO patient_profiles (patient_id) VALUES (:id)");
                    $profileStmt->bindParam(':id', $userId, PDO::PARAM_INT);
                    $profileStmt->execute();
                } elseif ($role === 'provider') {
                    $profileStmt = $this->db->prepare("INSERT INTO provider_profiles (provider_id) VALUES (:id)");
                    $profileStmt->bindParam(':id', $userId, PDO::PARAM_INT);
                    $profileStmt->execute();
                }
                
                $this->db->commit();
                return ['user_id' => $userId];
            } else {
                throw new Exception("Unsupported database connection type");
            }
            
        } catch (Exception $e) {
            // Rollback transaction on error
            if ($this->db instanceof mysqli) {
                $this->db->rollback();
            } elseif ($this->db instanceof PDO) {
                $this->db->rollBack();
            }
            
            error_log("Registration error: " . $e->getMessage());
            return ['error' => 'Registration failed: ' . $e->getMessage()];
        }
    }
    
    /**
     * Validate password strength
     */
    public function validatePasswordStrength($password) {
        // Check minimum length
        if (strlen($password) < 8) {
            return 'Password must be at least 8 characters long';
        }
        
        // Check for at least one uppercase letter
        if (!preg_match('/[A-Z]/', $password)) {
            return 'Password must contain at least one uppercase letter';
        }
        
        // Check for at least one lowercase letter
        if (!preg_match('/[a-z]/', $password)) {
            return 'Password must contain at least one lowercase letter';
        }
        
        // Check for at least one number
        if (!preg_match('/\d/', $password)) {
            return 'Password must contain at least one number';
        }
        
        // Check for at least one special character
        if (!preg_match('/[^A-Za-z0-9]/', $password)) {
            return 'Password must contain at least one special character';
        }
        
        return true;
    }
    
    /**
     * Check if email already exists
     */
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
    
    /**
     * Generate password reset token
     */
    public function requestPasswordReset($email) {
        if (!$this->emailExists($email)) {
            return false;
        }
        
        try {
            // Generate secure random token
            $token = bin2hex(random_bytes(32));
            $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
            
            if ($this->db instanceof mysqli) {
                $stmt = $this->db->prepare("
                    UPDATE users 
                    SET reset_token = ?, reset_token_expires = ? 
                    WHERE email = ?
                ");
                $stmt->bind_param("sss", $token, $expires, $email);
                $stmt->execute();
            } elseif ($this->db instanceof PDO) {
                $stmt = $this->db->prepare("
                    UPDATE users 
                    SET reset_token = :token, reset_token_expires = :expires 
                    WHERE email = :email
                ");
                $stmt->bindParam(':token', $token, PDO::PARAM_STR);
                $stmt->bindParam(':expires', $expires, PDO::PARAM_STR);
                $stmt->bindParam(':email', $email, PDO::PARAM_STR);
                $stmt->execute();
            }
            
            return ['token' => $token, 'expires' => $expires];
            
        } catch (Exception $e) {
            error_log("Password reset request error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Reset password using token
     */
    public function resetPassword($token, $newPassword) {
        // Validate password strength
        $passwordValidation = $this->validatePasswordStrength($newPassword);
        if ($passwordValidation !== true) {
            return ['error' => $passwordValidation];
        }
        
        try {
            // Verify token is valid and not expired
            if ($this->db instanceof mysqli) {
                $stmt = $this->db->prepare("
                    SELECT user_id 
                    FROM users 
                    WHERE reset_token = ? AND reset_token_expires > NOW()
                ");
                $stmt->bind_param("s", $token);
                $stmt->execute();
                $result = $stmt->get_result();
                $user = $result->fetch_assoc();
            } elseif ($this->db instanceof PDO) {
                $stmt = $this->db->prepare("
                    SELECT user_id 
                    FROM users 
                    WHERE reset_token = :token AND reset_token_expires > NOW()
                ");
                $stmt->bindParam(':token', $token, PDO::PARAM_STR);
                $stmt->execute();
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
            } else {
                throw new Exception("Unsupported database connection type");
            }
            
            if (!$user) {
                return ['error' => 'Invalid or expired reset token'];
            }
            
            // Hash new password
            $passwordHash = password_hash($newPassword, PASSWORD_DEFAULT);
            
            // Update password and clear reset token
            if ($this->db instanceof mysqli) {
                $stmt = $this->db->prepare("
                    UPDATE users 
                    SET password_hash = ?, reset_token = NULL, reset_token_expires = NULL 
                    WHERE user_id = ?
                ");
                $stmt->bind_param("si", $passwordHash, $user['user_id']);
                $stmt->execute();
            } elseif ($this->db instanceof PDO) {
                $stmt = $this->db->prepare("
                    UPDATE users 
                    SET password_hash = :hash, reset_token = NULL, reset_token_expires = NULL 
                    WHERE user_id = :id
                ");
                $stmt->bindParam(':hash', $passwordHash, PDO::PARAM_STR);
                $stmt->bindParam(':id', $user['user_id'], PDO::PARAM_INT);
                $stmt->execute();
            }
            
            return true;
            
        } catch (Exception $e) {
            error_log("Password reset error: " . $e->getMessage());
            return ['error' => 'Password reset failed: ' . $e->getMessage()];
        }
    }
    
    /**
     * Generate verification token for email verification
     */
    public function generateVerificationToken($userId) {
        try {
            // Generate secure random token
            $token = bin2hex(random_bytes(32));
            
            if ($this->db instanceof mysqli) {
                $stmt = $this->db->prepare("
                    UPDATE users 
                    SET verification_token = ? 
                    WHERE user_id = ?
                ");
                $stmt->bind_param("si", $token, $userId);
                $stmt->execute();
            } elseif ($this->db instanceof PDO) {
                $stmt = $this->db->prepare("
                    UPDATE users 
                    SET verification_token = :token 
                    WHERE user_id = :id
                ");
                $stmt->bindParam(':token', $token, PDO::PARAM_STR);
                $stmt->bindParam(':id', $userId, PDO::PARAM_INT);
                $stmt->execute();
            }
            
            return $token;
            
        } catch (Exception $e) {
            error_log("Verification token generation error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Verify email with verification token
     */
    public function verifyEmail($token) {
        try {
            if ($this->db instanceof mysqli) {
                $stmt = $this->db->prepare("
                    UPDATE users 
                    SET email_verified_at = NOW(), verification_token = NULL 
                    WHERE verification_token = ?
                ");
                $stmt->bind_param("s", $token);
                $stmt->execute();
                
                return $stmt->affected_rows > 0;
            } elseif ($this->db instanceof PDO) {
                $stmt = $this->db->prepare("
                    UPDATE users 
                    SET email_verified_at = NOW(), verification_token = NULL 
                    WHERE verification_token = :token
                ");
                $stmt->bindParam(':token', $token, PDO::PARAM_STR);
                $stmt->execute();
                
                return $stmt->rowCount() > 0;
            }
            
            return false;
            
        } catch (Exception $e) {
            error_log("Email verification error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get user by ID
     */
    public function getUserById($userId) {
        try {
            if ($this->db instanceof mysqli) {
                $stmt = $this->db->prepare("
                    SELECT user_id, email, first_name, last_name, phone, role, is_active, 
                           email_verified_at, created_at, last_login
                    FROM users 
                    WHERE user_id = ?
                ");
                $stmt->bind_param("i", $userId);
                $stmt->execute();
                
                $result = $stmt->get_result();
                return $result->fetch_assoc();
            } elseif ($this->db instanceof PDO) {
                $stmt = $this->db->prepare("
                    SELECT user_id, email, first_name, last_name, phone, role, is_active, 
                           email_verified_at, created_at, last_login
                    FROM users 
                    WHERE user_id = :id
                ");
                $stmt->bindParam(':id', $userId, PDO::PARAM_INT);
                $stmt->execute();
                
                return $stmt->fetch(PDO::FETCH_ASSOC);
            }
            
            return null;
            
        } catch (Exception $e) {
            error_log("Get user error: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Update user account information
     */
    public function updateUser($userId, $userData) {
        try {
            $updateFields = [];
            $params = [];
            $types = "";
            
            // Fields that can be updated
            $allowedFields = [
                'first_name' => 's',
                'last_name' => 's',
                'phone' => 's',
                'is_active' => 'i'
            ];
            
            // Build update query dynamically
            foreach ($allowedFields as $field => $type) {
                if (isset($userData[$field])) {
                    $updateFields[] = "$field = ?";
                    $params[] = $userData[$field];
                    $types .= $type;
                }
            }
            
            if (empty($updateFields)) {
                return ['error' => 'No valid fields to update'];
            }
            
            if ($this->db instanceof mysqli) {
                $query = "UPDATE users SET " . implode(', ', $updateFields) . " WHERE user_id = ?";
                $stmt = $this->db->prepare($query);
                
                // Add user_id to params
                $params[] = $userId;
                $types .= "i";
                
                $stmt->bind_param($types, ...$params);
                $stmt->execute();
                
                return $stmt->affected_rows > 0;
            } elseif ($this->db instanceof PDO) {
                $query = "UPDATE users SET " . implode(', ', $updateFields) . " WHERE user_id = :user_id";
                $stmt = $this->db->prepare($query);
                
                // Bind parameters dynamically
                foreach ($params as $i => $param) {
                    $paramName = ':param' . $i;
                    $stmt->bindValue($paramName, $param);
                    $updateFields[$i] = str_replace('?', $paramName, $updateFields[$i]);
                }
                
                $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
                $stmt->execute();
                
                return $stmt->rowCount() > 0;
            }
            
            return ['error' => 'Unsupported database connection type'];
            
        } catch (Exception $e) {
            error_log("Update user error: " . $e->getMessage());
            return ['error' => 'Update failed: ' . $e->getMessage()];
        }
    }

    public function getAllUsersWithFilters($whereClause = "", $params = []) {
        $sql = "SELECT * FROM users $whereClause ORDER BY user_id DESC";
        
        if (empty($params)) {
            $stmt = $this->db->query($sql);
            return $stmt->fetch_all(MYSQLI_ASSOC);
        } else {
            $stmt = $this->db->prepare($sql);
            if (!$stmt) {
                error_log("Prepare failed: " . $this->db->error);
                return [];
            }
            
            // Create binding parameters
            $types = str_repeat('s', count($params)); // Assume all strings for simplicity
            
            $stmt->bind_param($types, ...$params);
            $stmt->execute();
            
            $result = $stmt->get_result();
            return $result->fetch_all(MYSQLI_ASSOC);
        }
    }
    /**
     * Change user password
     */
    public function changePassword($userId, $currentPassword, $newPassword) {
        try {
            // First verify current password
            if ($this->db instanceof mysqli) {
                $stmt = $this->db->prepare("
                    SELECT password_hash 
                    FROM users 
                    WHERE user_id = ?
                ");
                $stmt->bind_param("i", $userId);
                $stmt->execute();
                
                $result = $stmt->get_result();
                $user = $result->fetch_assoc();
            } elseif ($this->db instanceof PDO) {
                $stmt = $this->db->prepare("
                    SELECT password_hash 
                    FROM users 
                    WHERE user_id = :id
                ");
                $stmt->bindParam(':id', $userId, PDO::PARAM_INT);
                $stmt->execute();
                
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
            } else {
                throw new Exception("Unsupported database connection type");
            }
            
            if (!$user || !password_verify($currentPassword, $user['password_hash'])) {
                return ['error' => 'Current password is incorrect'];
            }
            
            // Validate new password
            $passwordValidation = $this->validatePasswordStrength($newPassword);
            if ($passwordValidation !== true) {
                return ['error' => $passwordValidation];
            }
            
            // Hash and update new password
            $newHash = password_hash($newPassword, PASSWORD_DEFAULT);
            
            if ($this->db instanceof mysqli) {
                $stmt = $this->db->prepare("
                    UPDATE users 
                    SET password_hash = ? 
                    WHERE user_id = ?
                ");
                $stmt->bind_param("si", $newHash, $userId);
                $stmt->execute();
            } elseif ($this->db instanceof PDO) {
                $stmt = $this->db->prepare("
                    UPDATE users 
                    SET password_hash = :hash 
                    WHERE user_id = :id
                ");
                $stmt->bindParam(':hash', $newHash, PDO::PARAM_STR);
                $stmt->bindParam(':id', $userId, PDO::PARAM_INT);
                $stmt->execute();
            }
            
            return true;
            
        } catch (Exception $e) {
            error_log("Change password error: " . $e->getMessage());
            return ['error' => 'Password change failed: ' . $e->getMessage()];
        }
    }
}
?>