var ReviewService = {
    // Initialize when page loads
    init: function () {
        console.log("ReviewService starting...");
        
        if (window.location.hash === '#reviews' || $('#reviewsSection').length > 0) {
            ReviewService.loadReviews();
        }
    },
    
    // Load all reviews from database
    loadReviews: function() {
        console.log("Loading reviews...");
        
        // Show loading message
        $('#reviewsSection').html(`
            <div class="col-12 text-center">
                <p>Loading reviews...</p>
            </div>
        `);
        
        // Get reviews from API
        RestClient.get("reviews", function(reviews) {
            ReviewService.showReviews(reviews);
        }, function(error) {
            $('#reviewsSection').html('<p>Failed to load reviews</p>');
        });
    },
    
    // Display reviews on page
    showReviews: function(reviews) {
        let html = '';
        const isLoggedIn = UserService.isLoggedIn();
        const isAdmin = UserService.isAdmin();
        
        // Add review button for logged in users
        if (isLoggedIn) {
            html += `
                <div class="col-12 mb-4">
                    <div class="card bg-light">
                        <div class="card-body">
                            <h5>Add Your Review</h5>
                            <button class="btn btn-primary" onclick="ReviewService.openReviewForm()">
                                Write a Review
                            </button>
                        </div>
                    </div>
                </div>
            `;
        }
        
        // Show message if no reviews
        if (!reviews || reviews.length === 0) {
            html += '<div class="col-12 text-center"><h4>No reviews yet</h4></div>';
            $('#reviewsSection').html(html);
            return;
        }
        
        // Create review cards
        reviews.forEach(function(review) {
            const stars = '⭐'.repeat(Math.floor(review.rating));
            
            html += `
                <div class="col-md-6 mb-4">
                    <div class="card h-100 shadow-sm">
                        <div class="card-body position-relative">
                            ${isAdmin ? `
                                <button class="btn btn-danger btn-sm position-absolute top-0 end-0 m-2" 
                                        onclick="ReviewService.deleteReview(${review.id})">
                                    Delete
                                </button>
                            ` : ''}
                            
                            <div class="d-flex align-items-center mb-3">
                                <img src="${review.image_url || 'assets/img/default-movie.jpg'}" 
                                     class="rounded me-3" alt="${review.movie_title}" 
                                     style="width: 80px; height: 120px; object-fit: cover;">
                                <div>
                                    <h5>${review.movie_title}</h5>
                                    <div class="mb-2">
                                        <span class="text-warning">${stars}</span>
                                        <small class="text-muted ms-2">${review.rating}/10</small>
                                    </div>
                                    <small class="text-muted">By ${review.user_name}</small>
                                </div>
                            </div>
                            
                            <p>"${review.comment}"</p>
                            <small class="text-muted">Posted recently</small>
                        </div>
                    </div>
                </div>
            `;
        });
        
        $('#reviewsSection').html(html);
    },
    
    // Open review form modal
    openReviewForm: function() {
        if (!UserService.isLoggedIn()) {
            toastr.error("Please login first");
            return;
        }
        
        // Get movies for dropdown
        RestClient.get("movies", function(movies) {
            let movieOptions = '<option value="">Choose a movie</option>';
            movies.forEach(function(movie) {
                movieOptions += `<option value="${movie.id}">${movie.title}</option>`;
            });
            
            // Create modal
            const modalHtml = `
                <div class="modal fade" id="reviewModal" tabindex="-1">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5>Write a Review</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <div class="mb-3">
                                    <label>Movie:</label>
                                    <select class="form-select" id="movieSelect">
                                        ${movieOptions}
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label>Rating (1-10):</label>
                                    <input type="number" class="form-control" id="ratingInput" 
                                           min="1" max="10" step="0.1">
                                </div>
                                <div class="mb-3">
                                    <label>Your Review:</label>
                                    <textarea class="form-control" id="commentInput" rows="4"
                                              placeholder="What did you think of this movie?"></textarea>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                <button type="button" class="btn btn-primary" onclick="ReviewService.submitReview()">
                                    Submit Review
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            
            $('#reviewModal').remove();
            $('body').append(modalHtml);
            $('#reviewModal').modal('show');
        });
    },
    
    // Submit new review
    submitReview: function() {
        const movieId = $('#movieSelect').val();
        const rating = $('#ratingInput').val();
        const comment = $('#commentInput').val();
        
        // Check if all fields filled
        if (!movieId || !rating || !comment) {
            toastr.error("Please fill all fields");
            return;
        }
        
        // Prepare data
        const data = {
            user_id: 24, // Admin user ID 
            movie_id: movieId,
            rating: parseFloat(rating),
            comment: comment
        };
        
        // Send to server
        RestClient.post('reviews', data, function(response) {
            toastr.success("Review submitted!");
            $('#reviewModal').modal('hide');
            ReviewService.loadReviews(); // Refresh reviews
        }, function(error) {
            toastr.error("Failed to submit review");
        });
    },
    
    // Delete review (admin only)
    deleteReview: function(reviewId) {
        if (!UserService.requireAdmin()) return;
        
        if (confirm("Delete this review?")) {
            RestClient.delete(`reviews/${reviewId}`, null, function(response) {
                toastr.success("Review deleted");
                ReviewService.loadReviews(); // Refresh reviews
            }, function(error) {
                toastr.error("Failed to delete review");
            });
        }
    }
};