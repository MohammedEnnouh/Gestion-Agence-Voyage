# Vogie Web Application

This is the web version of the Vogie travel booking application, built with PHP, MySQL, and Stripe for payments.

## Prerequisites

- PHP 7.4 or higher
- MySQL 5.7 or higher
- Composer (for PHP dependencies)
- Web server (Apache/Nginx)
- XAMPP/WAMP/MAMP (for local development)

## Installation

1. **Clone the repository**
   ```bash
   git clone [repository-url] vogie-web
   cd vogie-web
   ```

2. **Install PHP dependencies**
   ```bash
   composer install
   ```

3. **Set up the database**
   - Create a new MySQL database named `vogie_web`
   - Import the database schema from `database/vogie_web.sql`
   ```bash
   mysql -u [username] -p vogie_web < database/vogie_web.sql
   ```

4. **Configure the application**
   - Copy `.env.example` to `.env` and update the following values:
     ```
     DB_HOST=localhost
     DB_NAME=vogie_web
     DB_USER=your_db_username
     DB_PASS=your_db_password
     
     STRIPE_PUBLIC_KEY=pk_test_51Pe5DrDW9NzzBWTtvTKSAGaCxmzOb1us4eve170c9zYIcknAh3I4HcQhnUhfyLk8kd1ML8u4qmOzX8Pzi0WHOfvn00ujylHqyb
     STRIPE_SECRET_KEY=sk_test_xxxxxxxxxxxxxxxxx
     
     BASE_URL=http://localhost/vogie/web
     ```

5. **Set up the web server**
   - Point your web server's document root to the `public` directory
   - Make sure the `uploads` directory is writable by the web server
   - Enable URL rewriting (mod_rewrite for Apache)

6. **Install SSL certificate (for production)**
   - Required for Stripe payments
   - You can use Let's Encrypt for free SSL certificates

## Directory Structure

```
vogie-web/
├── api/                  # API endpoints
├── assets/               # Static assets (CSS, JS, images)
│   ├── css/
│   ├── js/
│   └── images/
├── config/               # Configuration files
├── database/             # Database schema and migrations
├── includes/             # PHP includes and helper functions
├── uploads/              # User uploads (QR codes, etc.)
├── vendor/               # Composer dependencies
├── .env                  # Environment variables
├── .htaccess             # Apache configuration
├── composer.json         # PHP dependencies
├── composer.lock         # Lock file for Composer
├── index.php             # Entry point
└── README.md             # This file
```

## Features

- User registration and authentication
- Destination browsing
- Online booking system
- Secure payment processing with Stripe
- QR code generation for cash payments
- Responsive design for all devices
- Admin dashboard (coming soon)

## Security

- CSRF protection
- SQL injection prevention
- XSS protection
- Secure password hashing
- Input validation and sanitization

## Testing

To run tests:

```bash
composer test
```

## Deployment

1. Set up a production environment with PHP and MySQL
2. Configure your web server (Apache/Nginx)
3. Set up an SSL certificate (Let's Encrypt)
4. Update the `.env` file with production values
5. Set the following PHP settings:
   ```
   display_errors = Off
   error_reporting = E_ALL & ~E_DEPRECATED & ~E_STRICT
   log_errors = On
   ```

## License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## Support

For support, please contact support@vogie.com
