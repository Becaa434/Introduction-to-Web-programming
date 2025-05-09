<?php
require_once __DIR__ . '/BaseService.php';
require_once __DIR__ . '/../dao/FavoriteDao.php';

class FavoriteService extends BaseService {
    public function __construct() {
        $dao = new FavoriteDao();
        parent::__construct($dao);
    }
    
    // Business logic for adding a favorite
    public function addFavorite($data) {
        // Validate required fields
        if (empty($data['user_id']) || empty($data['movie_id'])) {
            throw new Exception('User ID and Movie ID are required.');
        }
        
        // Check if already in favorites
        $existingFavorite = $this->dao->getByUserAndMovie($data['user_id'], $data['movie_id']);
        if ($existingFavorite) {
            throw new Exception('Movie is already in user favorites.');
        }
        
        return $this->create($data);
    }
    
    // Remove from favorites
    public function removeFavorite($user_id, $movie_id) {
        $favorite = $this->dao->getByUserAndMovie($user_id, $movie_id);
        if (!$favorite) {
            throw new Exception('Favorite not found.');
        }
        
        return $this->delete($favorite['id']);
    }
    
    // Get favorites by user ID
    public function getByUserId($user_id) {
        return $this->dao->getByUserId($user_id);
    }
    
    // Check if a movie is in user's favorites
    public function isFavorite($user_id, $movie_id) {
        $favorite = $this->dao->getByUserAndMovie($user_id, $movie_id);
        return !empty($favorite);
    }
}
?>