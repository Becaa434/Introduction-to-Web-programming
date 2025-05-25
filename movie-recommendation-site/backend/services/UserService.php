<?php
require_once __DIR__ . '/../dao/config.php';
require_once __DIR__ . '/BaseService.php';
require_once __DIR__ . '/../dao/UserDao.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class UserService extends BaseService {
    public function __construct() {
        $dao = new UserDao();
        parent::__construct($dao);
    }
    
    /**
     * Register a new user
     * @param array $data User data including email, password, and name
     * @return array Response containing success status and data or error message
     */
    public function register($data) {
    // Validate required fields
    if (empty($data['email']) || empty($data['password']) || empty($data['name'])) {
        return ['success' => false, 'error' => 'Name, email, and password are required.'];
    }
    
    // Validate email format
    if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        return ['success' => false, 'error' => 'Invalid email format.'];
    }
    
    // Check if email is already in use
    $existingUser = $this->dao->getByEmail($data['email']);
    if ($existingUser) {
        return ['success' => false, 'error' => 'Email already registered.'];
    }
    
    // Hash password for security
    $data['password'] = password_hash($data['password'], PASSWORD_BCRYPT);
    
    // Set default role if not provided 
    if (!isset($data['role'])) {
        $data['role'] = 'user'; // Default role
    }
    
    // Add created_at timestamp
    $data['created_at'] = date('Y-m-d H:i:s');
    
    // Insert user
    $id = $this->dao->insert($data);
    
    if ($id) {
        // Get the user without password
        $user = $this->dao->getById($id);
        unset($user['password']);
        
        return ['success' => true, 'data' => $user];
    } else {
        return ['success' => false, 'error' => 'Failed to create user.'];
    }
}
    
    /**
     * Authenticate user and generate JWT token
     * @param array $data Login credentials (email and password)
     * @return array Response containing success status and user data with token or error message
     */
    public function login($data) {
    // Validate required fields
    if (empty($data['email']) || empty($data['password'])) {
        return ['success' => false, 'error' => 'Email and password are required.'];
    }
    
    // Get user by email
    $user = $this->dao->getByEmail($data['email']);
    
    // Check if user exists and password is correct
    if (!$user || !password_verify($data['password'], $user['password'])) {
        return ['success' => false, 'error' => 'Invalid email or password.'];
    }
    
    // Remove password from response
    unset($user['password']);
    
    // Make sure user object has a role 
    if (!isset($user['role'])) {
        $user['role'] = 'user'; // Fallback role if missing
    }
    
    // Create JWT payload
    $jwt_payload = [
        'user' => $user,
        'iat' => time(),
        'exp' => time() + (60 * 60 * 24) // Token valid for 24 hours
    ];
    
    // Generate JWT token
    $token = JWT::encode(
        $jwt_payload,
        Config::JWT_SECRET(),
        'HS256'
    );
    
    // Return user data with token
    return [
        'success' => true,
        'data' => array_merge($user, ['token' => $token])
    ];
}
    
    /**
     * Get user by email
     * @param string $email Email
     * @return array|false User data or false if not found
     */
    public function getByEmail($email) {
        return $this->dao->getByEmail($email);
    }
}
?>