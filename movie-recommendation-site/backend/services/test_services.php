<?php
// Import all service classes
require_once __DIR__ . '/MovieService.php';
require_once __DIR__ . '/UserService.php';
require_once __DIR__ . '/ReviewService.php';
require_once __DIR__ . '/FavoriteService.php';
require_once __DIR__ . '/RecommendationService.php';

// Function to display test results
function displayTestResult($test, $result, $message = '') {
    echo "<div style='margin: 10px; padding: 10px; border: 1px solid " . ($result ? "green" : "red") . ";'>";
    echo "<strong>" . ($result ? "✓ PASS: " : "✗ FAIL: ") . $test . "</strong>";
    if (!empty($message)) {
        echo "<p>" . $message . "</p>";
    }
    echo "</div>";
}

// Test Movie Service
try {
    echo "<h2>Testing MovieService</h2>";
    
    $movieService = new MovieService();
    
    // Test getAll
    $allMovies = $movieService->getAll();
    displayTestResult("Get All Movies", is_array($allMovies), "Retrieved " . count($allMovies) . " movies");
    
    // Test create with validation
    try {
        $invalidMovie = [
            'title' => 'Test Movie',
            'release_year' => 3000 // Invalid release year
        ];
        $movieService->createMovie($invalidMovie);
        displayTestResult("Movie Validation - Invalid Year", false, "Should have rejected invalid release year");
    } catch (Exception $e) {
        displayTestResult("Movie Validation - Invalid Year", true, "Correctly rejected: " . $e->getMessage());
    }
    
    // Test valid movie creation
    $validMovie = [
        'title' => 'Test Movie',
        'description' => 'A test movie description',
        'genre' => 'Action,Adventure',
        'release_year' => 2023,
        'rating' => 8.5,
        'image_url' => 'https://example.com/image.jpg'
    ];
    
    $newMovieId = $movieService->createMovie($validMovie);
    displayTestResult("Create Valid Movie", $newMovieId > 0, "Created movie with ID: " . $newMovieId);
    
    // Test getById
    $retrievedMovie = $movieService->getById($newMovieId);
    displayTestResult("Get Movie By ID", $retrievedMovie && $retrievedMovie['title'] === 'Test Movie', 
                     "Retrieved movie: " . ($retrievedMovie ? $retrievedMovie['title'] : "none"));
    
    // Test update
    $updateData = ['rating' => 9.0];
    $updateResult = $movieService->updateMovie($newMovieId, $updateData);
    displayTestResult("Update Movie", $updateResult, "Updated rating to 9.0");
    
    // Verify update
    $updatedMovie = $movieService->getById($newMovieId);
    displayTestResult("Verify Update", $updatedMovie && $updatedMovie['rating'] == 9.0, 
                     "New rating: " . ($updatedMovie ? $updatedMovie['rating'] : "unknown"));
    
    // Test delete
    $deleteResult = $movieService->delete($newMovieId);
    displayTestResult("Delete Movie", $deleteResult, "Deleted movie with ID: " . $newMovieId);
    
    // Verify deletion
    $deletedMovie = $movieService->getById($newMovieId);
    displayTestResult("Verify Deletion", empty($deletedMovie), 
                     "Movie should no longer exist: " . (empty($deletedMovie) ? "confirmed" : "failed"));
    
} catch (Exception $e) {
    displayTestResult("MovieService Tests", false, "Exception: " . $e->getMessage());
}

echo "<h2>Test Completed</h2>";
?>