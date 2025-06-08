# Movie Recommendation Site

## Overview
A modern web application for exploring and managing movies with role-based access control. The application provides a comprehensive movie browsing experience with admin management capabilities and community-driven content.

## Features

### Core Features
- **Movie Browsing**: Explore a curated collection of movies with detailed information
- **Search & Filter**: Find movies by title, genre, or release year
- **Community Favorites**: View most loved movies by the community
- **Personalized Recommendations**: Get movie suggestions based on trending and popular content
- **Movie Reviews**: Read community reviews and ratings

###  Authentication & Authorization
- **User Registration & Login**: Secure JWT-based authentication
- **Role-Based Access Control**: 
  - **Admin Users**: Full CRUD operations on movies (Create, Read, Update, Delete)
  - **Regular Users**: Browse and view movies (Read-only access)
- **Password Security**: Hashed password storage for user protection

###  Admin Features
- **Movie Management**: Add, edit, and delete movies from the database
- **Dynamic Admin Panel**: Admin-only buttons and management interface
- **Form Validation**: Comprehensive validation for movie data entry
- **Modal-Based Interface**: User-friendly modal forms for CRUD operations


## Technologies

### Frontend
- **HTML5**: Semantic markup and structure
- **CSS3**: Custom styling with Bootstrap 5.3.3
- **JavaScript (ES6+)**: Modern JavaScript with jQuery
- **Bootstrap 5**: Responsive UI framework
- **jQuery**: DOM manipulation and AJAX requests
- **SPA Framework**: jquery.spapp for single-page application routing

### Backend
- **PHP**: Server-side scripting with FlightPHP framework
- **MySQL**: Relational database for data storage
- **JWT Authentication**: JSON Web Token for secure authentication
- **RESTful API**: Clean API endpoints for frontend communication

### Development Tools
- **XAMPP**: Local development environment
- **Git**: Version control
- **Browser DevTools**: Debugging and testing

## API Endpoints

## Setup Instructions

### Prerequisites
- XAMPP (Apache + MySQL + PHP)
- Modern web browser
- Git (optional)

### Installation

1. **Clone/Download the project**
   ```bash
   git clone https://github.com/Becaa434/Introduction-to-Web-programming.git
   cd movie-recommendation-site
   ```

2. **Setup Backend**
   - Place backend files in XAMPP's `htdocs` directory
   - Start XAMPP (Apache + MySQL)
   - Import database schema
   - Configure database connection in backend

3. **Setup Frontend**
   - Update `utils/constants.js` with correct backend URL
   - Ensure all file paths are correct

4. **Access the Application**
   - Open `http://localhost/AdiBeca/Introduction-to-Web-programming/movie-recommendation-site/frontend/`
   - Register a new account or login with existing credentials


**© 2025 Movie Recommendation Site. All rights reserved.**