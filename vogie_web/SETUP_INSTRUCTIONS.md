# Vogie Application Setup Instructions

## Overview
This project contains both an Android mobile application and a web application for the Vogie travel booking system. The applications are now properly connected to share data through a web API.

## What Has Been Fixed

### 1. Database Connection Issues ✅
- Android app now properly connects to the web database through API endpoints
- Cities are fetched from the web database instead of being hardcoded
- Fallback to hardcoded cities if API fails

### 2. Launch/Test Payment Screens Removed ✅
- App now goes directly from splash screen to main application
- Removed LauncherActivity and TestPaymentFlowActivity
- Streamlined user experience

### 3. Logo Integration ✅
- Added the new V logo to both Android app and web application
- Logo appears in navigation bars, splash screens, and destination cards
- Consistent branding across platforms

### 4. Cities Display Fixed ✅
- Cities now load from the database and display properly
- Search functionality works with real city data
- Proper error handling and loading states

## Setup Steps

### Step 1: Database Setup
1. Make sure you have XAMPP running with MySQL
2. Create a database named `vogie_web` in phpMyAdmin
3. Run the setup script: `http://localhost/vogie/web/setup_cities.php`
4. This will create the cities table and populate it with Moroccan cities

### Step 2: Web Application
1. Place the web folder in your XAMPP htdocs directory
2. Access via: `http://localhost/vogie/web/`
3. The logo should appear in the navigation and hero section
4. Cities should load from the database in the destinations section

### Step 3: Android Application
1. Open the project in Android Studio
2. Build and run the application
3. The app should:
   - Show the V logo on splash screen
   - Go directly to the main cities list
   - Display cities fetched from the web API
   - Use the V logo for all city images

## API Endpoints

The following API endpoints are now working:

- `GET /api/cities.php` - Returns all cities with descriptions
- `GET /api/destinations.php` - Returns popular destinations
- `GET /api/search_trips.php` - Search for available trips
- `GET /api/price.php` - Get pricing information
- `GET /api/bookings.php` - Get user bookings

## File Structure

```
vogie/
├── app/                          # Android application
│   ├── src/main/
│   │   ├── java/com/example/vogie/
│   │   │   ├── MainActivity.java     # Main cities list
│   │   │   ├── SplashActivity.java   # Splash screen with logo
│   │   │   ├── api/                  # API integration
│   │   │   └── model/                # Data models
│   │   └── res/
│   │       └── drawable/
│   │           └── v.png             # V logo
├── web/                         # Web application
│   ├── api/                     # API endpoints
│   ├── assets/
│   │   └── images/
│   │       └── v.png            # V logo
│   ├── includes/
│   │   └── header.php           # Navigation with logo
│   └── index.php                # Homepage with logo
└── setup_cities.php             # Database setup script
```

## Troubleshooting

### Cities Not Showing
1. Check if the database exists and has data
2. Run `setup_cities.php` to populate the database
3. Check browser console for API errors
4. Verify XAMPP is running and accessible

### Logo Not Displaying
1. Ensure `v.png` is copied to both `app/src/main/res/drawable/` and `web/assets/images/`
2. Check file permissions
3. Clear browser cache and Android app cache

### API Connection Issues
1. Verify the database configuration in `web/config/config.php`
2. Check if the cities table exists and has data
3. Test API endpoints directly in browser
4. Check Android app logs for network errors

## Features

### Android App
- ✅ Splash screen with V logo
- ✅ Direct navigation to main app
- ✅ Cities loaded from web database
- ✅ Search functionality
- ✅ City details and booking flow
- ✅ V logo used throughout

### Web Application
- ✅ Navigation with V logo
- ✅ Hero section with logo
- ✅ Cities loaded from database
- ✅ Search and booking forms
- ✅ Responsive design
- ✅ Consistent branding

## Next Steps

1. **Test the complete flow**: From web search to Android booking
2. **Add more cities**: Expand the database with additional destinations
3. **Enhance images**: Add actual city photos instead of logo placeholders
4. **Payment integration**: Complete the Stripe payment flow
5. **User authentication**: Implement login/registration system

## Support

If you encounter any issues:
1. Check the browser console for JavaScript errors
2. Check Android Studio logs for app errors
3. Verify database connectivity
4. Ensure all files are in the correct locations

The application should now work seamlessly with cities displaying properly and the logo appearing consistently across both platforms. 