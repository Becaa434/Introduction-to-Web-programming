var FavoriteService = {
    init: function () {
        console.log("FavoriteService.init() called");
        
        // Load favorites when the favorites page is accessed
        if (window.location.hash === '#favorites' || $('#favoritesSection').length > 0) {
            FavoriteService.loadFavorites();
        }
    },
    
    loadFavorites: function() {
        console.log("Loading user favorites from API...");
        
        // Check if user is logged in
        if (!UserService.isLoggedIn()) {
            $('#favoritesSection').html(`
                <div class="col-12 text-center">
                    <p>Please login to view your favorites.</p>
                    <a href="#login" class="btn btn-primary">Login</a>
                </div>
            `);
            return;
        }
        
        // Show loading
        $('#favoritesSection').html(`
            <div class="col-12 text-center">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading favorites...</span>
                </div>
                <p class="mt-2">Loading your favorite movies...</p>
            </div>
        `);
        
        // Get user favorites
        RestClient.get("user/movies/favorites", function(data) {
            console.log("Favorites loaded:", data);
            FavoriteService.renderFavorites(data.data || data);
        }, function(error) {
            console.error("Error loading favorites:", error);
            toastr.error("Failed to load favorites");
            $('#favoritesSection').html(`
                <div class="col-12 text-center">
                    <p class="text-danger">Failed to load favorites</p>
                </div>
            `);
        });
    },
    
    renderFavorites: function(movies) {
        const favoritesSection = $('#favoritesSection');
        
        if (!movies || movies.length === 0) {
            favoritesSection.html(`
                <div class="col-12 text-center">
                    <h4>No favorites yet</h4>
                    <p>Start adding movies to your favorites from the <a href="#movies">Movies page</a>!</p>
                </div>
            `);
            return;
        }
        
        let favoritesHtml = `
            <div class="col-12 mb-4">
                <h3>Your Favorite Movies (${movies.length})</h3>
            </div>
        `;
        
        movies.forEach(movie => {
            favoritesHtml += `
                <div class="col-md-3 mb-4">
                    <div class="card h-100 shadow-sm movie-card" onclick="FavoriteService.openMovieDetails(${movie.id})" style="cursor: pointer;">
                        <div class="position-relative">
                            <img src="${movie.image_url || 'assets/img/default-movie.jpg'}" 
                                 class="card-img-top" alt="${movie.title}" 
                                 style="height: 400px; object-fit: cover;">
                            <button class="btn btn-danger btn-sm position-absolute top-0 end-0 m-2" 
                                    onclick="event.stopPropagation(); FavoriteService.removeFavorite(${movie.id}, '${movie.title.replace(/'/g, "\\'")}');">
                                Remove
                            </button>
                        </div>
                        <div class="card-body text-center">
                            <h5 class="card-title">${movie.title}</h5>
                        </div>
                    </div>
                </div>
            `;
        });
        
        favoritesSection.html(favoritesHtml);
    },
    
    openMovieDetails: function(movieId) {
        // Reuse the movie details modal from MovieService
        if (typeof MovieService !== 'undefined' && MovieService.openMovieDetails) {
            MovieService.openMovieDetails(movieId);
        }
    },
    
    addToFavorites: function(movieId, movieTitle) {
        if (!UserService.isLoggedIn()) {
            toastr.error("Please login to add favorites");
            return;
        }
        
        console.log("Adding movie to favorites:", movieId);
        
        RestClient.post(`user/movies/favorites/${movieId}`, {}, function(response) {
            console.log("Successfully added to favorites:", response);
            toastr.success(`"${movieTitle}" added to favorites!`);
        }, function(error) {
            console.error("Error adding to favorites:", error);
            
            if (error.status === 403 || error.status === 401) {
                toastr.error("Please login again");
                UserService.logout();
            } else if (error.status === 500) {
                toastr.warning("Movie might already be in favorites");
            } else {
                toastr.error("Failed to add to favorites");
            }
        });
    },
    
    removeFavorite: function(movieId, movieTitle) {
        if (!UserService.isLoggedIn()) {
            toastr.error("Please login first");
            return;
        }
        
        if (confirm(`Remove "${movieTitle}" from favorites?`)) {
            RestClient.delete(`user/movies/favorites/${movieId}`, null, function(response) {
                toastr.success(`"${movieTitle}" removed from favorites`);
                FavoriteService.loadFavorites(); // Reload the favorites page
            }, function(error) {
                console.error("Error removing favorite:", error);
                toastr.error("Failed to remove from favorites");
            });
        }
    }
};