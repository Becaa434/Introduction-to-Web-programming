<?php
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class AuthMiddleware {
    
    public function verifyToken($token) {
        // Check if token exists
        if (!$token) {
            Flight::halt(401, "Missing authentication header");
        }
        
        // Remove 'Bearer ' prefix if present
        // This is a common practice for passing JWTs in HTTP headers
        if (strpos($token, 'Bearer ') === 0) {
            $token = substr($token, 7);
        }
        
        try {
            // Decode the token
            $decoded_token = JWT::decode($token, new Key(Config::JWT_SECRET(), 'HS256'));
            
            // Check if token is expired
            if (isset($decoded_token->exp) && $decoded_token->exp < time()) {
                throw new Exception('Token has expired');
            }
            
            // Store user data from token for use in routes
            Flight::set('user', $decoded_token->user);
            Flight::set('jwt_token', $token);
            return true;
        } catch (\Exception $e) {
            Flight::halt(401, "Invalid token: " . $e->getMessage());
        }
    }
    
    public function authorizeRole($requiredRole) {
        $user = Flight::get('user');
        
        if (!$user) {
            Flight::halt(401, "User not authenticated");
        }
        
        // Get user role - handling both object and array
        $userRole = is_object($user) ? $user->role : $user['role'];
        
        if ($userRole !== $requiredRole) {
            Flight::halt(403, 'Access denied: insufficient privileges');
        }
        
        return true;
    }
    
    public function authorizeRoles($roles) {
        $user = Flight::get('user');
        
        if (!$user) {
            Flight::halt(401, "User not authenticated");
        }
        
        // Get user role - handling both object and array
        $userRole = is_object($user) ? $user->role : $user['role'];
        
        if (!in_array($userRole, $roles)) {
            Flight::halt(403, 'Forbidden: role not allowed');
        }
        
        return true;
    }
    
    public function authorizePermission($permission) {
        $user = Flight::get('user');
        
        if (!$user) {
            Flight::halt(401, "User not authenticated");
        }
        
        $permissions = is_object($user) ? 
            (isset($user->permissions) ? $user->permissions : []) : 
            (isset($user['permissions']) ? $user['permissions'] : []);
        
        if (!in_array($permission, $permissions)) {
            Flight::halt(403, 'Access denied: permission missing');
        }
        
        return true;
    }
}