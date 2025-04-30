<?php
require_once __DIR__ . '/BaseService.php';
require_once __DIR__ . '/../dao/ReviewDao.php';

class ReviewService extends BaseService {
    public function __construct() {
        $dao = new ReviewDao();
        parent::__construct($dao);
    }
    
    // Business logic for creating a review with validation
    public function createReview($data) {
        // Validate required fields
        if (empty($data['user_id']) || empty($data['movie_id']) || !isset($data['rating'])) {
            throw new Exception('User ID, Movie ID, and rating are required.');
        }
        
        // Validate rating range
        if ($data['rating'] < 0 || $data['rating'] > 10) {
            throw new Exception('Rating must be between 0 and 10.');
        }
        
        // Check if user has already reviewed this movie
        $existingReview = $this->dao->getByUserAndMovie($data['user_id'], $data['movie_id']);
        if ($existingReview) {
            throw new Exception('User has already reviewed this movie.');
        }
        
        return $this->create($data);
    }
    
    // Update review with validation
    public function updateReview($id, $data) {
        // Validate rating if provided
        if (isset($data['rating']) && ($data['rating'] < 0 || $data['rating'] > 10)) {
            throw new Exception('Rating must be between 0 and 10.');
        }
        
        return $this->update($id, $data);
    }
    
    // Get reviews by movie ID
    public function getByMovieId($movie_id) {
        return $this->dao->getByMovieId($movie_id);
    }
    
    // Get reviews by user ID
    public function getByUserId($user_id) {
        return $this->dao->getByUserId($user_id);
    }
    
    // Get review by user and movie
    public function getByUserAndMovie($user_id, $movie_id) {
        return $this->dao->getByUserAndMovie($user_id, $movie_id);
    }
}
?>