<?php

require_once 'BaseDao.php';

class ReviewDao extends BaseDao {
    public function __construct() {
        parent::__construct("reviews");
    }

    public function getAll() {
        $stmt = $this->connection->prepare("
            SELECT 
                r.id,
                r.user_id,
                r.movie_id,
                r.rating,
                r.comment,
                r.created_at,
                m.title as movie_title,
                m.image_url,
                u.name as user_name
            FROM reviews r
            JOIN movies m ON r.movie_id = m.id
            JOIN users u ON r.user_id = u.id
            ORDER BY r.created_at DESC
        ");
        $stmt->execute();
        return $stmt->fetchAll();
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
    

}