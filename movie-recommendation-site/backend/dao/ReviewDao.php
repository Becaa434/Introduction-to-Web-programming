<?php

require_once 'BaseDao.php';

class ReviewDao extends BaseDao {
    public function __construct() {
        parent::__construct("reviews");
    }
    
    public function getByUserId($userId) {
        $stmt = $this->connection->prepare("SELECT * FROM reviews WHERE user_id = :user_id");
        $stmt->bindParam(':user_id', $userId);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    public function getByMovieId($movieId) {
        $stmt = $this->connection->prepare("SELECT * FROM reviews WHERE movie_id = :movie_id");
        $stmt->bindParam(':movie_id', $movieId);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    public function getByUserAndMovie($userId, $movieId) {
        $stmt = $this->connection->prepare("SELECT * FROM reviews WHERE user_id = :user_id AND movie_id = :movie_id");
        $stmt->bindParam(':user_id', $userId);
        $stmt->bindParam(':movie_id', $movieId);
        $stmt->execute();
        return $stmt->fetch();
    }
    
    public function getRecentReviews($limit = 10) {
        $stmt = $this->connection->prepare("SELECT * FROM reviews ORDER BY created_at DESC LIMIT :limit");
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}