<?php
/**
 * Movie Routes
 * Implements role-based access control for movie operations
 */

/**
 * Public endpoints - No authentication required
 */

/**
 * @OA\Get(
 *     path="/movies",
 *     tags={"movies"},
 *     summary="Get all movies",
 *     @OA\Response(
 *         response=200,
 *         description="List of all movies in the database"
 *     )
 * )
 */
Flight::route('GET /movies', function(){
    Flight::json(Flight::movieService()->getAll());
});

/**
 * @OA\Get(
 *     path="/movies/{id}",
 *     tags={"movies"},
 *     summary="Get a specific movie by ID",
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         description="ID of the movie",
 *         @OA\Schema(type="integer", example=1)
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Movie with the specified ID"
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Movie not found"
 *     )
 * )
 */
Flight::route('GET /movies/@id', function($id){
    Flight::json(Flight::movieService()->getById($id));
});

/**
 * @OA\Get(
 *     path="/movies/genre/{genre}",
 *     tags={"movies"},
 *     summary="Get movies by genre",
 *     @OA\Parameter(
 *         name="genre",
 *         in="path",
 *         required=true,
 *         description="Genre to filter movies by",
 *         @OA\Schema(type="string", example="Action")
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="List of movies with the specified genre"
 *     )
 * )
 */
Flight::route('GET /movies/genre/@genre', function($genre){
    Flight::json(Flight::movieService()->getByGenre($genre));
});

/**
 * @OA\Get(
 *     path="/movies/year/{year}",
 *     tags={"movies"},
 *     summary="Get movies by release year",
 *     @OA\Parameter(
 *         name="year",
 *         in="path",
 *         required=true,
 *         description="Release year to filter movies by",
 *         @OA\Schema(type="integer", example=2023)
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="List of movies from the specified year"
 *     )
 * )
 */
Flight::route('GET /movies/year/@year', function($year){
    Flight::json(Flight::movieService()->getByReleaseYear($year));
});

/**
 * Protected endpoints - Admin only
 */

/**
 * @OA\Post(
 *     path="/movies",
 *     tags={"movies"},
 *     summary="Add a new movie (Admin only)",
 *     security={{"BearerAuth": {}}},
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"title", "description", "genre", "release_year", "rating"},
 *             @OA\Property(property="title", type="string", example="The Matrix"),
 *             @OA\Property(property="description", type="string", example="A computer hacker learns about the true nature of reality"),
 *             @OA\Property(property="genre", type="string", example="Sci-Fi"),
 *             @OA\Property(property="release_year", type="integer", example=1999),
 *             @OA\Property(property="rating", type="number", format="float", example=8.7),
 *             @OA\Property(property="image_url", type="string", example="https://example.com/movie.jpg")
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Movie created successfully"
 *     ),
 *     @OA\Response(
 *         response=400,
 *         description="Invalid input data"
 *     ),
 *     @OA\Response(
 *         response=403,
 *         description="Forbidden - Admin access required"
 *     )
 * )
 */
Flight::route('POST /movies', function(){
    try {
        // Get authentication token
        $token = Flight::request()->getHeader('Authentication') ?: 
                 Flight::request()->getHeader('Authorization');
        
        // Verify token first
        Flight::auth_middleware()->verifyToken($token);
        
        // Then check if user has admin role
        Flight::auth_middleware()->authorizeRole('admin');
        
        // If authorized, create movie
        $data = Flight::request()->data->getData();
        $result = Flight::movieService()->createMovie($data);
        
        if ($result) {
            Flight::json([
                'message' => 'Movie created successfully',
                'data' => $result
            ]);
        } else {
            Flight::halt(400, json_encode([
                'message' => 'Failed to create movie'
            ]));
        }
    } catch (\Exception $e) {
        // Determine appropriate status code
        $statusCode = 401; // Default to unauthorized
        if (strpos($e->getMessage(), 'Insufficient permissions') !== false || 
            strpos($e->getMessage(), 'Access denied') !== false) {
            $statusCode = 403; // Forbidden for role-based access issues
        }
        
        Flight::halt($statusCode, json_encode([
            'message' => $e->getMessage()
        ]));
    }
});

/**
 * @OA\Put(
 *     path="/movies/{id}",
 *     tags={"movies"},
 *     summary="Update movie by ID (Admin only)",
 *     security={{"BearerAuth": {}}},
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         description="ID of the movie to update",
 *         @OA\Schema(type="integer", example=1)
 *     ),
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"title", "description", "genre", "release_year", "rating"},
 *             @OA\Property(property="title", type="string", example="Updated Title"),
 *             @OA\Property(property="description", type="string", example="Updated description of the movie"),
 *             @OA\Property(property="genre", type="string", example="Drama"),
 *             @OA\Property(property="release_year", type="integer", example=2000),
 *             @OA\Property(property="rating", type="number", format="float", example=9.0),
 *             @OA\Property(property="image_url", type="string", example="https://example.com/updated.jpg")
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Movie updated successfully"
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Movie not found"
 *     ),
 *     @OA\Response(
 *         response=403,
 *         description="Forbidden - Admin access required"
 *     )
 * )
 */
Flight::route('PUT /movies/@id', function($id){
    try {
        // Get authentication token
        $token = Flight::request()->getHeader('Authentication') ?: 
                 Flight::request()->getHeader('Authorization');
        
        // Verify token first
        Flight::auth_middleware()->verifyToken($token);
        
        // Check if user is admin
        Flight::auth_middleware()->authorizeRole('admin');
        
        $data = Flight::request()->data->getData();
        $result = Flight::movieService()->updateMovie($id, $data);
        
        if ($result) {
            Flight::json([
                'message' => 'Movie updated successfully'
            ]);
        } else {
            Flight::halt(404, json_encode([
                'message' => 'Movie not found or update failed'
            ]));
        }
    } catch (\Exception $e) {
        // Determine appropriate status code
        $statusCode = 401; // Default to unauthorized
        if (strpos($e->getMessage(), 'Insufficient permissions') !== false || 
            strpos($e->getMessage(), 'Access denied') !== false) {
            $statusCode = 403; // Forbidden for role-based access issues
        }
        
        Flight::halt($statusCode, json_encode([
            'message' => $e->getMessage()
        ]));
    }
});

/**
 * @OA\Patch(
 *     path="/movies/{id}",
 *     tags={"movies"},
 *     summary="Partial update movie by ID (Admin only)",
 *     security={{"BearerAuth": {}}},
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         description="ID of the movie to update",
 *         @OA\Schema(type="integer", example=1)
 *     ),
 *     @OA\RequestBody(
 *         @OA\JsonContent(
 *             @OA\Property(property="title", type="string", example="New Title"),
 *             @OA\Property(property="description", type="string", example="New description"),
 *             @OA\Property(property="genre", type="string", example="Comedy"),
 *             @OA\Property(property="release_year", type="integer", example=2022),
 *             @OA\Property(property="rating", type="number", format="float", example=7.5),
 *             @OA\Property(property="image_url", type="string", example="https://example.com/new.jpg")
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Movie partially updated successfully"
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Movie not found"
 *     ),
 *     @OA\Response(
 *         response=403,
 *         description="Forbidden - Admin access required"
 *     )
 * )
 */
Flight::route('PATCH /movies/@id', function($id){
    try {
        // Get authentication token
        $token = Flight::request()->getHeader('Authentication') ?: 
                 Flight::request()->getHeader('Authorization');
        
        // Verify token first
        Flight::auth_middleware()->verifyToken($token);
        
        // Check if user is admin
        Flight::auth_middleware()->authorizeRole('admin');
        
        $data = Flight::request()->data->getData();
        $result = Flight::movieService()->partialUpdateMovie($id, $data);
        
        if ($result) {
            Flight::json([
                'message' => 'Movie partially updated successfully'
            ]);
        } else {
            Flight::halt(404, json_encode([
                'message' => 'Movie not found or update failed'
            ]));
        }
    } catch (\Exception $e) {
        // Determine appropriate status code
        $statusCode = 401; // Default to unauthorized
        if (strpos($e->getMessage(), 'Insufficient permissions') !== false || 
            strpos($e->getMessage(), 'Access denied') !== false) {
            $statusCode = 403; // Forbidden for role-based access issues
        }
        
        Flight::halt($statusCode, json_encode([
            'message' => $e->getMessage()
        ]));
    }
});

/**
 * @OA\Delete(
 *     path="/movies/{id}",
 *     tags={"movies"},
 *     summary="Delete movie by ID (Admin only)",
 *     security={{"BearerAuth": {}}},
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         description="ID of the movie to delete",
 *         @OA\Schema(type="integer", example=1)
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Movie deleted successfully"
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Movie not found"
 *     ),
 *     @OA\Response(
 *         response=403,
 *         description="Forbidden - Admin access required"
 *     )
 * )
 */
Flight::route('DELETE /movies/@id', function($id){
    try {
        // Get authentication token
        $token = Flight::request()->getHeader('Authentication') ?: 
                 Flight::request()->getHeader('Authorization');
        
        // Verify token first
        Flight::auth_middleware()->verifyToken($token);
        
        // Check if user is admin
        Flight::auth_middleware()->authorizeRole('admin');
        
        $result = Flight::movieService()->delete($id);
        
        if ($result) {
            Flight::json([
                'message' => 'Movie deleted successfully'
            ]);
        } else {
            Flight::halt(404, json_encode([
                'message' => 'Movie not found or delete failed'
            ]));
        }
    } catch (\Exception $e) {
        // Determine appropriate status code
        $statusCode = 401; // Default to unauthorized
        if (strpos($e->getMessage(), 'Insufficient permissions') !== false || 
            strpos($e->getMessage(), 'Access denied') !== false) {
            $statusCode = 403; // Forbidden for role-based access issues
        }
        
        Flight::halt($statusCode, json_encode([
            'message' => $e->getMessage()
        ]));
    }
});

/**
 * User Movies Endpoints - Available to authenticated users
 */

/**
 * @OA\Get(
 *     path="/user/movies/favorites",
 *     tags={"user", "movies"},
 *     summary="Get user's favorite movies",
 *     security={{"BearerAuth": {}}},
 *     @OA\Response(
 *         response=200,
 *         description="List of user's favorite movies"
 *     ),
 *     @OA\Response(
 *         response=401,
 *         description="Unauthorized"
 *     )
 * )
 */
Flight::route('GET /user/movies/favorites', function() {
    try {
        // Get authentication token
        $token = Flight::request()->getHeader('Authentication') ?: 
                 Flight::request()->getHeader('Authorization');
        
        // Verify token first
        Flight::auth_middleware()->verifyToken($token);
        
        // Allow both user and admin roles
        Flight::auth_middleware()->authorizeRole(['user', 'admin']);
        
        $user = Flight::get('user');
        $userId = is_object($user) ? $user->id : $user['id'];
        
        $favorites = Flight::favoriteService()->getUserFavoriteMovies($userId);
        
        Flight::json([
            'data' => $favorites
        ]);
    } catch (\Exception $e) {
        // Determine appropriate status code
        $statusCode = 401; // Default to unauthorized
        if (strpos($e->getMessage(), 'Insufficient permissions') !== false || 
            strpos($e->getMessage(), 'Access denied') !== false) {
            $statusCode = 403; // Forbidden for role-based access issues
        }
        
        Flight::halt($statusCode, json_encode([
            'message' => $e->getMessage()
        ]));
    }
});

/**
 * @OA\Post(
 *     path="/user/movies/favorites/{movie_id}",
 *     tags={"user", "movies"},
 *     summary="Add a movie to user's favorites",
 *     security={{"BearerAuth": {}}},
 *     @OA\Parameter(
 *         name="movie_id",
 *         in="path",
 *         required=true,
 *         description="ID of the movie to add to favorites",
 *         @OA\Schema(type="integer", example=1)
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Movie added to favorites successfully"
 *     ),
 *     @OA\Response(
 *         response=401,
 *         description="Unauthorized"
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="Failed to add movie to favorites"
 *     )
 * )
 */
Flight::route('POST /user/movies/favorites/@movie_id', function($movie_id) {
    try {
        // Get authentication token
        $token = Flight::request()->getHeader('Authentication') ?: 
                 Flight::request()->getHeader('Authorization');
        
        // Verify token first
        Flight::auth_middleware()->verifyToken($token);
        
        // Allow both user and admin roles
        Flight::auth_middleware()->authorizeRole(['user', 'admin']);
        
        $user = Flight::get('user');
        $userId = is_object($user) ? $user->id : $user['id'];
        
        $data = [
            'user_id' => $userId,
            'movie_id' => $movie_id,
            'added_at' => date('Y-m-d H:i:s')
        ];
        
        $result = Flight::favoriteService()->create($data);
        
        if ($result) {
            Flight::json([
                'message' => 'Movie added to favorites'
            ]);
        } else {
            Flight::halt(500, json_encode([
                'message' => 'Failed to add movie to favorites'
            ]));
        }
    } catch (\Exception $e) {
        // Determine appropriate status code
        $statusCode = 401; // Default to unauthorized
        if (strpos($e->getMessage(), 'Insufficient permissions') !== false || 
            strpos($e->getMessage(), 'Access denied') !== false) {
            $statusCode = 403; // Forbidden for role-based access issues
        }
        
        Flight::halt($statusCode, json_encode([
            'message' => $e->getMessage()
        ]));
    }
});

/**
 * @OA\Delete(
 *     path="/user/movies/favorites/{movie_id}",
 *     tags={"user", "movies"},
 *     summary="Remove a movie from user's favorites",
 *     security={{"BearerAuth": {}}},
 *     @OA\Parameter(
 *         name="movie_id",
 *         in="path",
 *         required=true,
 *         description="ID of the movie to remove from favorites",
 *         @OA\Schema(type="integer", example=1)
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Movie removed from favorites successfully"
 *     ),
 *     @OA\Response(
 *         response=401,
 *         description="Unauthorized"
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Favorite not found or remove failed"
 *     )
 * )
 */
Flight::route('DELETE /user/movies/favorites/@movie_id', function($movie_id) {
    try {
        // Get authentication token
        $token = Flight::request()->getHeader('Authentication') ?: 
                 Flight::request()->getHeader('Authorization');
        
        // Verify token first
        Flight::auth_middleware()->verifyToken($token);
        
        // Allow both user and admin roles
        Flight::auth_middleware()->authorizeRole(['user', 'admin']);
        
        $user = Flight::get('user');
        $userId = is_object($user) ? $user->id : $user['id'];
        
        $result = Flight::favoriteService()->removeFavorite($userId, $movie_id);
        
        if ($result) {
            Flight::json([
                'message' => 'Movie removed from favorites'
            ]);
        } else {
            Flight::halt(404, json_encode([
                'message' => 'Favorite not found or remove failed'
            ]));
        }
    } catch (\Exception $e) {
        // Determine appropriate status code
        $statusCode = 401; // Default to unauthorized
        if (strpos($e->getMessage(), 'Insufficient permissions') !== false || 
            strpos($e->getMessage(), 'Access denied') !== false) {
            $statusCode = 403; // Forbidden for role-based access issues
        }
        
        Flight::halt($statusCode, json_encode([
            'message' => $e->getMessage()
        ]));
    }
});