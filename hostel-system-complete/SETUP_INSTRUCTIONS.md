# Hostel Management System - Setup Instructions

## Overview
Complete Hostel Management System with PHP 8.2 backend, PostgreSQL database, and Bootstrap 5 frontend.

## Quick Start

### 1. Prerequisites
- PHP 8.2 or higher
- PostgreSQL 12 or higher
- Git (optional)

### 2. Installation Steps

#### A. Database Setup
```bash
# Create a mySQL database
createdb hostel_system

# Restore the database with sample data
psql hostel_system < sql/database_backup.sql
# OR run the schema
psql hostel_system < sql/schema.sql
```

#### B. Configuration
1. Edit `config/database.php` with your PostgreSQL credentials:
```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'hostel_system');
define('DB_USER', 'postgres');
define('DB_PASS', 'your_password');
```

2. (Optional) Add Google Maps API key in `public/hostels.php`:
```php
// Replace YOUR_API_KEY with your actual key
src="https://maps.googleapis.com/maps/api/js?key=YOUR_API_KEY&callback=initMap"
```

#### C. Run the Application
```bash
# Start PHP development server
php -S localhost:5000 -t public

# Access at http://localhost:5000
```

### 3. Default Credentials
- **Email:** admin@hostel.com
- **Password:** admin123
- **Role:** Admin

### 4. Features

#### For Students
- User authentication and dashboard
- Hostel finder with Google Maps
- Room booking system
- Boda transport requests (UGX 250/km)
- Grab Corner marketplace
- Feedback/complaint system

#### For Business Owners
- **Hostel Owners:** Register hostels, manage rooms, view bookings & revenue
- **Hotel Owners:** Manage hotel properties
- **Boda Riders:** Accept ride requests, track earnings, manage profile

### 5. Registration Options
1. **Student** - Browse and book rooms, request transport
2. **Hostel Owner** - Manage hostels and rooms, receive bookings
3. **Hotel Owner** - Manage hotel properties
4. **Boda Rider** - Accept transport requests, earn commissions

### 6. File Structure
```
├── app/
│   ├── models/          # Database models
│   └── views/           # Additional templates
├── config/              # Configuration files
├── includes/            # Header/footer templates
├── public/              # Main application files (HTML/PHP)
├── sql/                 # Database schema & backups
└── replit.md            # Project documentation
```

### 7. Key Pages
- **Login:** `/login.php`
- **Register:** `/register.php`
- **Dashboard:** `/dashboard.php` (after login)
- **Find Hostels:** `/hostels.php`
- **Book Room:** `/booking.php`
- **Transport:** `/transport.php`
- **Grab Corner:** `/grab.php`
- **Feedback:** `/feedback.php`
- **Admin Panel:** `/admin.php` (admin only)
- **Owner Dashboard:** `/owner-dashboard.php` (business owners)

### 8. Database Schema

**Main Tables:**
- `users` - User accounts (students, owners, riders)
- `hostels` - Hostel listings
- `rooms` - Room details
- `bookings` - Room reservations
- `transport` - Boda requests
- `riders` - Boda rider profiles
- `products` - Marketplace items
- `orders` - Product orders
- `feedback` - User feedback/complaints

### 9. Pricing
- **Boda Transport:** UGX 250 per kilometer
- **Rooms:** Variable (set by hostel owner)
- **Products:** Set by marketplace

### 10. Troubleshooting

**Database Connection Error:**
- Check PostgreSQL is running
- Verify credentials in `config/database.php`
- Ensure database exists: `createdb hostel_system`

**Google Maps Not Loading:**
- Add valid API key in `public/hostels.php`
- Enable Maps JavaScript API in Google Cloud Console

**Session Errors:**
- Clear browser cookies
- Ensure `/tmp` directory is writable for PHP sessions

### 11. Next Steps
1. Customize branding and colors in `public/css/style.css`
2. Add your business hostels via Owner Dashboard
3. Configure Boda rider accounts
4. Add products to Grab Corner
5. Test the booking and transport flow

### 12. Support
For issues or customization needs, refer to the code comments and replit.md documentation.

---
**Version:** 1.0  
**Last Updated:** November 2025
