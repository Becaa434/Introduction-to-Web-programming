<?php
/**
 * @OA\Get(
 *     path="/recommendations",
 *     tags={"recommendations"},
 *     summary="Get all recommendations",
 *     @OA\Response(
 *         response=200,
 *         description="List of all recommendations in the system"
 *     )
 * )
 */
Flight::route('GET /recommendations', function(){
    Flight::json(Flight::recommendationService()->getAll());
});

/**
 * @OA\Get(
 *     path="/users/{id}/recommendations",
 *     tags={"recommendations", "users"},
 *     summary="Get recommendations for a user",
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         description="ID of the user",
 *         @OA\Schema(type="integer", example=1)
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="List of movie recommendations for the specified user"
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="User not found or no recommendations available"
 *     )
 * )
 */
Flight::route('GET /users/@id/recommendations', function($id){
    Flight::json(Flight::recommendationService()->getRecommendationsForUser($id));
});

/**
 * @OA\Post(
 *     path="/users/{id}/recommendations",
 *     tags={"recommendations", "users"},
 *     summary="Generate recommendations for a user",
 *     description="Generates new movie recommendations based on user preferences and history",
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         description="ID of the user",
 *         @OA\Schema(type="integer", example=1)
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="New recommendations generated successfully"
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="User not found"
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="Failed to generate recommendations"
 *     )
 * )
 */
Flight::route('POST /users/@id/recommendations', function($id){
    Flight::json(Flight::recommendationService()->generateRecommendationsForUser($id));
});

/**
 * @OA\Post(
 *     path="/recommendations",
 *     tags={"recommendations"},
 *     summary="Add a movie to recommendations (Admin only)",
 *     security={{"BearerAuth": {}}},
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"movie_id", "recommendation_score"},
 *             @OA\Property(property="movie_id", type="integer", example=1),
 *             @OA\Property(property="recommendation_score", type="number", format="float", example=8.0)
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Movie added to recommendations successfully"
 *     ),
 *     @OA\Response(
 *         response=403,
 *         description="Forbidden - Admin access required"
 *     )
 * )
 */
Flight::route('POST /recommendations', function(){
    try {
        // Get authentication token
        $token = Flight::request()->getHeader('Authentication') ?: 
                 Flight::request()->getHeader('Authorization');
        
        // Verify token first
        Flight::auth_middleware()->verifyToken($token);
        
        // Check if user is admin
        Flight::auth_middleware()->authorizeRole('admin');
        
        $data = Flight::request()->data->getData();
        $result = Flight::recommendationService()->create($data);
        
        if ($result) {
            Flight::json([
                'message' => 'Movie added to recommendations successfully',
                'data' => $result
            ]);
        } else {
            Flight::halt(400, json_encode([
                'message' => 'Failed to add recommendation'
            ]));
        }
    } catch (\Exception $e) {
        $statusCode = (strpos($e->getMessage(), 'Access denied') !== false) ? 403 : 401;
        Flight::halt($statusCode, json_encode([
            'message' => $e->getMessage()
        ]));
    }
});

/**
 * @OA\Delete(
 *     path="/recommendations/{id}",
 *     tags={"recommendations"},
 *     summary="Remove a recommendation (Admin only)",
 *     security={{"BearerAuth": {}}},
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         description="ID of the recommendation to remove",
 *         @OA\Schema(type="integer", example=1)
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Recommendation removed successfully"
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Recommendation not found"
 *     ),
 *     @OA\Response(
 *         response=403,
 *         description="Forbidden - Admin access required"
 *     )
 * )
 */
Flight::route('DELETE /recommendations/@id', function($id){
    try {
        // Get authentication token
        $token = Flight::request()->getHeader('Authentication') ?: 
                 Flight::request()->getHeader('Authorization');
        
        // Verify token first
        Flight::auth_middleware()->verifyToken($token);
        
        // Check if user is admin
        Flight::auth_middleware()->authorizeRole('admin');
        
        $result = Flight::recommendationService()->delete($id);
        
        if ($result) {
            Flight::json([
                'message' => 'Recommendation removed successfully'
            ]);
        } else {
            Flight::halt(404, json_encode([
                'message' => 'Recommendation not found'
            ]));
        }
    } catch (\Exception $e) {
        $statusCode = (strpos($e->getMessage(), 'Access denied') !== false) ? 403 : 401;
        Flight::halt($statusCode, json_encode([
            'message' => $e->getMessage()
        ]));
    }
});
?>