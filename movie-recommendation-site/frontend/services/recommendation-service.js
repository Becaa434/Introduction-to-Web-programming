var RecommendationService = {
    init: function () {
        console.log("RecommendationService.init() called");
        
        // Load recommendations when the recommendations page is accessed
        if (window.location.hash === '#recommendations' || $('#recommendationsSection').length > 0) {
            RecommendationService.loadRecommendations();
        }
    },

    addMovieToRecommendations: function(movieId, movieTitle) {
        if (!UserService.requireAdmin()) return;
        
        const data = {
            user_id: 24, 
            movie_id: movieId,
            recommendation_score: 8.0, 
            created_at: new Date().toISOString().slice(0, 19).replace('T', ' ')
        };
        
        console.log("Adding movie to recommendations:", data);
        
        RestClient.post('recommendations', data, function(response) {
            console.log("Successfully added to recommendations:", response);
            toastr.success(`"${movieTitle}" added to recommendations!`);
            
            // Close movie modal if it's open
            if (typeof MovieService !== 'undefined' && MovieService.closeMovieDetails) {
                MovieService.closeMovieDetails();
            }
        }, function(error) {
            console.error("Error adding to recommendations:", error);
            
            // Handle duplicate entry error
            let errorMessage = "Failed to add to recommendations";
            try {
                const responseText = error.responseText;
                if (responseText) {
                    const errorData = JSON.parse(responseText);
                    const message = errorData.message || "";
                    
                    if (message.includes("Duplicate") || message.includes("already")) {
                        toastr.warning(`"${movieTitle}" is already in recommendations!`);
                        return;
                    }
                    
                    if (errorData.message) {
                        errorMessage = errorData.message;
                    }
                }
            } catch (parseError) {
                console.error("Error parsing response:", parseError);
            }
            
            toastr.error(errorMessage);
        });
    },
    
    loadRecommendations: function() {
        console.log("Loading recommendations from API...");
        
        // Show loading
        $('#recommendationsSection').html(`
            <div class="col-12 text-center">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading recommendations...</span>
                </div>
                <p class="mt-2">Loading recommended movies...</p>
            </div>
        `);
        
        // Get all recommendations
        RestClient.get("recommendations", function(data) {
            console.log("Recommendations loaded:", data);
            RecommendationService.renderRecommendations(data);
        }, function(error) {
            console.error("Error loading recommendations:", error);
            toastr.error("Failed to load recommendations");
            $('#recommendationsSection').html(`
                <div class="col-12 text-center">
                    <p class="text-danger">Failed to load recommendations</p>
                </div>
            `);
        });
    },
    
    renderRecommendations: function(recommendations) {
        const recommendationsSection = $('#recommendationsSection');
        const isAdmin = UserService.isAdmin();
        
        let recommendationsHtml = '';
        
        if (!recommendations || recommendations.length === 0) {
            recommendationsSection.html(`
                <div class="col-12 text-center">
                    <h4>No recommendations yet</h4>
                    <p>Check back later for movie recommendations!</p>
                </div>
            `);
            return;
        }
        
        recommendations.forEach(recommendation => {
            const movie = recommendation;
            
            recommendationsHtml += `
                <div class="col-md-3 mb-4">
                    <div class="card h-100 shadow-sm">
                        <div class="position-relative">
                            <img src="${movie.image_url || 'assets/img/default-movie.jpg'}" 
                                 class="card-img-top" alt="${movie.title}" 
                                 style="height: 400px; object-fit: cover; pointer-events: none;">
                            ${isAdmin ? `
                                <button class="btn btn-danger btn-sm position-absolute top-0 end-0 m-2" 
                                        onclick="RecommendationService.removeRecommendation(${recommendation.id}, '${movie.title.replace(/'/g, "\\'")}');">
                                    Remove
                                </button>
                            ` : ''}
                        </div>
                        <div class="card-body text-center" style="pointer-events: none;">
                            <h5 class="card-title">${movie.title}</h5>
                            <div class="mb-2">
                                <small class="text-muted">
                                    <strong>${movie.genre || 'Unknown'}</strong> • 
                                    ${movie.release_year} • 
                                    ⭐ ${movie.rating || 'N/A'}/10
                                </small>
                            </div>
                            <div class="text-center">
                                <span class="badge bg-success">Recommended</span>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        });
        
        recommendationsSection.html(recommendationsHtml);
    },
    
    removeRecommendation: function(recommendationId, movieTitle) {
        if (!UserService.requireAdmin()) return;
        
        if (confirm(`Remove "${movieTitle}" from recommendations?`)) {
            RestClient.delete(`recommendations/${recommendationId}`, null, function(response) {
                toastr.success(`"${movieTitle}" removed from recommendations`);
                RecommendationService.loadRecommendations();
            }, function(error) {
                console.error("Error removing recommendation:", error);
                toastr.error("Failed to remove recommendation");
            });
        }
    }
};