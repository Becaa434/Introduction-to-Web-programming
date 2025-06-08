<?php
require 'vendor/autoload.php';
require 'dao/config.php';
require 'middleware/AuthMiddleware.php';
require 'data/Roles.php';

// Set base URL based on the current script directory
Flight::set('flight.base_url', '/AdiBeca/Introduction-to-Web-programming/movie-recommendation-site/backend');

// Include all service files
require 'services/UserService.php';
require 'services/MovieService.php';
require 'services/ReviewService.php';
require 'services/FavoriteService.php';
require 'services/RecommendationService.php';
require 'services/AuthService.php';

// Use JWT libraries
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

// Enable error reporting for development
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Register services
Flight::register('movieService', 'MovieService');
Flight::register('userService', 'UserService');
Flight::register('reviewService', 'ReviewService');
Flight::register('favoriteService', 'FavoriteService');
Flight::register('recommendationService', 'RecommendationService');
Flight::register('auth_service', 'AuthService');
Flight::register('auth_middleware', 'AuthMiddleware');

// Enable CORS for development
Flight::map('cors', function() {
    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, PATCH, OPTIONS");
    header("Access-Control-Allow-Headers: Content-Type, Authorization, Authentication");
   
    if (Flight::request()->method === 'OPTIONS') {
        Flight::halt(200);
    }
});

// Apply CORS to all routes
Flight::before('start', function(&$params, &$output) {
    Flight::cors();
});

// Global middleware for token verification
Flight::route('/*', function() {
    // Skip authentication for public routes
    $publicRoutes = [
        '/auth/login',
        '/auth/register',
        '/movies', // Allow public access to movies
        '/recommendations',
        '/public',
        '/docs',
        '/swagger',
        '/test' 
    ];
   
    $currentPath = Flight::request()->url;
   
    // Check if the current path starts with any of the public routes
    foreach ($publicRoutes as $route) {
        if (strpos($currentPath, $route) === 0) {
            return true; // Skip authentication for public routes
        }
    }
   
    // Handle OPTIONS request (preflight CORS)
    if (Flight::request()->method === 'OPTIONS') {
        return true;
    }
   
    // Verify token for protected routes
    try {
        $token = Flight::request()->getHeader('Authentication') ?: 
                Flight::request()->getHeader('Authorization');
        
        if (Flight::auth_middleware()->verifyToken($token)) {
            return true;
        }
    } catch (\Exception $e) {
        Flight::halt(401, json_encode([
            'message' => 'Authentication failed: ' . $e->getMessage()
        ]));
    }
});

// Include all route files
require_once 'routes/AuthRoutes.php';
require_once 'routes/MovieRoutes.php';
require_once 'routes/UserRoutes.php';
require_once 'routes/ReviewRoutes.php';
require_once 'routes/FavoriteRoutes.php';
require_once 'routes/RecommendationRoutes.php';



// Start Flight
Flight::start();