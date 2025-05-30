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
    
    public function register($entity) { 
        try {
            // Validation
            if (empty($entity['email']) || empty($entity['password'])) {
                return ['success' => false, 'error' => 'Email and password are required.'];
            }
            
            if (empty($entity['name'])) {
                return ['success' => false, 'error' => 'Name is required.'];
            }
            
            // Check if email already exists
            $email_exists = $this->auth_dao->get_user_by_email($entity['email']);
            if($email_exists){
                return ['success' => false, 'error' => 'Email already registered.'];
            }
            
            // Hash password
            $entity['password'] = password_hash($entity['password'], PASSWORD_BCRYPT);
            
            // Set default role if not provided
            if (!isset($entity['role'])) {
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
                
                return ['success' => true, 'data' => $responseData];
            } else {
                return ['success' => false, 'error' => 'Failed to create user.'];
            }
        } catch (Exception $e) {
            error_log("Registration error: " . $e->getMessage());
            return ['success' => false, 'error' => 'Registration failed: ' . $e->getMessage()];
        }
    }
    
    public function login($entity) { 
        try {
            if (empty($entity['email']) || empty($entity['password'])) {
                return ['success' => false, 'error' => 'Email and password are required.'];
            }
            
            $user = $this->auth_dao->get_user_by_email($entity['email']);
            if(!$user){
                return ['success' => false, 'error' => 'Invalid username or password.'];
            }
            
            if(!password_verify($entity['password'], $user['password'])) {
                return ['success' => false, 'error' => 'Invalid username or password.'];
            }
            
            unset($user['password']);
            
            $jwt_payload = [
                'user' => $user,
                'iat' => time(),
                'exp' => time() + (60 * 60 * 24) // valid for 1 day
            ];
            
            $token = JWT::encode(
                $jwt_payload,
                Config::JWT_SECRET(),
                'HS256'
            );
            
            return ['success' => true, 'data' => array_merge($user, ['token' => $token])]; 
        } catch (Exception $e) {
            error_log("Login error: " . $e->getMessage());
            return ['success' => false, 'error' => 'Login failed: ' . $e->getMessage()];
        }
    }
}