# Reach Out World Day Media Resource Portal - Application Documentation

## Overview
The Reach Out World Day Media Resource Portal is a video sharing and management platform with user authentication, video upload capabilities, and administrative features.

## Directory Structure

### Main Application (`/app`)

#### Core Files
- `index.php` - Main entry point of the application
- `router.php` - Handles URL routing
- `auth_check.php` - Authentication verification
- `install_auth.php` - Initial authentication setup

#### Authentication
- `login.php` - User login interface
- `register.php` - New user registration
- `forgot_password.php` - Password recovery
- `reset_password.php` - Password reset functionality
- `logout.php` - Session termination

#### Video Management
- `upload.php` - Video upload interface
- `handle_upload.php` - Processes video uploads
- `player.php` - Video player page
- `video_details.php` - Displays video information
- `save_video_details.php` - Updates video metadata
- `fetch_videos.php` - Retrieves video listings
- `search_videos.php` - Video search functionality
- `category.php` - Category-based video browsing
- `latest.php` - Shows recent videos

#### User Features
- `profile.php` - User profile management
- `search.php` - Search functionality

#### Assets and Styling
- `/assets` - Static resources
- `/components` - Reusable UI components
- `/includes` - Shared PHP includes
- `/js` - JavaScript files
  - `script.js` - Main JavaScript functionality
  - `shared.js` - Shared JavaScript utilities
- `styles.css` - Main stylesheet
- `mobile.css` - Mobile-specific styles
- `auth.css` - Authentication pages styling

### Administrative Panel (`/admin`)

#### Core Admin Files
- `index.php` - Admin panel entry point
- `dashboard.php` - Administrative dashboard
- `auth.php` - Admin authentication
- `login.php` - Admin login interface
- `logout.php` - Admin logout

#### User Management
- `users.php` - User management interface
- `delete_user.php` - User deletion functionality

#### Content Management
- `media.php` - Media management interface

#### Styling
- `admin.css` - Admin panel styling

## Key Features

1. **User Authentication**
   - Secure login/registration system
   - Password recovery functionality
   - Session management

2. **Video Management**
   - Video upload with metadata
   - Video categorization
   - Search functionality
   - Video player

3. **Administrative Features**
   - User management
   - Content moderation
   - Analytics dashboard
   - Media management

4. **Security**
   - Authentication checks
   - Admin-level access control
   - Secure password handling

## Database
The application uses MySQL database (schema available in `rowdresources.sql`)

## Technical Requirements
- PHP
- MySQL
- Web server (e.g., Apache)
- Modern web browser

## Setup Instructions
1. Import `rowdresources.sql` to set up the database
2. Configure database connection
3. Run `install_auth.php` for initial authentication setup
4. Access the application through the web server

## Security Considerations
- All admin routes are protected by authentication
- User passwords are securely hashed
- Session management implemented
- Input validation and sanitization in place
