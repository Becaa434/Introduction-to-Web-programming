<?php
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
 * @OA\Post(
 *     path="/movies",
 *     tags={"movies"},
 *     summary="Add a new movie",
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
 *     )
 * )
 */
Flight::route('POST /movies', function(){
    $data = Flight::request()->data->getData();
    Flight::json(Flight::movieService()->createMovie($data));
});

/**
 * @OA\Put(
 *     path="/movies/{id}",
 *     tags={"movies"},
 *     summary="Update movie by ID",
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
 *     )
 * )
 */
Flight::route('PUT /movies/@id', function($id){
    $data = Flight::request()->data->getData();
    Flight::json(Flight::movieService()->updateMovie($id, $data));
});

/**
 * @OA\Patch(
 *     path="/movies/{id}",
 *     tags={"movies"},
 *     summary="Partial update movie by ID",
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
 *     )
 * )
 */
Flight::route('PATCH /movies/@id', function($id){
    $data = Flight::request()->data->getData();
    Flight::json(Flight::movieService()->partialUpdateMovie($id, $data));
});

/**
 * @OA\Delete(
 *     path="/movies/{id}",
 *     tags={"movies"},
 *     summary="Delete movie by ID",
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
 *     )
 * )
 */
Flight::route('DELETE /movies/@id', function($id){
    Flight::json(Flight::movieService()->delete($id));
});
?>