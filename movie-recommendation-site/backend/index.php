<?php
require 'vendor/autoload.php';

// Set base URL based on your directory structure

Flight::set('flight.base_url', '/AdiBeca/Introduction-to-Web-programming/movie-recommendation-site/backend');

// Register services
require_once __DIR__ . '/services/MovieService.php';
require_once __DIR__ . '/services/UserService.php';
require_once __DIR__ . '/services/ReviewService.php';
require_once __DIR__ . '/services/FavoriteService.php';
require_once __DIR__ . '/services/RecommendationService.php';

Flight::register('movieService', 'MovieService');
Flight::register('userService', 'UserService');
Flight::register('reviewService', 'ReviewService');
Flight::register('favoriteService', 'FavoriteService');
Flight::register('recommendationService', 'RecommendationService');

// Test route to verify routing is working
Flight::route('/test', function(){
    echo 'Test route works!';
});

// Include routes
require_once __DIR__ . '/routes/MovieRoutes.php';
require_once __DIR__ . '/routes/UserRoutes.php';
require_once __DIR__ . '/routes/ReviewRoutes.php';
require_once __DIR__ . '/routes/FavoriteRoutes.php';
require_once __DIR__ . '/routes/RecommendationRoutes.php';

Flight::start();
?>