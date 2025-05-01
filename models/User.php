<?php
/**
 * User Model
 * 
 * Handles user authentication, registration, and profile management
 */
class User {
    private $db;
    
    /**
     * Constructor - initialize with database connection
     * 
     * @param mysqli|PDO $db Database connection
     */
    public function __construct($db) {
        $this->db = $db;
    }
    
    /**
     * Authenticate a user with secure password verification
     * 
     * @param string $email User email
     * @param string $password User password (plain text)
     * @return array|bool User data if authenticated, false otherwise
     */
    public function authenticate($email, $password) {
        try {
            if ($this->db instanceof mysqli) {
                // MySQLi implementation
                $query = "SELECT user_id, email, password_hash, first_name, last_name, role, 
                        password_change_required, email_verified_at, is_verified 
                        FROM users WHERE email = ? AND is_active = 1";
                
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
                $query = "SELECT user_id, email, password_hash, first_name, last_name, role, 
                password_change_required, email_verified_at, is_verified
                FROM users
                WHERE email = :email AND is_active = 1";
                
                $stmt = $this->db->prepare($query);
                $stmt->bindParam(':email', $email, PDO::PARAM_STR);
                $stmt->execute();
                
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
            } else {
                throw new Exception("Unsupported database connection type");
            }
            
            // Add debug log separately before the conditional
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
    
    /**
     * Toggle user active status
     * 
     * @param int $userId User ID to toggle
     * @return bool Success flag
     */
    public function toggleStatus($userId) {
        try {
            $sql = "UPDATE users SET is_active = NOT is_active WHERE user_id = ?";
            $stmt = $this->db->prepare($sql);
            
            if (!$stmt) {
                error_log("Prepare failed: " . $this->db->error);
                return false;
            }
            
            $stmt->bind_param('i', $userId);
            $result = $stmt->execute();
            
            return $result && $stmt->affected_rows > 0;
        } catch (Exception $e) {
            error_log("Toggle status error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Update the password hash if the algorithm has changed
     * 
     * @param int $userId User ID
     * @param string $password Plain text password
     * @return bool Success flag
     */
    private function updatePasswordHash($userId, $password) {
        try {
            $newHash = password_hash($password, PASSWORD_DEFAULT);
            
            if ($this->db instanceof mysqli) {
                $stmt = $this->db->prepare("UPDATE users SET password_hash = ? WHERE user_id = ?");
                $stmt->bind_param("si", $newHash, $userId);
                return $stmt->execute();
            } elseif ($this->db instanceof PDO) {
                $stmt = $this->db->prepare("UPDATE users SET password_hash = :hash WHERE user_id = :id");
                $stmt->bindParam(':hash', $newHash, PDO::PARAM_STR);
                $stmt->bindParam(':id', $userId, PDO::PARAM_INT);
                return $stmt->execute();
            }
            return false;
        } catch (Exception $e) {
            error_log("Failed to update password hash: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Update last login timestamp
     * 
     * @param int $userId User ID
     * @return bool Success flag
     */
    private function updateLastLogin($userId) {
        try {
            if ($this->db instanceof mysqli) {
                $stmt = $this->db->prepare("UPDATE users SET last_login = NOW() WHERE user_id = ?");
                $stmt->bind_param("i", $userId);
                return $stmt->execute();
            } elseif ($this->db instanceof PDO) {
                $stmt = $this->db->prepare("UPDATE users SET last_login = NOW() WHERE user_id = :id");
                $stmt->bindParam(':id', $userId, PDO::PARAM_INT);
                return $stmt->execute();
            }
            return false;
        } catch (Exception $e) {
            error_log("Failed to update last login: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Register a new user with secure password hashing and validation
     * 
     * @param string $email User email
     * @param string $password Password hash or plain text
     * @param string $firstName User first name
     * @param string $lastName User last name
     * @param string $phone User phone number
     * @param string $role User role (default: 'patient')
     * @return array Result with user_id or error message
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
            
            // Check if password is already hashed
            $passwordHash = $password;
            if (!$this->isPasswordHashed($password)) {
                // Validate password strength for plain text passwords
                $passwordValidation = $this->validatePasswordStrength($password);
                if ($passwordValidation !== true) {
                    return ['error' => $passwordValidation];
                }
                
                // Hash the password
                $passwordHash = password_hash($password, PASSWORD_DEFAULT);
            }
            
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
     * Check if a string is already a password hash
     * 
     * @param string $password String to check
     * @return bool True if already hashed
     */
    private function isPasswordHashed($password) {
        // Password hashes in PHP typically start with $2y$ (bcrypt)
        return (strlen($password) > 40 && strpos($password, '$2y$') === 0);
    }
    
    /**
     * Validate password strength
     * 
     * @param string $password Password to validate
     * @return bool|string True if valid, error message otherwise
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
     * 
     * @param string $email Email to check
     * @return bool True if email exists
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
     * Check if email is taken by another user
     * 
     * @param string $email Email to check
     * @param int $excludeUserId User ID to exclude from check
     * @return bool True if email is taken by another user
     */
    public function isEmailTakenByOther($email, $excludeUserId) {
        try {
            if ($this->db instanceof mysqli) {
                $query = "SELECT COUNT(*) as count FROM users WHERE email = ? AND user_id != ?";
                $stmt = $this->db->prepare($query);
                $stmt->bind_param("si", $email, $excludeUserId);
                $stmt->execute();
                
                $result = $stmt->get_result();
                $row = $result->fetch_assoc();
                
                return $row['count'] > 0;
            } elseif ($this->db instanceof PDO) {
                $query = "SELECT COUNT(*) as count FROM users WHERE email = :email AND user_id != :id";
                $stmt = $this->db->prepare($query);
                $stmt->bindParam(':email', $email, PDO::PARAM_STR);
                $stmt->bindParam(':id', $excludeUserId, PDO::PARAM_INT);
                $stmt->execute();
                
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                return $row['count'] > 0;
            }
            
            return false;
        } catch (Exception $e) {
            error_log("Email check error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Generate password reset token
     * 
     * @param string $email User email
     * @return array|bool Token data or false on failure
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
     * 
     * @param string $token Reset token
     * @param string $newPassword New password
     * @return array|bool True on success, error array otherwise
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
     * 
     * @param int $userId User ID
     * @return string|bool Token or false on failure
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
     * 
     * @param string $token Verification token
     * @return bool Success flag
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
     * Get users by role
     *
     * @param string $role Role to filter by (patient, provider, admin)
     * @return array Array of users with the specified role
     */
    public function getUsersByRole($role) {
        try {
            if ($this->db instanceof mysqli) {
                $stmt = $this->db->prepare("
                    SELECT user_id, email, first_name, last_name, phone, role, is_active,
                        email_verified_at, created_at, last_login
                    FROM users
                    WHERE role = ? AND is_active = 1
                    ORDER BY last_name, first_name
                ");
                $stmt->bind_param("s", $role);
                $stmt->execute();
                
                $result = $stmt->get_result();
                $users = [];
                while ($row = $result->fetch_assoc()) {
                    $users[] = $row;
                }
                return $users;
                
            } elseif ($this->db instanceof PDO) {
                $stmt = $this->db->prepare("
                    SELECT user_id, email, first_name, last_name, phone, role, is_active,
                        email_verified_at, created_at, last_login
                    FROM users
                    WHERE role = :role AND is_active = 1
                    ORDER BY last_name, first_name
                ");
                $stmt->bindParam(':role', $role, PDO::PARAM_STR);
                $stmt->execute();
                
                return $stmt->fetchAll(PDO::FETCH_ASSOC);
            }
            
            return [];
            
        } catch (Exception $e) {
            error_log("Get users by role error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get user by ID
     * 
     * @param int $userId User ID
     * @return array|null User data or null if not found
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
     * 
     * @param int $userId User ID
     * @param array $userData User data to update
     * @return bool|array Success flag or error array
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
                'email' => 's',
                'role' => 's',
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

    /**
     * Get all users with optional filtering
     * 
     * @param string $whereClause Optional WHERE clause
     * @param array $params Parameters for WHERE clause
     * @return array List of users
     */
    public function getAllUsersWithFilters($whereClause = "", $params = []) {
        try {
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
        } catch (Exception $e) {
            error_log("Get users error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Update user password
     * 
     * @param int $userId User ID
     * @param string $password New password (plain text)
     * @param int $passwordChangeRequired Whether password change is required on next login
     * @return bool Success flag
     */
    public function updatePassword($userId, $password, $passwordChangeRequired = 0) {
        try {
            $passwordHash = password_hash($password, PASSWORD_DEFAULT);
            
            if ($this->db instanceof mysqli) {
                $stmt = $this->db->prepare("
                    UPDATE users
                    SET password_hash = ?, password_change_required = ?
                    WHERE user_id = ?
                ");
                $stmt->bind_param("sii", $passwordHash, $passwordChangeRequired, $userId);
                return $stmt->execute();
            } elseif ($this->db instanceof PDO) {
                $stmt = $this->db->prepare("
                    UPDATE users
                    SET password_hash = :hash, password_change_required = :required
                    WHERE user_id = :id
                ");
                $stmt->bindParam(':hash', $passwordHash, PDO::PARAM_STR);
                $stmt->bindParam(':required', $passwordChangeRequired, PDO::PARAM_INT);
                $stmt->bindParam(':id', $userId, PDO::PARAM_INT);
                return $stmt->execute();
            }
            
            return false;
        } catch (Exception $e) {
            error_log("Update password error: " . $e->getMessage());
            return false;
        }
    }
    /**
     * Update patient profile information
     * 
     * @param int $patientId The patient's user ID
     * @param array $data The profile data to update
     * @return bool True on success, false on failure
     */
    public function updatePatientProfile($patientId, $data) {
        try {
            // Format insurance info as JSON
            $insuranceInfo = json_encode([
                'provider' => $data['insurance_provider'] ?? '',
                'policy_number' => $data['insurance_policy_number'] ?? ''
            ]);
            
            // First check if a profile already exists
            $stmt = $this->db->prepare("SELECT * FROM patient_profiles WHERE patient_id = ?");
            $stmt->bind_param("i", $patientId);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                // Update existing profile
                $query = "UPDATE patient_profiles SET 
                    phone = ?,
                    date_of_birth = ?, 
                    address = ?, 
                    emergency_contact = ?,
                    emergency_contact_phone = ?,
                    medical_conditions = ?,
                    insurance_info = ?,
                    updated_at = NOW()
                    WHERE patient_id = ?";
                
                $stmt = $this->db->prepare($query);
                $phone = $data['phone'] ?? '';
                $emergencyContact = $data['emergency_contact'] ?? '';
                $emergencyPhone = $data['emergency_contact_phone'] ?? '';
                $medicalConditions = $data['medical_conditions'] ?? '';
            
                $stmt->bind_param(
                    "sssssssi", 
                    $phone,
                    $data['date_of_birth'],
                    $data['address'],
                    $emergencyContact,
                    $emergencyPhone,
                    $medicalConditions,
                    $insuranceInfo,
                    $patientId
                );
            } else {
                // Insert new profile
                $query = "INSERT INTO patient_profiles (
                    patient_id, 
                    user_id,
                    phone,
                    date_of_birth, 
                    address,
                    emergency_contact,
                    emergency_contact_phone,
                    medical_conditions,
                    insurance_info,
                    created_at,
                    updated_at
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())";
                
                $stmt = $this->db->prepare($query);
                $phone = $data['phone'] ?? '';
                $emergencyContact = $data['emergency_contact'] ?? '';
                $emergencyPhone = $data['emergency_contact_phone'] ?? '';
                $medicalConditions = $data['medical_conditions'] ?? '';
            
                $stmt->bind_param(
                    "iisssssssi", 
                    $patientId,
                    $patientId, // user_id is the same as patient_id
                    $phone,
                    $data['date_of_birth'],
                    $data['address'],
                    $emergencyContact,
                    $emergencyPhone,
                    $medicalConditions,
                    $insuranceInfo
                );
            }
            
            $result = $stmt->execute();
            
            if (!$result) {
                error_log("Error updating patient profile: " . $stmt->error);
                return false;
            }
            
            return true;
        } catch (Exception $e) {
            error_log("Exception updating patient profile: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Change user password with current password verification
     * 
     * @param int $userId User ID
     * @param string $currentPassword Current password
     * @param string $newPassword New password
     * @return bool|array Success flag or error array
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
    

    /**
     * Get all patients (users with role 'patient')
     * 
     * @return array List of patients
     */
    public function getPatients() {
        try {
            $query = "
                SELECT user_id, CONCAT(first_name, ' ', last_name) AS full_name
                FROM users
                WHERE role = 'patient' AND is_active = 1
                ORDER BY first_name, last_name
            ";
            error_log("Executing query: " . $query);
            
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            
            // If using MySQLi
            $result = $stmt->get_result();
            if (!$result) {
                error_log("MySQLi error: " . $this->db->error);
                return [];
            }
            
            $patients = [];
            while ($row = $result->fetch_assoc()) {
                $patients[] = $row;
            }
            
            error_log("Found " . count($patients) . " patients");
            return $patients;
        } catch (Exception $e) {
            error_log("Exception in getPatients: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get patient profile by ID
     * 
     * @param int $patient_id Patient ID
     * @return array|null Patient data or null if not found
     */
    public function getPatientById($patient_id) {
        try {
            if ($this->db instanceof mysqli) {
                $query = "SELECT u.*, p.date_of_birth, p.insurance_info, p.medical_notes,
                     p.preferences, p.emergency_contact
                     FROM users u
                     LEFT JOIN patient_profiles p ON u.user_id = p.patient_id
                     WHERE u.user_id = ? AND u.role = 'patient'";
                
                $stmt = $this->db->prepare($query);
                $stmt->bind_param("i", $patient_id);
                $stmt->execute();
                
                $result = $stmt->get_result();
                return $result->fetch_assoc();
                
            } elseif ($this->db instanceof PDO) {
                $query = "SELECT u.*, p.date_of_birth, p.insurance_info, p.medical_notes,
                     p.preferences, p.emergency_contact
                     FROM users u
                     LEFT JOIN patient_profiles p ON u.user_id = p.patient_id
                     WHERE u.user_id = :id AND u.role = 'patient'";
                
                $stmt = $this->db->prepare($query);
                $stmt->bindParam(':id', $patient_id, PDO::PARAM_INT);
                $stmt->execute();
                
                return $stmt->fetch(PDO::FETCH_ASSOC);
            }
            
            return null;
        } catch (Exception $e) {
            error_log("Error getting patient: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Get total count of users
     * 
     * @return int Total user count
     */
    public function getTotalCount() {
        try {
            $stmt = $this->db->query("SELECT COUNT(*) as count FROM users");
            if ($stmt) {
                $result = $stmt->fetch_assoc();
                return $result['count'];
            }
            return 0;
        } catch (Exception $e) {
            error_log("Error getting user count: " . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Get count of users by role
     * 
     * @param string $role Role to count
     * @return int Count of users with specified role
     */
    public function getCountByRole($role) {
        try {
            $stmt = $this->db->prepare("SELECT COUNT(*) as count FROM users WHERE role = ?");
            $stmt->bind_param("s", $role);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result) {
                $row = $result->fetch_assoc();
                return $row['count'];
            }
            return 0;
        } catch (Exception $e) {
            error_log("Error getting role count: " . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Search providers by specialty and location
     * 
     * @param string $specialty Specialty to search for
     * @param string $location Location to search for
     * @return array List of matching providers
     */
    public function searchProviders($specialty = '', $location = '') {
        try {
            if ($this->db instanceof mysqli) {
                $conditions = [];
                $params = [];
                $types = "";
                
                $query = "SELECT u.user_id, u.first_name, u.last_name,
                     p.specialization, p.title, p.bio, p.accepting_new_patients
                     FROM users u
                     JOIN provider_profiles p ON u.user_id = p.provider_id
                     WHERE u.role = 'provider' AND u.is_active = 1";
                
                if (!empty($specialty)) {
                    $query .= " AND p.specialization LIKE ?";
                    $specialty = "%$specialty%";
                    $params[] = $specialty;
                    $types .= "s";
                }
                
                if (!empty($location)) {
                    // Assuming you have location data in users table or provider_profiles
                    $query .= " AND (u.address LIKE ? OR u.city LIKE ?)";
                    $location = "%$location%";
                    $params[] = $location;
                    $params[] = $location;
                    $types .= "ss";
                }
                
                $query .= " ORDER BY u.last_name, u.first_name";
                
                $stmt = $this->db->prepare($query);
                
                if (!empty($params)) {
                    $stmt->bind_param($types, ...$params);
                }
                
                $stmt->execute();
                $result = $stmt->get_result();
                
                $providers = [];
                while ($row = $result->fetch_assoc()) {
                    $providers[] = $row;
                }
                
                return $providers;
                
            } elseif ($this->db instanceof PDO) {
                // PDO implementation would go here
                // Similar structure to the mysqli implementation
                return [];
            }
            
            return [];
        } catch (Exception $e) {
            error_log("Error searching providers: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get user activity log
     * 
     * @param int $userId User ID
     * @param int $limit Number of records to return
     * @return array Activity log entries
     */
    public function getUserActivityLog($userId, $limit = 10) {
        try {
            if ($this->db instanceof mysqli) {
                $query = "SELECT * FROM activity_log 
                          WHERE user_id = ? 
                          ORDER BY created_at DESC 
                          LIMIT ?";
                
                $stmt = $this->db->prepare($query);
                $stmt->bind_param("ii", $userId, $limit);
                $stmt->execute();
                
                $result = $stmt->get_result();
                return $result->fetch_all(MYSQLI_ASSOC);
            } elseif ($this->db instanceof PDO) {
                $query = "SELECT * FROM activity_log 
                          WHERE user_id = :user_id 
                          ORDER BY created_at DESC 
                          LIMIT :limit";
                
                $stmt = $this->db->prepare($query);
                $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
                $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
                $stmt->execute();
                
                return $stmt->fetchAll(PDO::FETCH_ASSOC);
            }
            
            return [];
        } catch (Exception $e) {
            error_log("Error getting user activity log: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Log user activity
     * 
     * @param int $userId User ID
     * @param string $action Action performed
     * @param string $details Additional details
     * @return bool Success flag
     */
    public function logActivity($userId, $action, $details = '') {
        try {
            if ($this->db instanceof mysqli) {
                $query = "INSERT INTO activity_log (user_id, action, details, created_at) 
                          VALUES (?, ?, ?, NOW())";
                
                $stmt = $this->db->prepare($query);
                $stmt->bind_param("iss", $userId, $action, $details);
                return $stmt->execute();
            } elseif ($this->db instanceof PDO) {
                $query = "INSERT INTO activity_log (user_id, action, details, created_at) 
                          VALUES (:user_id, :action, :details, NOW())";
                
                $stmt = $this->db->prepare($query);
                $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
                $stmt->bindParam(':action', $action, PDO::PARAM_STR);
                $stmt->bindParam(':details', $details, PDO::PARAM_STR);
                return $stmt->execute();
            }
            
            return false;
        } catch (Exception $e) {
            error_log("Error logging user activity: " . $e->getMessage());
            return false;
        }
    }
    /**
     * Get patient profile data
     *
     * @param int $patientId The patient's user ID
     * @return array|null Patient profile data or null if not found
     */
    public function getPatientProfile($patientId) {
        try {
            // First check if the patient profile exists
            $query = "SELECT * FROM patient_profiles WHERE patient_id = ?";
            $stmt = $this->db->prepare($query);
            $stmt->bind_param("i", $patientId);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                // Profile exists, get it
                $profile = $result->fetch_assoc();
                
                // Extract insurance info from JSON
                if (!empty($profile['insurance_info'])) {
                    $insuranceInfo = json_decode($profile['insurance_info'], true);
                    if (is_array($insuranceInfo)) {
                        $profile['insurance_provider'] = $insuranceInfo['provider'] ?? '';
                        $profile['insurance_policy_number'] = $insuranceInfo['policy_number'] ?? '';
                    }
                }
                
                // If emergency contact fields are empty but we have data in medical_history JSON,
                // extract it for backward compatibility
                if (empty($profile['emergency_contact']) && !empty($profile['medical_history'])) {
                    $medicalHistory = json_decode($profile['medical_history'], true);
                    if (is_array($medicalHistory)) {
                        $profile['emergency_contact'] = $medicalHistory['emergency_contact_name'] ?? '';
                        $profile['emergency_contact_phone'] = $medicalHistory['emergency_contact_phone'] ?? '';
                        
                        // If medical_conditions is empty, populate it from the JSON
                        if (empty($profile['medical_conditions'])) {
                            $profile['medical_conditions'] = $medicalHistory['conditions'] ?? '';
                        }
                    }
                }
                
                return $profile;
            }
            
            // If no profile exists, get basic user data
            $query = "SELECT user_id, first_name, last_name, email, phone, created_at
                    FROM users
                    WHERE user_id = ? AND role = 'patient'";
            $stmt = $this->db->prepare($query);
            $stmt->bind_param("i", $patientId);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                // Return basic user data
                $userData = $result->fetch_assoc();
                // Add empty profile fields
                $userData['date_of_birth'] = null;
                $userData['address'] = null;
                $userData['emergency_contact'] = null;
                $userData['emergency_contact_phone'] = null;
                $userData['medical_conditions'] = null;
                $userData['insurance_provider'] = null;
                $userData['insurance_policy_number'] = null;
                return $userData;
            }
            
            return null;
        } catch (Exception $e) {
            error_log("Error in getPatientProfile: " . $e->getMessage());
            return null;
        }
    }
     /**
     * Get all available providers for booking
     * 
     * @return array List of available providers
     */
    public function getAvailableProviders() {
        try {
            if ($this->db instanceof mysqli) {
                // MySQL implementation
                $query = "SELECT u.user_id, u.first_name, u.last_name,
                     p.specialization, p.title, p.bio, p.accepting_new_patients
                     FROM users u
                     JOIN provider_profiles p ON u.user_id = p.provider_id
                     WHERE u.role = 'provider'
                     AND u.is_active = 1
                     AND p.accepting_new_patients = 1
                     ORDER BY u.last_name, u.first_name";
                
                $stmt = $this->db->prepare($query);
                if (!$stmt) {
                    error_log("Prepare failed: " . $this->db->error);
                    return [];
                }
                
                $stmt->execute();
                $result = $stmt->get_result();
                
                $providers = [];
                while ($row = $result->fetch_assoc()) {
                    $providers[] = $row;
                }
                
                return $providers;
                
            } elseif ($this->db instanceof PDO) {
                // PDO implementation
                $query = "SELECT u.user_id, u.first_name, u.last_name,
                     p.specialization, p.title, p.bio, p.accepting_new_patients
                     FROM users u
                     JOIN provider_profiles p ON u.user_id = p.provider_id
                     WHERE u.role = 'provider'
                     AND u.is_active = 1
                     AND p.accepting_new_patients = 1
                     ORDER BY u.last_name, u.first_name";
                
                $stmt = $this->db->prepare($query);
                $stmt->execute();
                
                return $stmt->fetchAll(PDO::FETCH_ASSOC);
            } else {
                throw new Exception("Unsupported database connection type");
            }
        } catch (Exception $e) {
            error_log("Error getting available providers: " . $e->getMessage());
            return [];
        }
    }
}
?>
