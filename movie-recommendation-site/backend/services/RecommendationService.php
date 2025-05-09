<?php
require_once __DIR__ . '/BaseService.php';
require_once __DIR__ . '/../dao/RecommendationDao.php';
require_once __DIR__ . '/MovieService.php';
require_once __DIR__ . '/ReviewService.php';

class RecommendationService extends BaseService {
    private $movieService;
    private $reviewService;
    
    public function __construct() {
        $dao = new RecommendationDao();
        parent::__construct($dao);
        
        $this->movieService = new MovieService();
        $this->reviewService = new ReviewService();
    }
    
    // Generate recommendations for a user based on their reviews and favorites
    public function generateRecommendationsForUser($user_id) {
        // Get user's reviews to understand preferences
        $userReviews = $this->reviewService->getByUserId($user_id);
        
        // Get genres the user tends to rate highly
        $preferredGenres = $this->getPreferredGenres($userReviews);
        
        // Get recommended movies based on preferred genres
        $recommendations = [];
        foreach ($preferredGenres as $genre) {
            $moviesInGenre = $this->movieService->getByGenre($genre);
            
            // Filter out already reviewed movies
            foreach ($moviesInGenre as $movie) {
                $alreadyReviewed = false;
                foreach ($userReviews as $review) {
                    if ($review['movie_id'] == $movie['id']) {
                        $alreadyReviewed = true;
                        break;
                    }
                }
                
                if (!$alreadyReviewed) {
                    $recommendations[] = $movie;
                }
            }
        }
        
        // Store recommendations in the database
        foreach ($recommendations as $movie) {
            $this->create([
                'user_id' => $user_id,
                'movie_id' => $movie['id'],
                'score' => $this->calculateRecommendationScore($user_id, $movie['id']),
                'timestamp' => date('Y-m-d H:i:s')
            ]);
        }
        
        return $recommendations;
    }
    
    // Get recommendations for a specific user
    public function getRecommendationsForUser($user_id) {
        return $this->dao->getByUserId($user_id);
    }
    
    // Helper method to determine preferred genres from reviews
    private function getPreferredGenres($userReviews) {
        $genreRatings = [];
        $genreCounts = [];
        
        foreach ($userReviews as $review) {
            $movie = $this->movieService->getById($review['movie_id']);
            if (!empty($movie['genre'])) {
                $genres = explode(',', $movie['genre']);
                foreach ($genres as $genre) {
                    $genre = trim($genre);
                    if (!isset($genreRatings[$genre])) {
                        $genreRatings[$genre] = 0;
                        $genreCounts[$genre] = 0;
                    }
                    $genreRatings[$genre] += $review['rating'];
                    $genreCounts[$genre]++;
                }
            }
        }
        
        // Calculate average rating per genre
        $genreAverages = [];
        foreach ($genreRatings as $genre => $totalRating) {
            if ($genreCounts[$genre] > 0) {
                $genreAverages[$genre] = $totalRating / $genreCounts[$genre];
            }
        }
        
        // Sort by average rating and take top genres
        arsort($genreAverages);
        return array_slice(array_keys($genreAverages), 0, 3); // Return top 3 genres
    }
    
    // Calculate recommendation score
    private function calculateRecommendationScore($user_id, $movie_id) {
        // This is a simplified version - in a real system this would be more complex
        $movie = $this->movieService->getById($movie_id);
        $userReviews = $this->reviewService->getByUserId($user_id);
        
        $score = 0;
        $relevantReviewCount = 0;
        
        // Calculate score based on how user rated similar movies
        foreach ($userReviews as $review) {
            $reviewedMovie = $this->movieService->getById($review['movie_id']);
            
            // Check if genres match
            $movieGenres = explode(',', $movie['genre']);
            $reviewedGenres = explode(',', $reviewedMovie['genre']);
            
            $matchingGenres = array_intersect($movieGenres, $reviewedGenres);
            if (count($matchingGenres) > 0) {
                $score += $review['rating'] * (count($matchingGenres) / count($movieGenres));
                $relevantReviewCount++;
            }
        }
        
        if ($relevantReviewCount > 0) {
            return $score / $relevantReviewCount;
        }
        
        // Return a default score based on overall movie rating if no relevant reviews
        return isset($movie['rating']) ? $movie['rating'] / 2 : 5;
    }
}
?>