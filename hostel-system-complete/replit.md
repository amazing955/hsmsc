# Hostel Management System

## Overview
A comprehensive full-stack Hostel Management System built with PHP, Bootstrap 5, and PostgreSQL.

## Features
- **User Authentication**: Secure login/register with password hashing and role-based access (Admin/Student)
- **Hostel Finder**: Interactive Google Maps integration to find nearby hostels
- **Room Booking**: Check availability and book rooms with instant confirmation
- **Transport Service**: Hostel Boda request system with rider assignment
- **Grab Corner**: Mini-market for ordering items
- **Feedback System**: Submit complaints, reviews, and suggestions
- **Admin Dashboard**: Complete overview of users, hostels, rooms, bookings, feedback, and transport

## Tech Stack
- **Frontend**: HTML5, CSS3, JavaScript, Bootstrap 5, Font Awesome
- **Backend**: PHP 8.2 with PDO and MVC architecture
- **Database**: PostgreSQL
- **Maps**: Google Maps JavaScript API

## Project Structure
```
├── app/
│   ├── models/          # Data models (User, Hostel, Room, Booking, etc.)
│   ├── controllers/     # Request handlers
│   └── views/          # Additional view templates
├── config/
│   ├── config.php      # Application configuration
│   └── database.php    # Database connection
├── includes/
│   ├── header.php      # Common header template
│   └── footer.php      # Common footer template
├── public/
│   ├── css/           # Stylesheets
│   ├── js/            # JavaScript files
│   └── *.php          # Main application pages
└── sql/
    └── schema.sql     # Database schema
```

## Database Schema
- **users**: User accounts with roles
- **hostels**: Hostel information with coordinates
- **rooms**: Room details and availability
- **bookings**: Room reservations
- **transport**: Boda transport requests
- **products**: Grab Corner items
- **orders**: Product orders
- **feedback**: User feedback/complaints
- **room_layout**: Saved room layouts (for future feature)

## Default Credentials
- **Admin**: admin@hostel.com / admin123

## Setup Instructions
1. Database is automatically configured via environment variables
2. Run the PHP development server on port 5000
3. Access the application at the provided URL
4. Add Google Maps API key in hostels.php to enable full map features

## Recent Changes
- Initial project setup with MVC structure
- Database schema created with sample data
- All MVP pages implemented
- Bootstrap 5 UI with gradient background
- Responsive sidebar navigation
