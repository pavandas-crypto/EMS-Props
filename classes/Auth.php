<?php
/**
 * Authentication Class
 * Handles user login and session management
 */

class Auth {
    private $db;
    
    public function __construct($database) {
        $this->db = $database;
    }
    
    /**
     * Login user
     */
    public function login($email, $password) {
        try {
            $sql = "SELECT user_id, name, email, password, role FROM users WHERE email = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->bind_param('s', $email);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows === 0) {
                return ['success' => false, 'message' => ERROR_INVALID_CREDENTIALS];
            }
            
            $user = $result->fetch_assoc();
            
            if (!verify_password($password, $user['password'])) {
                if ($user['password'] !== $password) {
                    return ['success' => false, 'message' => ERROR_INVALID_CREDENTIALS];
                }

                // Upgrade plain text password to a secure hash for compatibility with sample data.
                $user['password'] = hash_password($password);
                $this->db->update('users', ['password' => $user['password']], ['user_id' => $user['user_id']]);
            }
            
            // Set session
            $_SESSION[SESSION_NAME] = [
                'user_id' => $user['user_id'],
                'name' => $user['name'],
                'email' => $user['email'],
                'role' => $user['role'],
                'last_activity' => time()
            ];
            
            log_activity('LOGIN', 'User logged in', $user['user_id']);
            
            return ['success' => true, 'message' => SUCCESS_LOGIN, 'user' => $user];
            
        } catch (Exception $e) {
            error_log('Login Error: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Login failed'];
        }
    }
    
    /**
     * Logout user
     */
    public function logout() {
        $user_id = get_current_user_id();
        log_activity('LOGOUT', 'User logged out', $user_id);
        
        unset($_SESSION[SESSION_NAME]);
        session_destroy();
        
        return ['success' => true, 'message' => SUCCESS_LOGOUT];
    }
    
    /**
     * Register user
     */
    public function register($name, $email, $password, $role = ROLE_ADMIN) {
        try {
            // Validate input
            if (!is_valid_email($email)) {
                return ['success' => false, 'message' => ERROR_INVALID_EMAIL];
            }
            
            $password_errors = validate_password_strength($password);
            if (!empty($password_errors)) {
                return ['success' => false, 'message' => implode(', ', $password_errors)];
            }
            
            // Check if user exists
            $sql = "SELECT user_id FROM users WHERE email = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->bind_param('s', $email);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                return ['success' => false, 'message' => ERROR_USER_EXISTS];
            }
            
            // Create user
            $hashed_password = hash_password($password);
            $user_id = $this->db->insert('users', [
                'name' => $name,
                'email' => $email,
                'password' => $hashed_password,
                'role' => $role,
                'created_at' => date('Y-m-d H:i:s')
            ]);
            
            log_activity('USER_CREATED', 'New user created: ' . $email);
            
            return ['success' => true, 'message' => 'User created successfully', 'user_id' => $user_id];
            
        } catch (Exception $e) {
            error_log('Registration Error: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Registration failed'];
        }
    }
    
    /**
     * Change password
     */
    public function change_password($user_id, $old_password, $new_password) {
        try {
            $sql = "SELECT password FROM users WHERE user_id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->bind_param('i', $user_id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows === 0) {
                return ['success' => false, 'message' => 'User not found'];
            }
            
            $user = $result->fetch_assoc();
            
            if (!verify_password($old_password, $user['password'])) {
                return ['success' => false, 'message' => 'Old password is incorrect'];
            }
            
            $password_errors = validate_password_strength($new_password);
            if (!empty($password_errors)) {
                return ['success' => false, 'message' => implode(', ', $password_errors)];
            }
            
            $hashed_password = hash_password($new_password);
            $this->db->update('users', 
                ['password' => $hashed_password, 'updated_at' => date('Y-m-d H:i:s')],
                ['user_id' => $user_id]
            );
            
            log_activity('PASSWORD_CHANGED', 'User changed password', $user_id);
            
            return ['success' => true, 'message' => 'Password changed successfully'];
            
        } catch (Exception $e) {
            error_log('Change Password Error: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Failed to change password'];
        }
    }
    
    /**
     * Get user by ID
     */
    public function get_user($user_id) {
        try {
            $sql = "SELECT user_id, name, email, role, created_at FROM users WHERE user_id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->bind_param('i', $user_id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            return $result->fetch_assoc();
            
        } catch (Exception $e) {
            error_log('Get User Error: ' . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Verify user exists
     */
    public function user_exists($email) {
        try {
            $count = $this->db->count('users', ['email' => $email]);
            return $count > 0;
        } catch (Exception $e) {
            error_log('User Exists Error: ' . $e->getMessage());
            return false;
        }
    }
}

?>
