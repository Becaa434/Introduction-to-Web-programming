<?php

require_once 'BaseDao.php';

class RecommendationDao extends BaseDao {
    public function __construct() {
        parent::__construct("recommendations");
    }
    
    public function getByUserId($userId) {
        $stmt = $this->connection->prepare("SELECT * FROM recommendations WHERE user_id = :user_id");
        $stmt->bindParam(':user_id', $userId);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    public function getByMovieId($movieId) {
        $stmt = $this->connection->prepare("SELECT * FROM recommendations WHERE movie_id = :movie_id");
        $stmt->bindParam(':movie_id', $movieId);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    public function getUserRecommendedMovies($userId) {
        $stmt = $this->connection->prepare("
            SELECT m.*, r.recommendation_score FROM movies m
            JOIN recommendations r ON m.id = r.movie_id
            WHERE r.user_id = :user_id
            ORDER BY r.recommendation_score DESC
        ");
        $stmt->bindParam(':user_id', $userId);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    public function deleteUserRecommendations($userId) {
        $stmt = $this->connection->prepare("DELETE FROM recommendations WHERE user_id = :user_id");
        $stmt->bindParam(':user_id', $userId);
        return $stmt->execute();
    }
}