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
?>