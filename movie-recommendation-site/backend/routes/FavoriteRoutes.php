<?php
/**
 * @OA\Get(
 *     path="/favorites",
 *     tags={"favorites"},
 *     summary="Get all favorites",
 *     @OA\Response(
 *         response=200,
 *         description="List of all favorites in the database"
 *     )
 * )
 */
Flight::route('GET /favorites', function(){
    Flight::json(Flight::favoriteService()->getAll());
});

/**
 * @OA\Get(
 *     path="/users/{id}/favorites",
 *     tags={"favorites", "users"},
 *     summary="Get favorites by user ID",
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         description="ID of the user",
 *         @OA\Schema(type="integer", example=1)
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="List of favorites for the specified user"
 *     )
 * )
 */
Flight::route('GET /users/@id/favorites', function($id){
    Flight::json(Flight::favoriteService()->getByUserId($id));
});

/**
 * @OA\Post(
 *     path="/favorites",
 *     tags={"favorites"},
 *     summary="Add a movie to favorites",
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"user_id", "movie_id"},
 *             @OA\Property(property="user_id", type="integer", example=1),
 *             @OA\Property(property="movie_id", type="integer", example=2)
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Movie added to favorites successfully"
 *     ),
 *     @OA\Response(
 *         response=400,
 *         description="Invalid input data"
 *     )
 * )
 */
Flight::route('POST /favorites', function(){
    $data = Flight::request()->data->getData();
    Flight::json(Flight::favoriteService()->addFavorite($data));
});

/**
 * @OA\Delete(
 *     path="/users/{user_id}/favorites/{movie_id}",
 *     tags={"favorites"},
 *     summary="Remove a movie from favorites",
 *     @OA\Parameter(
 *         name="user_id",
 *         in="path",
 *         required=true,
 *         description="ID of the user",
 *         @OA\Schema(type="integer", example=1)
 *     ),
 *     @OA\Parameter(
 *         name="movie_id",
 *         in="path",
 *         required=true,
 *         description="ID of the movie",
 *         @OA\Schema(type="integer", example=2)
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Movie removed from favorites successfully"
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Favorite not found"
 *     )
 * )
 */
Flight::route('DELETE /users/@user_id/favorites/@movie_id', function($user_id, $movie_id){
    Flight::json(Flight::favoriteService()->removeFavorite($user_id, $movie_id));
});

/**
 * @OA\Get(
 *     path="/users/{user_id}/favorites/{movie_id}",
 *     tags={"favorites"},
 *     summary="Check if a movie is in favorites",
 *     @OA\Parameter(
 *         name="user_id",
 *         in="path",
 *         required=true,
 *         description="ID of the user",
 *         @OA\Schema(type="integer", example=1)
 *     ),
 *     @OA\Parameter(
 *         name="movie_id",
 *         in="path",
 *         required=true,
 *         description="ID of the movie",
 *         @OA\Schema(type="integer", example=2)
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Returns whether the movie is in the user's favorites",
 *         @OA\JsonContent(
 *             @OA\Property(property="is_favorite", type="boolean")
 *         )
 *     )
 * )
 */
Flight::route('GET /users/@user_id/favorites/@movie_id', function($user_id, $movie_id){
    $result = Flight::favoriteService()->isFavorite($user_id, $movie_id);
    Flight::json(['is_favorite' => $result]);
});
?>