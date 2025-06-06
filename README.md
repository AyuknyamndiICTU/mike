# Online Event Booking System

A comprehensive web-based event booking platform that allows users to browse, search, and book tickets for various events.

## Features

1. User Authentication
   - Sign-up, login, and logout functionality
   - Profile management
   - Session handling

2. Event Listings
   - Catalog of available events
   - Event details including name, date, time, venue, organizer, image, and price

3. Search & Filters
   - Search events by name
   - Filter by location and date

4. Event Details
   - Detailed event information
   - Interactive map
   - Organizer contact
   - Ticket booking options

5. Booking Cart
   - Add/remove tickets
   - Update quantities
   - Cart management

6. Checkout Process
   - Payment simulation
   - Booking confirmation

7. Booking History
   - Past and upcoming bookings
   - Ticket downloads
   - QR code generation

8. Admin Panel
   - Event management
   - Booking overview
   - Report generation

## Installation

1. Clone the repository
2. Import the database schema from `database/event_booking.sql`
3. Configure database connection in `config/database.php`
4. Start your local server
5. Access the application through your web browser

## Technology Stack

- Frontend: HTML5, CSS3, Bootstrap 5, JavaScript
- Backend: PHP
- Database: MySQL
- Additional Libraries: 
  - jQuery for AJAX requests
  - Leaflet.js for maps
  - QRCode.js for QR code generation

## Directory Structure

```
event-booking/
├── admin/             # Admin panel files
├── assets/           # Static assets (CSS, JS, images)
├── config/           # Configuration files
├── database/         # Database schema and migrations
├── includes/         # PHP includes and functions
├── uploads/          # Event images and user uploads
└── user/             # User-specific functionality
```

## Security Features

- Password hashing
- SQL injection prevention
- XSS protection
- CSRF protection
- Input validation

## Contributors

- [Your Name]

## License

MIT License 