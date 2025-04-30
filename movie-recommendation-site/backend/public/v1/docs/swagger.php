<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require __DIR__ . '/../../../vendor/autoload.php';


if($_SERVER['SERVER_NAME'] == 'localhost' || $_SERVER['SERVER_NAME'] == '127.0.0.1'){
    define('BASE_URL', 'http://' . $_SERVER['SERVER_NAME'] . '/AdiBeca/Introduction-to-Web-programming/movie-recommendation-site/backend');
} else {
    define('BASE_URL', 'https://add-production-server-after-deployment/backend/');
}

// Scan route files for OpenAPI annotations
$openapi = \OpenApi\Generator::scan([
    __DIR__ . '/doc_setup.php',
    __DIR__ . '/../../../routes'
]);

header('Content-Type: application/json');
echo $openapi->toJson();