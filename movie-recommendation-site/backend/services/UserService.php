<?php
require_once __DIR__ . '/BaseService.php';
require_once __DIR__ . '/../dao/UserDao.php';

class UserService extends BaseService {
    public function __construct() {
        $dao = new UserDao();
        parent::__construct($dao);
    }
    
    // Business logic for user registration
    public function registerUser($data) {
        // Validate required fields
        if (empty($data['username']) || empty($data['email']) || empty($data['password'])) {
            throw new Exception('Username, email, and password are required.');
        }
        
        // Validate email format
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            throw new Exception('Invalid email format.');
        }
        
        // Check if username or email already exists
        if ($this->dao->getUserByUsername($data['username'])) {
            throw new Exception('Username already exists.');
        }
        
        if ($this->dao->getUserByEmail($data['email'])) {
            throw new Exception('Email already registered.');
        }
        
        // Hash password before storing
        $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        
        return $this->create($data);
    }
    
    // Business logic for user login
    public function loginUser($username, $password) {
        $user = $this->dao->getUserByUsername($username);
        
        if (!$user) {
            throw new Exception('User not found.');
        }
        
        if (!password_verify($password, $user['password'])) {
            throw new Exception('Invalid password.');
        }
        
        return $user;
    }
    
    // Get user by username
    public function getByUsername($username) {
        return $this->dao->getUserByUsername($username);
    }
    
    // Get user by email
    public function getByEmail($email) {
        return $this->dao->getUserByEmail($email);
    }
}
?>