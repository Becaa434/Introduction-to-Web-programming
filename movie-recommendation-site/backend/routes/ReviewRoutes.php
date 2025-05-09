<?php
/**
 * @OA\Get(
 *     path="/reviews",
 *     tags={"reviews"},
 *     summary="Get all reviews",
 *     @OA\Response(
 *         response=200,
 *         description="List of all reviews"
 *     )
 * )
 */
Flight::route('GET /reviews', function(){
    Flight::json(Flight::reviewService()->getAll());
});

/**
 * @OA\Get(
 *     path="/reviews/{id}",
 *     tags={"reviews"},
 *     summary="Get review by ID",
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         description="ID of the review",
 *         @OA\Schema(type="integer", example=1)
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Review with the specified ID"
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Review not found"
 *     )
 * )
 */
Flight::route('GET /reviews/@id', function($id){
    Flight::json(Flight::reviewService()->getById($id));
});

/**
 * @OA\Get(
 *     path="/movies/{id}/reviews",
 *     tags={"reviews", "movies"},
 *     summary="Get reviews by movie ID",
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         description="ID of the movie",
 *         @OA\Schema(type="integer", example=1)
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="List of reviews for the specified movie"
 *     )
 * )
 */
Flight::route('GET /movies/@id/reviews', function($id){
    Flight::json(Flight::reviewService()->getByMovieId($id));
});

/**
 * @OA\Get(
 *     path="/users/{id}/reviews",
 *     tags={"reviews", "users"},
 *     summary="Get reviews by user ID",
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         description="ID of the user",
 *         @OA\Schema(type="integer", example=1)
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="List of reviews by the specified user"
 *     )
 * )
 */
Flight::route('GET /users/@id/reviews', function($id){
    Flight::json(Flight::reviewService()->getByUserId($id));
});

/**
 * @OA\Post(
 *     path="/reviews",
 *     tags={"reviews"},
 *     summary="Add a new review",
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"user_id", "movie_id", "rating", "comment"},
 *             @OA\Property(property="user_id", type="integer", example=1),
 *             @OA\Property(property="movie_id", type="integer", example=1),
 *             @OA\Property(property="rating", type="number", format="float", example=4.5),
 *             @OA\Property(property="comment", type="string", example="Great movie, highly recommend it!")
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Review created successfully"
 *     ),
 *     @OA\Response(
 *         response=400,
 *         description="Invalid input data"
 *     )
 * )
 */
Flight::route('POST /reviews', function(){
    $data = Flight::request()->data->getData();
    Flight::json(Flight::reviewService()->createReview($data));
});

/**
 * @OA\Put(
 *     path="/reviews/{id}",
 *     tags={"reviews"},
 *     summary="Update review",
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         description="ID of the review to update",
 *         @OA\Schema(type="integer", example=1)
 *     ),
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"user_id", "movie_id", "rating", "comment"},
 *             @OA\Property(property="user_id", type="integer", example=1),
 *             @OA\Property(property="movie_id", type="integer", example=1),
 *             @OA\Property(property="rating", type="number", format="float", example=3.5),
 *             @OA\Property(property="comment", type="string", example="Updated review comment")
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Review updated successfully"
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Review not found"
 *     )
 * )
 */
Flight::route('PUT /reviews/@id', function($id){
    $data = Flight::request()->data->getData();
    Flight::json(Flight::reviewService()->updateReview($id, $data));
});

/**
 * @OA\Delete(
 *     path="/reviews/{id}",
 *     tags={"reviews"},
 *     summary="Delete review",
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         description="ID of the review to delete",
 *         @OA\Schema(type="integer", example=1)
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Review deleted successfully"
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Review not found"
 *     )
 * )
 */
Flight::route('DELETE /reviews/@id', function($id){
    Flight::json(Flight::reviewService()->delete($id));
});
?>