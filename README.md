# swift_booking_project

## Swift Book - Online Ticket Reservation System

### Project Overview
Swift Book is a comprehensive online ticket reservation system developed in PHP and MySQL for university coursework. The system allows users to search, book, and manage transportation tickets across multiple modes (Bus, Train, Airline).

### Developer
**Fahim Ibney Hafiz** (ID: 41230301887)

### Technology Stack
- **Backend:** PHP 8.3+
- **Database:** MySQL 8.0+
- **Frontend:** HTML5, CSS3, JavaScript
- **Architecture:** MVC-inspired structure

### Features Implemented

#### Core Features
1. **User Registration & Authentication**
   - User registration with validation
   - Login system with admin/user roles
   - Session management

2. **Route Management**
   - Add new routes (Admin only)
   - View available routes with filters
   - Search by transport type, origin, destination
   - Real-time seat availability

3. **Ticket Booking System**
   - Book tickets with seat selection
   - Real-time seat availability checking
   - Booking confirmation with unique ID
   - Payment tracking

4. **Booking Management**
   - View personal bookings
   - Cancel bookings with seat restoration
   - Booking history with status tracking

5. **Admin Features**
   - Dashboard with statistics
   - Route management
   - User management capabilities
   - Database inspector

#### Enhanced Features
1. **Database Inspector**
   - View database schema
   - Execute SELECT queries
   - Real-time statistics
   - Function and trigger documentation

2. **Advanced UI/UX**
   - Responsive design
   - Modern CSS styling
   - Interactive forms
   - Real-time updates

3. **Database Functions & Triggers**
   - Revenue calculation function
   - Automatic route status management
   - Enhanced data integrity

### Database Schema

#### Tables
1. **Users** - User accounts and authentication
2. **Routes** - Transportation routes and schedules
3. **Bookings** - Ticket bookings and seat assignments
4. **Payments** - Payment transactions and status

#### Functions
- `CalculateRouteRevenue(route_id)` - Calculate total revenue for a route

#### Triggers
- `UpdateRouteStatus` - Auto-update route status when seats are full

### Installation Instructions

1. **Database Setup**
   ```sql
   CREATE DATABASE swift_book;
   CREATE USER 'swift_user'@'localhost' IDENTIFIED BY 'swift_pass';
   GRANT ALL PRIVILEGES ON swift_book.* TO 'swift_user'@'localhost';
   FLUSH PRIVILEGES;
   ```

2. **Import Database**
   ```bash
   mysql -u swift_user -p swift_book < swift_book_complete.sql
   ```

3. **Configuration**
   - Update database credentials in `db_connect.php` if needed
   - Ensure PHP and MySQL are running

4. **Access the System**
   ```bash
   php -S localhost:8000
   ```
   - Navigate to `http://localhost:8000/`

### Demo Accounts

#### Admin Account
- **Username:** admin
- **Password:** admin123
- **Features:** Full system access, route management, DB inspector

#### User Accounts
- **Username:** john_doe | **Password:** password123
- **Username:** jane_smith | **Password:** password123
- **Username:** mike_wilson | **Password:** password123

### File Structure
```
swift_book_project/
├── css/
│   └── style.css              # Responsive styling
├── swift_book_complete.sql    # Complete database schema & data
├── db_connect.php            # Database connection
├── index.php                 # Main dashboard
├── login.php                 # User authentication
├── register.php              # User registration
├── view_routes.php           # Route browsing
├── book_ticket.php           # Ticket booking
├── my_bookings.php           # User bookings
├── add_route.php             # Admin route management
├── cancel_booking.php        # Booking cancellation
├── manage_users.php          # User management (Admin)
├── db_inspector.php          # Database inspector (Admin)
├── logout.php                # Session termination
└── README.md                 # This file
```

### Key Improvements

1. **Web-Based Interface** - Accessible from any browser
2. **Database Integration** - Persistent data storage with advanced features
3. **Multi-User Support** - Concurrent user sessions
4. **Enhanced Security** - SQL injection prevention
5. **Real-Time Updates** - Dynamic seat availability
6. **Admin Dashboard** - Comprehensive management tools
7. **Database Inspector** - Advanced debugging and analysis
8. **Responsive Design** - Mobile-friendly interface

### Future Enhancements
- Password hashing for enhanced security
- Email notifications
- PDF ticket generation
- Advanced reporting and analytics
- API integration for real-time schedules
- Mobile app development
- Payment gateway integration

### Project Status
✅ **Complete** - All features implemented and tested

---
**Note:** This system successfully implements a modern web-based ticket reservation system with enhanced functionality, responsive design, and comprehensive admin tools.
