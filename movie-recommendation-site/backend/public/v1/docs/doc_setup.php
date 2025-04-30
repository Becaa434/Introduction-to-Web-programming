<?php
/**
 * @OA\Info(
 *   title="Movie Recommendation API",
 *   description="API for movie recommendations, reviews, favorites, and user management",
 *   version="1.0",
 *   @OA\Contact(
 *     email="your.email@example.com",
 *     name="API Support"
 *   )
 * )
 */

/**
 * @OA\Server(
 *   url= "http://localhost/AdiBeca/Introduction-to-Web-programming/movie-recommendation-site/backend",
 *   description="Local Development API Server"
 * )
 */

/**
 * @OA\SecurityScheme(
 *   securityScheme="ApiKey",
 *   type="apiKey",
 *   in="header",
 *   name="Authentication"
 * )
 */