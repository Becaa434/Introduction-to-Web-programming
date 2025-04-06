<?php

require_once 'BaseDao.php';

class MovieDao extends BaseDao {
    public function __construct() {
        parent::__construct("movies");
    }
    
    public function getByTitle($title) {
        $stmt = $this->connection->prepare("SELECT * FROM movies WHERE title LIKE :title");
        $searchTerm = "%$title%";
        $stmt->bindParam(':title', $searchTerm);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    public function getByGenre($genre) {
        $stmt = $this->connection->prepare("SELECT * FROM movies WHERE genre LIKE :genre");
        $searchTerm = "%$genre%";
        $stmt->bindParam(':genre', $searchTerm);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    public function getTopRated($limit = 10) {
        $stmt = $this->connection->prepare("SELECT * FROM movies ORDER BY rating DESC LIMIT :limit");
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    public function getByReleaseYear($year) {
        $stmt = $this->connection->prepare("SELECT * FROM movies WHERE release_year = :year");
        $stmt->bindParam(':year', $year);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}