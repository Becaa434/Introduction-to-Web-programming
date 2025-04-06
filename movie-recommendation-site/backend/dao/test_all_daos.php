<?php

require_once 'BaseDao.php';
require_once 'UserDao.php';
require_once 'MovieDao.php';  
require_once 'ReviewDao.php';
require_once 'FavoriteDao.php';
require_once 'RecommendationDao.php';

// Test all DAO classes
echo "=== Testing User DAO ===\n";
$userDao = new UserDao();

// Create a new user
$userId = null;
$userEmail = 'test_user_' . time() . '@example.com';
$result = $userDao->insert([
    'name' => 'Test User',
    'email' => $userEmail,
    'password' => password_hash('password123', PASSWORD_DEFAULT),
    'role' => 'user'
]);
echo "Insert user result: " . ($result ? "Success" : "Failed") . "\n";

// Get the created user by email
$user = $userDao->getByEmail($userEmail);
if ($user) {
    $userId = $user['id'];
    echo "User created with ID: " . $userId . "\n";
    
    // Update the user
    $result = $userDao->update($userId, ['name' => 'Updated Test User']);
    echo "Update user result: " . ($result ? "Success" : "Failed") . "\n";
    
    // Get the updated user
    $updatedUser = $userDao->getById($userId);
    echo "Updated user name: " . $updatedUser['name'] . "\n";
} else {
    echo "Failed to retrieve created user\n";
}

echo "\n=== Testing Movie DAO ===\n";
$movieDao = new MovieDao();

// Create a new movie
$movieId = null;
$result = $movieDao->insert([
    'title' => 'Test Movie',
    'description' => 'This is a test movie for DAO testing',
    'genre' => 'Test',
    'release_year' => 2023,
    'rating' => 7.5,
    'image_url' => 'test_movie.jpg'
]);
echo "Insert movie result: " . ($result ? "Success" : "Failed") . "\n";

// Get all movies
$movies = $movieDao->getAll();
echo "Total movies: " . count($movies) . "\n";

foreach ($movies as $movie) {
    if ($movie['title'] == 'Test Movie') {
        $movieId = $movie['id'];
        echo "Found test movie with ID: " . $movieId . "\n";
        break;
    }
}

if ($movieId) {
    // Update the movie
    $result = $movieDao->update($movieId, ['rating' => 8.0, 'genre' => 'Updated Test']);
    echo "Update movie result: " . ($result ? "Success" : "Failed") . "\n";
    
    // Get the updated movie
    $updatedMovie = $movieDao->getById($movieId);
    echo "Updated movie rating: " . $updatedMovie['rating'] . ", genre: " . $updatedMovie['genre'] . "\n";
    
    // Get movies by genre
    $testMovies = $movieDao->getByGenre('Test');
    echo "Movies with 'Test' in genre: " . count($testMovies) . "\n";
}

echo "\n=== Testing Review DAO ===\n";
$reviewDao = new ReviewDao();

// Create a new review if we have user and movie IDs
if ($userId && $movieId) {
    $result = $reviewDao->insert([
        'user_id' => $userId,
        'movie_id' => $movieId,
        'rating' => 8,
        'comment' => 'This is a test review'
    ]);
    echo "Insert review result: " . ($result ? "Success" : "Failed") . "\n";
    
    // Get reviews by user
    $userReviews = $reviewDao->getByUserId($userId);
    echo "Reviews by test user: " . count($userReviews) . "\n";
    
    // Get reviews by movie
    $movieReviews = $reviewDao->getByMovieId($movieId);
    echo "Reviews for test movie: " . count($movieReviews) . "\n";
    
    if (count($movieReviews) > 0) {
        $reviewId = $movieReviews[0]['id'];
        
        // Update the review
        $result = $reviewDao->update($reviewId, ['rating' => 9, 'comment' => 'Updated test review']);
        echo "Update review result: " . ($result ? "Success" : "Failed") . "\n";
        
        // Get the updated review
        $updatedReview = $reviewDao->getById($reviewId);
        echo "Updated review rating: " . $updatedReview['rating'] . ", comment: " . $updatedReview['comment'] . "\n";
    }
}

echo "\n=== Testing Favorite DAO ===\n";
$favoriteDao = new FavoriteDao();

// Create a new favorite if we have user and movie IDs
if ($userId && $movieId) {
    $result = $favoriteDao->insert([
        'user_id' => $userId,
        'movie_id' => $movieId
    ]);
    echo "Insert favorite result: " . ($result ? "Success" : "Failed") . "\n";
    
    // Check if movie is favorited
    $isFavorite = $favoriteDao->checkFavorite($userId, $movieId);
    echo "Is movie favorited: " . ($isFavorite ? "Yes" : "No") . "\n";
    
    // Get user's favorite movies
    $favoriteMovies = $favoriteDao->getUserFavoriteMovies($userId);
    echo "User's favorite movies: " . count($favoriteMovies) . "\n";
    
    // Get favorites by user
    $userFavorites = $favoriteDao->getByUserId($userId);
    if (count($userFavorites) > 0) {
        $favoriteId = $userFavorites[0]['id'];
        
        // Delete the favorite
        $result = $favoriteDao->delete($favoriteId);
        echo "Delete favorite result: " . ($result ? "Success" : "Failed") . "\n";
        
        // Check if still favorited
        $isStillFavorite = $favoriteDao->checkFavorite($userId, $movieId);
        echo "Is movie still favorited: " . ($isStillFavorite ? "Yes" : "No") . "\n";
    }
}

echo "\n=== Testing Recommendation DAO ===\n";
$recommendationDao = new RecommendationDao();

// Create a new recommendation if we have user and movie IDs
if ($userId && $movieId) {
    $result = $recommendationDao->insert([
        'user_id' => $userId,
        'movie_id' => $movieId,
        'recommendation_score' => 85.5
    ]);
    echo "Insert recommendation result: " . ($result ? "Success" : "Failed") . "\n";
    
    // Get recommendations by user
    $userRecommendations = $recommendationDao->getByUserId($userId);
    echo "Recommendations for test user: " . count($userRecommendations) . "\n";
    
    // Get user's recommended movies
    $recommendedMovies = $recommendationDao->getUserRecommendedMovies($userId);
    echo "User's recommended movies: " . count($recommendedMovies) . "\n";
    
    if (count($userRecommendations) > 0) {
        $recId = $userRecommendations[0]['id'];
        
        // Update the recommendation
        $result = $recommendationDao->update($recId, ['recommendation_score' => 92.7]);
        echo "Update recommendation result: " . ($result ? "Success" : "Failed") . "\n";
        
        // Get the updated recommendation
        $updatedRec = $recommendationDao->getById($recId);
        echo "Updated recommendation score: " . $updatedRec['recommendation_score'] . "\n";
    }
    
    // Delete all user recommendations
    $result = $recommendationDao->deleteUserRecommendations($userId);
    echo "Delete user recommendations result: " . ($result ? "Success" : "Failed") . "\n";
}

// Clean up - delete test movie and user if needed
if ($movieId) {
    $movieDao->delete($movieId);
    echo "\nTest movie deleted\n";
}

if ($userId) {
    $userDao->delete($userId);
    echo "Test user deleted\n";
}

echo "\nAll DAO tests completed.\n";
?>