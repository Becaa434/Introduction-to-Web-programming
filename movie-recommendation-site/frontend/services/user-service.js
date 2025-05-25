var UserService = {
    init: function () {
        console.log("UserService.init() called");
        
        var token = localStorage.getItem("user_token");
        var currentPage = window.location.hash;
        
        // Only redirect to home if user is on login/register pages and already has a token
        if (token && token !== undefined && (currentPage === '#login' || currentPage === '#register')) {
            console.log("User already has token and is on auth page, redirecting to home");
            window.location.replace("#home");
            return;
        }
        
        // Setup login form validation (only if login form exists)
        if ($("#login-form").length > 0) {
            $("#login-form").validate({
                rules: {
                    email: {
                        required: true,
                        email: true
                    },
                    password: {
                        required: true,
                        minlength: 6
                    }
                },
                messages: {
                    email: {
                        required: "Please enter your email address",
                        email: "Please enter a valid email address"
                    },
                    password: {
                        required: "Please enter your password",
                        minlength: "Password must be at least 6 characters long"
                    }
                },
                submitHandler: function (form) {
                    console.log("Login form submitted");
                    var entity = Object.fromEntries(new FormData(form).entries());
                    console.log("Login entity:", entity);
                    UserService.login(entity);
                },
            });
        }
        
        // Setup register form validation (only if register form exists)
        if ($("#register-form").length > 0) {
            $("#register-form").validate({
                rules: {
                    name: {
                        required: true,
                        minlength: 2
                    },
                    email: {
                        required: true,
                        email: true
                    },
                    password: {
                        required: true,
                        minlength: 6
                    },
                    "confirm-password": {
                        required: true,
                        equalTo: "#password"
                    }
                },
                messages: {
                    name: {
                        required: "Please enter your full name",
                        minlength: "Name must be at least 2 characters long"
                    },
                    email: {
                        required: "Please enter your email address",
                        email: "Please enter a valid email address"
                    },
                    password: {
                        required: "Please enter a password",
                        minlength: "Password must be at least 6 characters long"
                    },
                    "confirm-password": {
                        required: "Please confirm your password",
                        equalTo: "Passwords do not match"
                    }
                },
                submitHandler: function (form) {
                    console.log("Register form submitted");
                    var entity = Object.fromEntries(new FormData(form).entries());
                    console.log("Register entity:", entity);
                    UserService.register(entity);
                },
            });
        }
        
        // Update navigation based on login status
        UserService.updateNavigation();
        
        console.log("UserService initialization complete");
    },
    
    updateNavigation: function() {
        var token = localStorage.getItem("user_token");
        
        if (token) {
            // User is logged in - show logout option, hide login/register
            console.log("User is logged in");
            UserService.showAuthenticatedNav();
        } else {
            console.log("User is not logged in");
            UserService.showUnauthenticatedNav();
        }
    },
    
    showAuthenticatedNav: function() {
        const user = UserService.getCurrentUser();
        if (user) {
            // Hide login/register, show user-specific navigation
            $('.nav-link[href="#login"], .nav-link[href="#register"]').parent().hide();
            
            // Add user menu if it doesn't exist
            if ($('#user-menu').length === 0) {
                const userMenu = `
                    <li class="nav-item dropdown" id="user-menu">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                            Welcome, ${user.name}
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="#home">Home</a></li>
                            <li><a class="dropdown-item" href="#register">Register/Login</a></li>
                            <li><a class="dropdown-item" href="#" id="logout-link">Logout</a></li>
                        </ul>
                    </li>
                `;
                $('.navbar-nav').append(userMenu);
                
                // Add event listener for logout link
                $('#logout-link').on('click', function(e) {
                    e.preventDefault();
                    UserService.logout();
                });
            }
        }
    },
    
    showUnauthenticatedNav: function() {
        $('.nav-link[href="#login"], .nav-link[href="#register"]').parent().show();
        $('#user-menu').remove();
    },
    
    login: function (entity) {
        console.log("UserService.login called with:", entity);
        
        $.ajax({
            url: Constants.PROJECT_BASE_URL + "/auth/login",
            type: "POST",
            data: JSON.stringify(entity),
            contentType: "application/json",
            dataType: "json",
            beforeSend: function(xhr) {
                console.log("Sending login request to:", Constants.PROJECT_BASE_URL + "/auth/login");
            },
            success: function (result) {
                console.log("Login success:", result);
                
                if (result.data && result.data.token) {
                    localStorage.setItem("user_token", result.data.token);
                    console.log("Token stored, redirecting to home");
                    toastr.success("Login successful!");
                    UserService.updateNavigation();
                    window.location.hash = "home"; 
                } else {
                    console.error("No token in response:", result);
                    toastr.error("Login successful but no token received");
                }
            },
            error: function (XMLHttpRequest, textStatus, errorThrown) {
                console.error("Login error:", XMLHttpRequest.status, XMLHttpRequest.responseText, textStatus, errorThrown);
                
                let errorMessage = 'Login failed';
                if (XMLHttpRequest.responseText) {
                    try {
                        const errorResponse = JSON.parse(XMLHttpRequest.responseText);
                        errorMessage = errorResponse.message || errorResponse.error || XMLHttpRequest.responseText;
                    } catch (e) {
                        errorMessage = XMLHttpRequest.responseText;
                    }
                }
                
                toastr.error(errorMessage);
            },
        });
    },
    
    register: function (entity) {
        console.log("UserService.register called with:", entity);
        
        // Client-side password confirmation check
        if (entity.password !== entity['confirm-password']) {
            toastr.error("Passwords do not match");
            return;
        }
        
        $.ajax({
            url: Constants.PROJECT_BASE_URL + "/auth/register",
            type: "POST",
            data: JSON.stringify(entity),
            contentType: "application/json",
            dataType: "json",
            beforeSend: function(xhr) {
                console.log("Sending register request to:", Constants.PROJECT_BASE_URL + "/auth/register");
            },
            success: function (result) {
                console.log("Register success:", result);
                toastr.success("Registration successful! Please log in.");
                window.location.hash = "login"; 
            },
            error: function (XMLHttpRequest, textStatus, errorThrown) {
                console.error("Register error:", XMLHttpRequest.status, XMLHttpRequest.responseText, textStatus, errorThrown);
                
                let errorMessage = 'Registration failed';
                if (XMLHttpRequest.responseText) {
                    try {
                        const errorResponse = JSON.parse(XMLHttpRequest.responseText);
                        errorMessage = errorResponse.message || errorResponse.error || XMLHttpRequest.responseText;
                    } catch (e) {
                        errorMessage = XMLHttpRequest.responseText;
                    }
                }
                
                toastr.error(errorMessage);
            },
        });
    },
    
    logout: function () {
        // Clear user data from localStorage
        localStorage.removeItem("user_token");
        
        // Show success message
        toastr.success("Logged out successfully!");
        
        // Update navigation UI
        UserService.updateNavigation();
        
        // Force reload the page before redirecting to home
        // This ensures a clean state with all components properly initialized
        window.location.hash = "home";
        
        // Ensure content is loaded for home page
        if (typeof Router !== 'undefined' && Router.loadContent) {
            console.log("Triggering content reload for home page");
            setTimeout(function() {
                Router.loadContent("home");
            }, 100);
        } else {
            console.log("Forcing page reload");
            window.location.reload();
        }
    },
    
    // Check if user is logged in (utility function)
    isLoggedIn: function() {
        return localStorage.getItem("user_token") !== null;
    },
    
    // Get current user from token
    getCurrentUser: function() {
        const token = localStorage.getItem("user_token");
        if (token) {
            const decoded = Utils.parseJwt(token);
            return decoded ? decoded.user : null;
        }
        return null;
    },
    
    // Check if current user is admin
    isAdmin: function() {
        const user = UserService.getCurrentUser();
        return user && user.role === 'admin';
    },
    
    // Check if current user is regular user
    isUser: function() {
        const user = UserService.getCurrentUser();
        return user && user.role === 'user';
    },
    
    // Middleware to check authentication
    requireAuth: function(callback) {
        if (!UserService.isLoggedIn()) {
            toastr.error("Please login to access this feature");
            window.location.hash = "login";
            return false;
        }
        if (callback) callback();
        return true;
    },
    
    // Middleware to check admin role
    requireAdmin: function(callback) {
        if (!UserService.isLoggedIn()) {
            toastr.error("Please login to access this feature");
            window.location.hash = "login";
            return false;
        }
        if (!UserService.isAdmin()) {
            toastr.error("Access denied. Admin privileges required.");
            return false;
        }
        if (callback) callback();
        return true;
    }
};