<?php
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

/**
 * CSRF Protection Middleware
 */
function generateCSRFToken() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verifyCSRFToken($token) {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Input validation middleware
 */
function validateRequestData($requiredFields = []) {
    $data = Flight::request()->data->getData();
    
    // Check for required fields
    foreach ($requiredFields as $field) {
        if (!isset($data[$field]) || empty(trim($data[$field]))) {
            Flight::halt(400, json_encode(['error' => "Missing required field: {$field}"]));
        }
    }
    
    // Check request size (prevent DoS)
    $contentLength = $_SERVER['CONTENT_LENGTH'] ?? 0;
    if ($contentLength > 1048576) { // 1MB limit
        Flight::halt(413, json_encode(['error' => 'Request too large']));
    }
    
    return $data;
}

Flight::group('/auth', function() {
    /**
     * @OA\Post(
     * path="/auth/register",
     * summary="Register new user.",
     * description="Add a new user to the database with enhanced security validation.",
     * tags={"auth"},
     * @OA\RequestBody(
     *    description="Add new user",
     *    required=true,
     *    @OA\MediaType(
     *        mediaType="application/json",
     *        @OA\Schema(
     *            required={"password", "email", "name"},
     *            @OA\Property(
     *                property="name",
     *                type="string",
     *                example="John Doe",
     *                description="User's full name (2-100 characters, letters only)"
     *            ),
     *            @OA\Property(
     *                property="password",
     *                type="string",
     *                example="SecurePass123!",
     *                description="User password (min 8 chars, must include uppercase, lowercase, number, special char)"
     *            ),
     *            @OA\Property(
     *                property="email",
     *                type="string",
     *                example="user@example.com",
     *                description="Valid email address"
     *            )
     *        )
     *    )
     * ),
     * @OA\Response(
     *    response=200,
     *    description="User has been registered successfully."
     * ),
     * @OA\Response(
     *    response=400,
     *    description="Validation error."
     * ),
     * @OA\Response(
     *    response=500,
     *    description="Internal server error."
     * )
     * )
     */
    Flight::route("POST /register", function () {
        try {
            // Rate limiting check
            $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
            session_start();
            $key = 'register_attempts_' . md5($ip);
            $now = time();
            $attempts = $_SESSION[$key] ?? [];
            
            // Remove attempts older than 1 hour
            $attempts = array_filter($attempts, function($timestamp) use ($now) {
                return ($now - $timestamp) < 3600;
            });
            
            if (count($attempts) >= 10) { // Max 10 registrations per hour per IP
                Flight::halt(429, json_encode(['error' => 'Too many registration attempts. Please try again later.']));
            }
            
            $attempts[] = $now;
            $_SESSION[$key] = $attempts;
            
            // Validate request data
            $data = validateRequestData(['name', 'email', 'password']);
            
            // Additional validation for registration
            if (strlen($data['name']) > 100) {
                Flight::halt(400, json_encode(['error' => 'Name cannot exceed 100 characters.']));
            }
            
            if (strlen($data['email']) > 255) {
                Flight::halt(400, json_encode(['error' => 'Email address is too long.']));
            }
            
            if (strlen($data['password']) > 255) {
                Flight::halt(400, json_encode(['error' => 'Password is too long.']));
            }
            
            $response = Flight::auth_service()->register($data);
            
            if ($response['success']) {
                // Clear registration attempts on success
                unset($_SESSION[$key]);
                
                Flight::json([
                    'message' => 'User registered successfully',
                    'data' => $response['data']
                ], 201);
            } else {
                Flight::halt(400, json_encode(['error' => $response['error']]));
            }
        } catch (Exception $e) {
            error_log("Registration route error: " . $e->getMessage());
            Flight::halt(500, json_encode(['error' => 'Registration failed. Please try again.']));
        }
    });
    
    /**
     * @OA\Post(
     * path="/auth/login",
     * tags={"auth"},
     * summary="Login to system using email and password with enhanced security",
     * @OA\Response(
     *    response=200,
     *    description="User data and JWT token"
     * ),
     * @OA\Response(
     *    response=400,
     *    description="Validation error"
     * ),
     * @OA\Response(
     *    response=401,
     *    description="Authentication failed"
     * ),
     * @OA\Response(
     *    response=429,
     *    description="Too many login attempts"
     * ),
     * @OA\RequestBody(
     *    description="Login credentials",
     *    @OA\JsonContent(
     *        required={"email","password"},
     *        @OA\Property(property="email", type="string", example="user@example.com", description="User email address"),
     *        @OA\Property(property="password", type="string", example="SecurePass123!", description="User password")
     *    )
     * )
     * )
     */
    Flight::route('POST /login', function() {
        try {
            // Validate request data
            $data = validateRequestData(['email', 'password']);
            
            // Additional validation for login
            if (strlen($data['email']) > 255) {
                Flight::halt(400, json_encode(['error' => 'Email address is too long.']));
            }
            
            if (strlen($data['password']) > 255) {
                Flight::halt(400, json_encode(['error' => 'Password is too long.']));
            }
            
            $response = Flight::auth_service()->login($data);
            
            if ($response['success']) {
                Flight::json([
                    'message' => 'User logged in successfully',
                    'data' => $response['data']
                ]);
            } else {
                // Return 401 for authentication failures
                if (strpos($response['error'], 'Invalid email or password') !== false) {
                    Flight::halt(401, json_encode(['error' => $response['error']]));
                } elseif (strpos($response['error'], 'Too many') !== false) {
                    Flight::halt(429, json_encode(['error' => $response['error']]));
                } else {
                    Flight::halt(400, json_encode(['error' => $response['error']]));
                }
            }
        } catch (Exception $e) {
            error_log("Login route error: " . $e->getMessage());
            Flight::halt(500, json_encode(['error' => 'Login failed. Please try again.']));
        }
    });
    
    /**
     * @OA\Post(
     * path="/auth/check-email",
     * summary="Check if email already exists",
     * description="Validate email availability for registration",
     * tags={"auth"},
     * @OA\RequestBody(
     *    required=true,
     *    @OA\MediaType(
     *        mediaType="application/json",
     *        @OA\Schema(
     *            required={"email"},
     *            @OA\Property(
     *                property="email",
     *                type="string",
     *                example="user@example.com",
     *                description="Email to check"
     *            )
     *        )
     *    )
     * ),
     * @OA\Response(
     *    response=200,
     *    description="Email availability status",
     *    @OA\JsonContent(
     *        @OA\Property(property="exists", type="boolean", description="Whether email exists")
     *    )
     * )
     * )
     */
    Flight::route('POST /check-email', function() {
        try {
            $data = validateRequestData(['email']);
            
            if (strlen($data['email']) > 255) {
                Flight::halt(400, json_encode(['error' => 'Email address is too long.']));
            }
            
            $response = Flight::auth_service()->checkEmailExists($data['email']);
            Flight::json($response);
        } catch (Exception $e) {
            error_log("Email check route error: " . $e->getMessage());
            Flight::json(['exists' => false, 'error' => 'Unable to check email availability.']);
        }
    });
    
    /**
     * @OA\Get(
     * path="/auth/csrf-token",
     * summary="Get CSRF token",
     * description="Get CSRF token for form submissions",
     * tags={"auth"},
     * @OA\Response(
     *    response=200,
     *    description="CSRF token",
     *    @OA\JsonContent(
     *        @OA\Property(property="csrf_token", type="string", description="CSRF token")
     *    )
     * )
     * )
     */
    Flight::route('GET /csrf-token', function() {
        $token = generateCSRFToken();
        Flight::json(['csrf_token' => $token]);
    });
    
    /**
     * @OA\Post(
     * path="/auth/logout",
     * summary="Logout user",
     * description="Invalidate user session and token",
     * tags={"auth"},
     * security={{"bearerAuth": {}}},
     * @OA\Response(
     *    response=200,
     *    description="Logout successful"
     * )
     * )
     */
    Flight::route('POST /logout', function() {
        try {
            // Clear session data
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
            session_destroy();
                        
            Flight::json(['message' => 'Logout successful']);
        } catch (Exception $e) {
            error_log("Logout route error: " . $e->getMessage());
            Flight::halt(500, json_encode(['error' => 'Logout failed.']));
        }
    });
});