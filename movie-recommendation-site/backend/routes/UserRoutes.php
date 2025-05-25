<?php
/**
 * User Routes
 * Implements role-based access control for user operations
 */

/**
 * Auth endpoints (public routes - no authentication needed)
 */

/**
 * @OA\Post(
 *     path="/auth/register",
 *     tags={"auth"},
 *     summary="Register a new user",
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"name", "email", "password"},
 *             @OA\Property(property="name", type="string", example="John Doe"),
 *             @OA\Property(property="email", type="string", format="email", example="john@example.com"),
 *             @OA\Property(property="password", type="string", format="password", example="securepassword"),
 *             @OA\Property(property="role", type="string", example="user")
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="User registered successfully"
 *     ),
 *     @OA\Response(
 *         response=400,
 *         description="Invalid input data"
 *     )
 * )
 */
Flight::route('POST /auth/register', function(){
    $data = Flight::request()->data->getData();
    $response = Flight::userService()->register($data);
    
    if ($response['success']) {
        Flight::json([
            'message' => 'User registered successfully',
            'data' => $response['data']
        ]);
    } else {
        Flight::halt(400, json_encode([
            'message' => 'Registration failed: ' . $response['error']
        ]));
    }
});

/**
 * @OA\Post(
 *     path="/auth/login",
 *     tags={"auth"},
 *     summary="User login",
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"email", "password"},
 *             @OA\Property(property="email", type="string", format="email", example="john@example.com"),
 *             @OA\Property(property="password", type="string", format="password", example="securepassword")
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Login successful"
 *     ),
 *     @OA\Response(
 *         response=401,
 *         description="Unauthorized - Invalid credentials"
 *     )
 * )
 */
Flight::route('POST /auth/login', function(){
    $data = Flight::request()->data->getData();
    $response = Flight::userService()->login($data);
    
    if ($response['success']) {
        Flight::json([
            'message' => 'Login successful',
            'data' => $response['data']
        ]);
    } else {
        Flight::halt(401, json_encode([
            'message' => $response['error']
        ]));
    }
});

/**
 * Admin-only routes
 */
Flight::group('/admin/users', function() {
    /**
     * @OA\Get(
     *     path="/admin/users",
     *     tags={"admin", "users"},
     *     summary="Get all users (Admin only)",
     *     security={{"BearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="List of all users"
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden - Admin access required"
     *     )
     * )
     */
    Flight::route('GET /', function() {
        Flight::auth_middleware()->authorizeRole(Roles::ADMIN);
        
        $users = Flight::userService()->getAll();
        // Remove passwords from response
        foreach ($users as &$user) {
            unset($user['password']);
        }
        
        Flight::json(['data' => $users]);
    });
    
    /**
     * @OA\Get(
     *     path="/admin/users/{id}",
     *     tags={"admin", "users"},
     *     summary="Get user by ID (Admin only)",
     *     security={{"BearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the user",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="User with the specified ID"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="User not found"
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden - Admin access required"
     *     )
     * )
     */
    Flight::route('GET /@id', function($id) {
        Flight::auth_middleware()->authorizeRole(Roles::ADMIN);
        
        $user = Flight::userService()->getById($id);
        if ($user) {
            unset($user['password']);
            Flight::json(['data' => $user]);
        } else {
            Flight::halt(404, json_encode([
                'message' => 'User not found'
            ]));
        }
    });
    
    /**
     * @OA\Put(
     *     path="/admin/users/{id}",
     *     tags={"admin", "users"},
     *     summary="Update user (Admin only)",
     *     security={{"BearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the user to update",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", example="Updated Name"),
     *             @OA\Property(property="email", type="string", format="email", example="updated@example.com"),
     *             @OA\Property(property="password", type="string", format="password", example="newpassword"),
     *             @OA\Property(property="role", type="string", example="admin")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="User updated successfully"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="User not found"
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden - Admin access required"
     *     )
     * )
     */
    Flight::route('PUT /@id', function($id) {
        Flight::auth_middleware()->authorizeRole(Roles::ADMIN);
        
        $data = Flight::request()->data->getData();
        
        // If password is being updated, hash it
        if (!empty($data['password'])) {
            $data['password'] = password_hash($data['password'], PASSWORD_BCRYPT);
        }
        
        $result = Flight::userService()->update($id, $data);
        
        if ($result) {
            Flight::json([
                'message' => 'User updated successfully'
            ]);
        } else {
            Flight::halt(404, json_encode([
                'message' => 'User not found or update failed'
            ]));
        }
    });
    
    /**
     * @OA\Delete(
     *     path="/admin/users/{id}",
     *     tags={"admin", "users"},
     *     summary="Delete user (Admin only)",
     *     security={{"BearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the user to delete",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="User deleted successfully"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="User not found"
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden - Admin access required"
     *     )
     * )
     */
    Flight::route('DELETE /@id', function($id) {
        Flight::auth_middleware()->authorizeRole(Roles::ADMIN);
        
        $result = Flight::userService()->delete($id);
        
        if ($result) {
            Flight::json([
                'message' => 'User deleted successfully'
            ]);
        } else {
            Flight::halt(404, json_encode([
                'message' => 'User not found or delete failed'
            ]));
        }
    });
});

/**
 * User profile routes - accessible by authenticated users
 */
Flight::group('/users', function() {
    /**
     * @OA\Get(
     *     path="/users/profile",
     *     tags={"users"},
     *     summary="Get current user profile",
     *     security={{"BearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="Current user profile"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="User not found"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     )
     * )
     */
    Flight::route('GET /profile', function() {
        $user = Flight::get('user');
        
        // Get fresh user data from database
        $userData = Flight::userService()->getById($user->id);
        if ($userData) {
            unset($userData['password']);
            Flight::json(['data' => $userData]);
        } else {
            Flight::halt(404, json_encode([
                'message' => 'User not found'
            ]));
        }
    });
    
    /**
     * @OA\Put(
     *     path="/users/profile",
     *     tags={"users"},
     *     summary="Update current user profile",
     *     security={{"BearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", example="John Smith"),
     *             @OA\Property(property="email", type="string", format="email", example="john.smith@example.com"),
     *             @OA\Property(property="password", type="string", format="password", example="newpassword")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Profile updated successfully"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Failed to update profile"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     )
     * )
     */
    Flight::route('PUT /profile', function() {
        $user = Flight::get('user');
        $data = Flight::request()->data->getData();
        
        // Users should not be able to change their role
        if (isset($data['role']) && $user->role !== Roles::ADMIN) {
            unset($data['role']);
        }
        
        // If password is being updated, hash it
        if (!empty($data['password'])) {
            $data['password'] = password_hash($data['password'], PASSWORD_BCRYPT);
        }
        
        $result = Flight::userService()->update($user->id, $data);
        
        if ($result) {
            Flight::json([
                'message' => 'Profile updated successfully'
            ]);
        } else {
            Flight::halt(500, json_encode([
                'message' => 'Failed to update profile'
            ]));
        }
    });
});