# Hostel Management System

A complete full-stack web application for managing hostels, bookings, transport services, and marketplace operations in Uganda.

## Features

### 🏨 Hostel Management
- Register and manage multiple hostels
- Add/edit rooms with capacity and pricing
- Real-time room availability tracking
- Hostel location with GPS coordinates

### 🛏️ Room Booking
- Browse available rooms
- Check-in/check-out date selection
- Instant booking confirmation
- Booking history and status tracking

### 🏍️ Hostel Boda Transport
- Request rides from available drivers
- Real-time rider tracking
- Automatic rider assignment
- Earnings tracking for riders

### 🛒 Grab Corner (Marketplace)
- Order snacks, drinks, and essentials
- Product inventory management
- Order tracking and history

### 💬 Feedback System
- Submit complaints and suggestions
- Track feedback status
- Admin review system

### 📊 Admin Dashboard
- Overview of users, hostels, rooms, bookings
- Transport request management
- Feedback review system
- System statistics

## Technology Stack

- **Frontend:** HTML5, CSS3, Bootstrap 5, JavaScript
- **Backend:** PHP 8.2
- **Database:** PostgreSQL
- **Maps:** Google Maps API
- **Architecture:** MVC (Model-View-Controller)

## Installation

See `SETUP_INSTRUCTIONS.md` for detailed setup.

**Quick Start:**
```bash
# 1. Create database
createdb hostel_system
psql hostel_system < sql/schema.sql

# 2. Configure database
# Edit config/database.php with your credentials

# 3. Start server
php -S localhost:5000 -t public

# 4. Login
# Email: admin@hostel.com
# Password: admin123
```

## User Roles

### Student
- Browse and book hostel rooms
- Request transport services
- Order from marketplace
- Submit feedback

### Hostel Owner
- Manage hostel listings
- Add and manage rooms
- View bookings and revenue
- Track business metrics

### Hotel Owner
- Manage hotel properties
- Coordinate with Boda service

### Boda Rider
- View pending ride requests
- Accept and complete rides
- Track earnings and rating
- Manage profile and availability

### Admin
- System oversight
- User management
- Business metrics
- Feedback review

## File Structure

```
hostel-system-complete/
├── app/
│   ├── models/              # Database models
│   ├── controllers/         # Business logic
│   └── views/               # Templates
├── config/
│   ├── config.php           # App configuration
│   ├── database.php         # Database connection
├── includes/
│   ├── header.php           # Common header
│   └── footer.php           # Common footer
├── public/
│   ├── css/                 # Stylesheets
│   ├── js/                  # JavaScript
│   ├── index.php            # Landing page
│   ├── login.php            # Login page
│   ├── register.php         # Registration
│   ├── dashboard.php        # User dashboard
│   ├── booking.php          # Room booking
│   ├── hostels.php          # Hostel finder
│   ├── transport.php        # Boda requests
│   ├── grab.php             # Marketplace
│   ├── feedback.php         # Feedback system
│   ├── admin.php            # Admin dashboard
│   └── more...
├── sql/
│   ├── schema.sql           # Database schema
│   └── database_backup.sql  # Complete backup
└── replit.md                # Documentation
```

## Default Admin Credentials

- **Email:** admin@hostel.com
- **Password:** admin123

## Pricing

- **Boda Transport:** UGX 250 per kilometer
- **Hostel Rooms:** Set by owner (sample: 150,000-360,000/night)
- **Marketplace:** Set by seller

## Key Pages

| Page | URL | Access |
|------|-----|--------|
| Login | /login.php | Public |
| Register | /register.php | Public |
| Dashboard | /dashboard.php | Logged in |
| Find Hostels | /hostels.php | Students |
| Book Room | /booking.php | Students |
| Transport | /transport.php | Students |
| Marketplace | /grab.php | Students |
| Owner Dashboard | /owner-dashboard.php | Owners |
| Rider Profile | /rider-profile.php | Riders |
| Admin Panel | /admin.php | Admin |

## Database Tables

- **users** - User accounts with roles
- **hostels** - Hostel listings
- **rooms** - Room inventory
- **bookings** - Room reservations
- **riders** - Boda rider profiles
- **transport** - Ride requests
- **products** - Marketplace items
- **orders** - Customer orders
- **feedback** - User feedback/complaints

## Security Features

✅ Password hashing with bcrypt  
✅ SQL injection prevention (prepared statements)  
✅ Session management  
✅ Role-based access control  
✅ Input validation and sanitization  

## Customization

### Change Boda Rate
Edit `public/transport.php` line 24:
```php
$cost = $distance * 250;  // Change 250 to desired rate
```

### Add Google Maps
Get API key from Google Cloud Console, then edit `public/hostels.php`:
```php
src="https://maps.googleapis.com/maps/api/js?key=YOUR_KEY&callback=initMap"
```

### Customize Theme
Edit `public/css/style.css` for colors and styling.

## Testing

1. **Create Student Account:**
   - Register with role "Student"
   - Login and access student features

2. **Create Hostel Owner:**
   - Register with role "Hostel Owner"
   - Complete hostel profile
   - Add rooms
   - View bookings

3. **Create Boda Rider:**
   - Register with role "Boda Rider"
   - Complete rider profile
   - Accept ride requests

4. **Admin Functions:**
   - Login as admin@hostel.com
   - Access admin dashboard
   - Review statistics and feedback

## Troubleshooting

**Database Error?**
- Ensure PostgreSQL is running
- Check database credentials in config/database.php
- Run: `psql hostel_system < sql/schema.sql`

**Maps Not Showing?**
- Add Google Maps API key in public/hostels.php

**Session Lost?**
- Clear browser cookies
- Check PHP session directory permissions

## License

© 2025 Hostel Management System

## Support & Updates

For customization or issues, review the code comments and documentation in each file.

---
**Version:** 1.0  
**Created:** November 2025  
**Compatible:** PHP 8.2+, PostgreSQL 12+
