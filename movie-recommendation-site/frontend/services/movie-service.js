var MovieService = {
    init: function () {
        console.log("MovieService.init() called");
        
        // Setup form validations 
        if ($("#addMovieForm").length > 0) {
            $("#addMovieForm").validate({
                rules: {
                    title: {
                        required: true,
                        minlength: 2
                    },
                    description: {
                        required: true,
                        minlength: 10
                    },
                    genre: {
                        required: true
                    },
                    release_year: {
                        required: true,
                        number: true,
                        min: 1888,
                        max: new Date().getFullYear() + 5
                    },
                    rating: {
                        required: true,
                        number: true,
                        min: 0,
                        max: 10
                    }
                },
                submitHandler: function (form) {
                    var movie = Object.fromEntries(new FormData(form).entries());
                    MovieService.addMovie(movie);
                    form.reset();
                },
            });
        }

        if ($("#editMovieForm").length > 0) {
            $("#editMovieForm").validate({
                rules: {
                    title: {
                        required: true,
                        minlength: 2
                    },
                    description: {
                        required: true,
                        minlength: 10
                    },
                    genre: {
                        required: true
                    },
                    release_year: {
                        required: true,
                        number: true,
                        min: 1888,
                        max: new Date().getFullYear() + 5
                    },
                    rating: {
                        required: true,
                        number: true,
                        min: 0,
                        max: 10
                    }
                },
                submitHandler: function (form) {
                    var movie = Object.fromEntries(new FormData(form).entries());
                    MovieService.editMovie(movie);
                },
            });
        }
        
        // Load movies when the movies page is accessed
        if (window.location.hash === '#movies' || $('#moviesSection').length > 0) {
            MovieService.loadMovies();
        }
    },
    
    loadMovies: function() {
        console.log("Loading movies from API...");
        
        RestClient.get("movies", function(data) {
            console.log("Movies loaded:", data);
            MovieService.renderMovies(data);
        }, function(error) {
            console.error("Error loading movies:", error);
            toastr.error("Failed to load movies from database");
            $('#moviesSection').html('<div class="col-12 text-center"><p class="text-danger">Failed to load movies from database</p></div>');
        });
    },
    
    renderMovies: function(movies) {
        const moviesSection = $('#moviesSection');
        const isAdmin = UserService.isAdmin();
        
        let moviesHtml = '';
        
        // Add admin controls at the top if user is admin
        if (isAdmin) {
            moviesHtml += `
                <div class="col-12 mb-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <h3>Movies Management (Admin)</h3>
                        <button type="button" class="btn btn-success" 
                                onclick="MovieService.openAddModal()">
                            Add New Movie
                        </button>
                    </div>
                </div>
            `;
        }
        
        movies.forEach(movie => {
            // Clean movie cards - just poster and title, clickable
            moviesHtml += `
                <div class="col-md-3 mb-4">
                    <div class="card h-100 shadow-sm movie-card" onclick="MovieService.openMovieDetails(${movie.id})" style="cursor: pointer;">
                        <div class="position-relative">
                            <img src="${movie.image_url || 'assets/img/default-movie.jpg'}" 
                                 class="card-img-top" alt="${movie.title}" 
                                 style="height: 400px; object-fit: cover;">
                        </div>
                        <div class="card-body text-center">
                            <h5 class="card-title">${movie.title}</h5>
                        </div>
                    </div>
                </div>
            `;
        });
        
        moviesSection.html(moviesHtml);
    },

    openMovieDetails: function(movieId) {
    RestClient.get('movies/' + movieId, function(movie) {
        const isAdmin = UserService.isAdmin();
        const isLoggedIn = UserService.isLoggedIn();
        
        // Build user action buttons if user is logged in
        const userButtons = isLoggedIn ? `
            <div class="mt-3">
                <button type="button" class="btn btn-success me-2" 
                        onclick="FavoriteService.addToFavorites(${movie.id}, '${movie.title.replace(/'/g, "\\'")}');">
                    ⭐ Add to Favorites
                </button>
            </div>
        ` : '';
        
        // Build admin buttons if user is admin
        const adminButtons = isAdmin ? `
        <div class="mt-3">
            <hr>
            <h6>Admin Actions:</h6>
         <button type="button" class="btn btn-info me-2"
                    onclick="RecommendationService.addMovieToRecommendations(${movie.id}, '${movie.title.replace(/'/g, "\\'")}');">
             Add to Recommendations
            </button>
            <button type="button" class="btn btn-warning me-2"
                    onclick="MovieService.closeMovieDetails(); MovieService.openEditModal(${movie.id});">
                Edit Movie
            </button>
            <button type="button" class="btn btn-danger"
                    onclick="MovieService.closeMovieDetails(); MovieService.openConfirmationDialog(${movie.id}, '${movie.title.replace(/'/g, "\\'")}');">
                Delete Movie
            </button>
        </div>
    ` : '';
        
        // Build the modal content
        const modalContent = `
            <div class="row">
                <div class="col-md-4">
                    <img src="${movie.image_url || 'assets/img/default-movie.jpg'}" 
                         class="img-fluid rounded" alt="${movie.title}">
                </div>
                <div class="col-md-8">
                    <h3>${movie.title}</h3>
                    <p class="text-muted mb-3">
                        <strong>${movie.genre || 'Unknown'}</strong> • 
                        ${movie.release_year} • 
                        ⭐ ${movie.rating || 'N/A'}/10
                    </p>
                    <p>${movie.description || 'No description available.'}</p>
                    ${userButtons}
                    ${adminButtons}
                </div>
            </div>
        `;
        
        // Update modal content and show
        $('#movieDetailsModal .modal-title').text(movie.title);
        $('#movieDetailsModal .modal-body').html(modalContent);
        $('#movieDetailsModal').modal('show');
        
    }, function(error) {
        toastr.error("Failed to load movie details");
    });
    },
    closeMovieDetails: function() {
        $('#movieDetailsModal').modal('hide');
    },
    openAddModal: function() {
        if (!UserService.requireAdmin()) return;
        $('#addMovieModal').modal('show');
    },

    addMovie: function(movie) {
        if (!UserService.requireAdmin()) return;
        
        console.log("Adding movie:", movie);
        $.blockUI({ message: '<h3>Processing...</h3>' });
        
        RestClient.post('movies', movie, function(response) {
            toastr.success("Movie added successfully");
            $.unblockUI();
            MovieService.loadMovies();
            MovieService.closeModal();
        }, function(response) {
            MovieService.closeModal();
            toastr.error("Failed to add movie");
            $.unblockUI();
        });
    },

    getMovieById: function(id) {
        RestClient.get('movies/' + id, function(data) {
            localStorage.setItem('selected_movie', JSON.stringify(data));
            $('input[name="title"]').val(data.title);
            $('textarea[name="description"]').val(data.description);
            $('select[name="genre"]').val(data.genre);
            $('input[name="release_year"]').val(data.release_year);
            $('input[name="rating"]').val(data.rating);
            $('input[name="image_url"]').val(data.image_url || '');
            $('input[name="id"]').val(data.id);
            $.unblockUI();
        }, function(xhr, status, error) {
            console.error('Error fetching movie data');
            $.unblockUI();
        });
    },

    openEditModal: function(id) {
        if (!UserService.requireAdmin()) return;
        
        $.blockUI({ message: '<h3>Processing...</h3>' });
        $('#editMovieModal').modal('show');
        MovieService.getMovieById(id);
    },

    closeModal: function() {
        $('#editMovieModal').modal('hide');
        $('#deleteMovieModal').modal('hide');
        $('#addMovieModal').modal('hide');
        $('#movieDetailsModal').modal('hide');
    },

    editMovie: function(movie) {
        if (!UserService.requireAdmin()) return;
        
        console.log("Editing movie:", movie);
        $.blockUI({ message: '<h3>Processing...</h3>' });
        
        RestClient.put('movies/' + movie.id, movie, function(data) {
            $.unblockUI();
            toastr.success("Movie updated successfully");
            MovieService.closeModal();
            MovieService.loadMovies();
        }, function(xhr, status, error) {
            console.error('Error updating movie');
            $.unblockUI();
            toastr.error("Failed to update movie");
        });
    },

    openConfirmationDialog: function(movieId, movieTitle) {
        if (!UserService.requireAdmin()) return;
        
        $('#deleteMovieModal').modal('show');
        $('#delete-movie-body').html("Do you want to delete movie: " + movieTitle);
        $('#delete_movie_id').val(movieId);
    },

    deleteMovie: function() {
        if (!UserService.requireAdmin()) return;
        
        const movieId = $('#delete_movie_id').val();
        
        RestClient.delete('movies/' + movieId, null, function(response) {
            MovieService.closeModal();
            toastr.success("Movie deleted successfully");
            MovieService.loadMovies();
        }, function(response) {
            MovieService.closeModal();
            toastr.error("Failed to delete movie");
        });
    },
    
    // Search and filter functions
    searchMovies: function(query) {
        if (!query.trim()) {
            MovieService.loadMovies();
            return;
        }
        
        RestClient.get("movies", function(data) {
            const filteredMovies = data.filter(movie => 
                movie.title.toLowerCase().includes(query.toLowerCase()) ||
                (movie.description && movie.description.toLowerCase().includes(query.toLowerCase()))
            );
            MovieService.renderMovies(filteredMovies);
        }, function(error) {
            toastr.error("Search failed");
        });
    },
    
    filterByGenre: function(genre) {
        if (!genre) {
            MovieService.loadMovies();
            return;
        }
        
        RestClient.get(`movies/genre/${encodeURIComponent(genre)}`, 
            function(data) {
                MovieService.renderMovies(data);
            }, 
            function(error) {
                toastr.error("Failed to filter movies by genre");
            }
        );
    },
    
    filterByYear: function(year) {
        if (!year) {
            MovieService.loadMovies();
            return;
        }
        
        RestClient.get(`movies/year/${year}`, 
            function(data) {
                MovieService.renderMovies(data);
            }, 
            function(error) {
                toastr.error("Failed to filter movies by year");
            }
        );
    }
};