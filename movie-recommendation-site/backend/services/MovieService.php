<?php
require_once __DIR__ . '/BaseService.php';
require_once __DIR__ . '/../dao/MovieDao.php';

class MovieService extends BaseService {

    public function __construct() {
        parent::__construct(new MovieDao());
    }

    // Business logic for creating a movie with validation
    public function createMovie($data) {
        $this->validateMovieData($data);
        return $this->create($data);
    }

    public function getById($id) {
        return $this->dao->getById((int)$id); // Always just return, no throw
    }

    public function getByGenre($genre) {
        return $this->dao->getByGenre($genre);
    }

    public function getByReleaseYear($year) {
        return $this->dao->getByReleaseYear($year);
    }

    // Update movie with validation
    public function updateMovie($id, $data) {
        $this->validateRatingIfPresent($data);

        $existingMovie = $this->getById($id);
        if (!$existingMovie) {
            throw new Exception('Movie not found.');
        }

        return $this->update($id, $data);
    }

    // New method for partial update
    public function partialUpdateMovie($id, $data) {
        $existingMovie = $this->getById($id);
        if (!$existingMovie) {
            throw new Exception('Movie not found.');
        }

        // Partial updates only modify existing fields
        // Validate and update only the fields present in the request
        if (isset($data['rating'])) {
            $this->validateRatingIfPresent($data);
        }

        // Only pass the fields that are present in $data for update
        return $this->update($id, $data);
    }

    // 🔹 Private helper methods for validation 🔹

    private function validateMovieData($data) {
        if (empty($data['title'])) {
            throw new Exception('Movie title is required.');
        }

        if (empty($data['release_year'])) {
            throw new Exception('Release year is required.');
        }

        if (!is_numeric($data['release_year']) || $data['release_year'] < 1888 || $data['release_year'] > (date('Y') + 5)) {
            throw new Exception('Invalid release year.');
        }

        $this->validateRatingIfPresent($data);
    }

    private function validateRatingIfPresent($data) {
        if (isset($data['rating']) && ($data['rating'] < 0 || $data['rating'] > 10)) {
            throw new Exception('Rating must be between 0 and 10.');
        }
    }
}
?>
