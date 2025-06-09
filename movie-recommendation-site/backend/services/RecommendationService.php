<?php
require_once __DIR__ . '/BaseService.php';
require_once __DIR__ . '/../dao/RecommendationDao.php';

class RecommendationService extends BaseService {
    public function __construct() {
        parent::__construct(new RecommendationDao());
    }

    
    // Get all recommendations 
    public function getAll() {
        return $this->dao->getAll();
    }
    
    
    // Get recommendations for a specific user
    public function getRecommendationsForUser($userId) {
        return $this->dao->getUserRecommendedMovies($userId);
    }
    
    // Generate recommendations for a user (placeholder for now)
    public function generateRecommendationsForUser($userId) {
        return ['message' => 'Recommendations generated successfully'];
    }
}
?>