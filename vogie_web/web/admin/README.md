# VOGIE Admin Panel

## Installation

1. **Create Admin User**
   - Run the SQL script `create_admin.sql` in your database
   - This creates an admin user with:
     - Email: admin@vogie.com
     - Password: admin123

2. **Access Admin Panel**
   - Navigate to `/web/admin/` in your browser
   - Login with the admin credentials

## Features

### Dashboard
- Overview statistics
- Quick access to all admin functions

### Cities Management
- Add new cities
- Edit existing cities
- Delete cities (if not used in routes)

### Routes Management
- Add new routes between cities
- Set prices per person
- Delete routes

### Cash Payments Management
- View all cash payments
- Confirm pending cash payments
- Edit payment details
- Delete payments

### User Management
- View all users
- Manage user accounts

## Security

- Only users with `role = 'admin'` can access the admin panel
- Session-based authentication
- All admin actions are logged

## File Structure

```
web/admin/
├── index.php          # Redirects to dashboard
├── dashboard.php      # Main admin dashboard
├── login.php          # Admin login
├── logout.php         # Admin logout
├── auth.php           # Authentication middleware
├── cities.php         # Cities management
├── routes.php         # Routes management
├── cash-payments.php  # Cash payments management
├── users.php          # User management
├── create_admin.sql   # SQL to create admin user
└── README.md          # This file
```

## Usage

1. **Login**: Use admin credentials to access the panel
2. **Navigate**: Use the sidebar to access different sections
3. **Manage**: Add, edit, or delete items as needed
4. **Monitor**: Check dashboard for system overview

## Customization

- Modify the admin user credentials in `create_admin.sql`
- Add new admin features by creating new PHP files
- Customize the styling by modifying CSS classes

## Support

For issues or questions, check the main application logs or contact the development team. 