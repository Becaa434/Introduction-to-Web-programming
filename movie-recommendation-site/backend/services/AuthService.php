<?php
require_once __DIR__ . '/BaseService.php';
require_once __DIR__ . '/../dao/AuthDao.php';
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class AuthService extends BaseService {
    private $auth_dao;
    
    public function __construct() {
        $this->auth_dao = new AuthDao();
        parent::__construct(new AuthDao);
    }
    
    public function get_user_by_email($email){
        return $this->auth_dao->get_user_by_email($email);
    }
    
    /**
     * Sanitize user input to prevent XSS attacks
     */
    private function sanitizeInput($input) {
        if (is_array($input)) {
            return array_map([$this, 'sanitizeInput'], $input);
        }
        return htmlspecialchars(strip_tags(trim($input)), ENT_QUOTES, 'UTF-8');
    }
    
    /**
     * Validate password strength
     */
    private function validatePasswordStrength($password) {
        // At least 8 characters
        if (strlen($password) < 8) {
            return 'Password must be at least 8 characters long.';
        }
        
        // At least one uppercase letter
        if (!preg_match('/[A-Z]/', $password)) {
            return 'Password must contain at least one uppercase letter.';
        }
        
        // At least one lowercase letter
        if (!preg_match('/[a-z]/', $password)) {
            return 'Password must contain at least one lowercase letter.';
        }
        
        // At least one number
        if (!preg_match('/\d/', $password)) {
            return 'Password must contain at least one number.';
        }
        
        // At least one special character
        if (!preg_match('/[@$!%*?&]/', $password)) {
            return 'Password must contain at least one special character (@$!%*?&).';
        }
        
        return null; // Password is valid
    }
    
    /**
     * Validate email format and length
     */
    private function validateEmail($email) {
        // Check length
        if (strlen($email) > 255) {
            return 'Email address is too long.';
        }
        
        // Check format
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return 'Invalid email format.';
        }
        
        return null; // Email is valid
    }
    
    /* Validate name*/
    
    private function validateName($name) {
        if (strlen($name) < 2) {
            return 'Name must be at least 2 characters long.';
        }
        
        if (strlen($name) > 100) {
            return 'Name cannot exceed 100 characters.';
        }
        
        // Only allow letters, spaces, hyphens, and apostrophes
        if (!preg_match('/^[a-zA-Z\s\-\']+$/', $name)) {
            return 'Name can only contain letters, spaces, hyphens, and apostrophes.';
        }
        
        return null; // Name is valid
    }
    
    /**
     * Rate limiting check
     */
    private function checkRateLimit($email) {
       
        session_start();
        
        $key = 'login_attempts_' . md5($email);
        $now = time();
        $attempts = $_SESSION[$key] ?? [];
        
        // Remove attempts older than 15 minutes
        $attempts = array_filter($attempts, function($timestamp) use ($now) {
            return ($now - $timestamp) < 900; // 15 minutes
        });
        
        // Check if more than 5 attempts in 15 minutes
        if (count($attempts) >= 5) {
            return false;
        }
        
        // Add current attempt
        $attempts[] = $now;
        $_SESSION[$key] = $attempts;
        
        return true;
    }
    
    public function register($entity) { 
        try {
            // Sanitize all inputs
            $entity = $this->sanitizeInput($entity);
            
            // Server-side validation
            if (empty($entity['email']) || empty($entity['password']) || empty($entity['name'])) {
                return ['success' => false, 'error' => 'Name, email, and password are required.'];
            }
            
            // Validate email
            $emailValidation = $this->validateEmail($entity['email']);
            if ($emailValidation) {
                return ['success' => false, 'error' => $emailValidation];
            }
            
            // Validate name
            $nameValidation = $this->validateName($entity['name']);
            if ($nameValidation) {
                return ['success' => false, 'error' => $nameValidation];
            }
            
            // Validate password strength
            $passwordValidation = $this->validatePasswordStrength($entity['password']);
            if ($passwordValidation) {
                return ['success' => false, 'error' => $passwordValidation];
            }
            
            // Check if email already exists
            $email_exists = $this->auth_dao->get_user_by_email($entity['email']);
            if($email_exists){
                return ['success' => false, 'error' => 'Email already registered.'];
            }
            
            // Hash password with strong options
            $entity['password'] = password_hash($entity['password'], PASSWORD_ARGON2ID, [
                'memory_cost' => 65536, // 64 MB
                'time_cost' => 4,       // 4 iterations
                'threads' => 3,         // 3 threads
            ]);
            
            // Set default role if not provided
            if (!isset($entity['role']) || !in_array($entity['role'], ['user', 'admin'])) {
                $entity['role'] = 'user';
            }
            
            // Add timestamp
            $entity['created_at'] = date('Y-m-d H:i:s');
            
            // Remove confirm-password if it exists (it's not a database field)
            if (isset($entity['confirm-password'])) {
                unset($entity['confirm-password']);
            }
            
            // Insert the user and get the new ID
            $newUserId = $this->auth_dao->create($entity);
            
            if ($newUserId) {
                // Create response data without password
                $responseData = $entity;
                unset($responseData['password']);
                $responseData['id'] = $newUserId;
                
                // Log successful registration
                error_log("User registered successfully: " . $entity['email']);
                
                return ['success' => true, 'data' => $responseData];
            } else {
                return ['success' => false, 'error' => 'Failed to create user.'];
            }
        } catch (Exception $e) {
            error_log("Registration error: " . $e->getMessage());
            return ['success' => false, 'error' => 'Registration failed. Please try again.'];
        }
    }
    
    public function login($entity) { 
        try {
            // Sanitize inputs
            $entity = $this->sanitizeInput($entity);
            
            if (empty($entity['email']) || empty($entity['password'])) {
                return ['success' => false, 'error' => 'Email and password are required.'];
            }
            
            // Validate email format
            $emailValidation = $this->validateEmail($entity['email']);
            if ($emailValidation) {
                return ['success' => false, 'error' => 'Invalid email format.'];
            }
            
            // Check rate limiting
            if (!$this->checkRateLimit($entity['email'])) {
                error_log("Rate limit exceeded for email: " . $entity['email']);
                return ['success' => false, 'error' => 'Too many login attempts. Please try again in 15 minutes.'];
            }
            
            $user = $this->auth_dao->get_user_by_email($entity['email']);
            if(!$user){
                // Log failed login attempt
                error_log("Login attempt with non-existent email: " . $entity['email']);
                return ['success' => false, 'error' => 'Invalid email or password.'];
            }
            
            if(!password_verify($entity['password'], $user['password'])) {
                // Log failed login attempt
                error_log("Failed login attempt for email: " . $entity['email']);
                return ['success' => false, 'error' => 'Invalid email or password.'];
            }
            
            // Remove password from user data
            unset($user['password']);
            
            // Generate secure JWT token
            $jwt_payload = [
                'user' => $user,
                'iat' => time(),
                'exp' => time() + (60 * 60 * 24), // valid for 1 day
                'jti' => bin2hex(random_bytes(16)), // unique token ID
                'iss' => $_SERVER['HTTP_HOST'] ?? 'localhost' // issuer
            ];
            
            $token = JWT::encode(
                $jwt_payload,
                Config::JWT_SECRET(),
                'HS256'
            );
            
            // Log successful login
            error_log("User logged in successfully: " . $entity['email']);
            
            // Clear rate limiting on successful login
            session_start();
            $key = 'login_attempts_' . md5($entity['email']);
            unset($_SESSION[$key]);
            
            return ['success' => true, 'data' => array_merge($user, ['token' => $token])]; 
        } catch (Exception $e) {
            error_log("Login error: " . $e->getMessage());
            return ['success' => false, 'error' => 'Login failed. Please try again.'];
        }
    }
    
    /**
     * Check if email already exists (for AJAX validation)
     */
    public function checkEmailExists($email) {
        try {
            $email = $this->sanitizeInput($email);
            
            $emailValidation = $this->validateEmail($email);
            if ($emailValidation) {
                return ['exists' => false, 'error' => $emailValidation];
            }
            
            $user = $this->auth_dao->get_user_by_email($email);
            return ['exists' => $user !== null];
        } catch (Exception $e) {
            error_log("Email check error: " . $e->getMessage());
            return ['exists' => false, 'error' => 'Unable to check email.'];
        }
    }
}