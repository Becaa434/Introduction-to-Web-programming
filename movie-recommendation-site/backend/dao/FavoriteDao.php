<?php

require_once 'BaseDao.php';

class FavoriteDao extends BaseDao {
    public function __construct() {
        parent::__construct("favorites");
    }
    
    public function getByUserId($userId) {
        $stmt = $this->connection->prepare("SELECT * FROM favorites WHERE user_id = :user_id");
        $stmt->bindParam(':user_id', $userId);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    public function getByMovieId($movieId) {
        $stmt = $this->connection->prepare("SELECT * FROM favorites WHERE movie_id = :movie_id");
        $stmt->bindParam(':movie_id', $movieId);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    public function checkFavorite($userId, $movieId) {
        $stmt = $this->connection->prepare("SELECT * FROM favorites WHERE user_id = :user_id AND movie_id = :movie_id");
        $stmt->bindParam(':user_id', $userId);
        $stmt->bindParam(':movie_id', $movieId);
        $stmt->execute();
        return $stmt->rowCount() > 0;
    }
    
    public function getUserFavoriteMovies($userId) {
        $stmt = $this->connection->prepare("
            SELECT m.* FROM movies m
            JOIN favorites f ON m.id = f.movie_id
            WHERE f.user_id = :user_id
            ORDER BY f.added_at DESC
        ");
        $stmt->bindParam(':user_id', $userId);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}