# Vogie Android App - Updated to Match Web Application

## Overview
This Android app has been updated to match the functionality and design of your web application. The app now includes:

1. **Main Activity** - Matches web index.php with city selection, date picker, and price display
2. **Trip Selection Activity** - Matches web search.php for displaying available trips
3. **Booking Activity** - Matches web booking.php for creating reservations
4. **Dashboard Activity** - Matches web dashboard.php for user dashboard
5. **Updated API Service** - Connects to your web API endpoints

## Key Features

### API Integration
- Connects to your web API endpoints (cities.php, search_trips.php, bookings.php, etc.)
- Uses the same data models as your web application
- Handles JSON responses in the same format

### UI/UX Matching Web Design
- Uses the same color scheme (#E91E63 primary color)
- Similar layout structure and component styling
- Material Design components for modern Android feel

### Functionality Parity
- City selection with price calculation
- Trip search and selection
- Booking creation with user information
- Payment integration ready
- Dashboard with user statistics

## Setup Instructions

### 1. API Configuration
Edit pp/src/main/java/com/example/vogie/api/ApiClient.java:

`java
// Change this to your actual domain
private static final String BASE_URL = "http://your-domain.com/"; 

// For local development:
// Android Emulator: "http://10.0.2.2/vogie/web/"
// Physical Device: "http://your-ip-address/vogie/web/"
`

### 2. Required Dependencies
The app uses these dependencies (already in build.gradle.kts):
- Retrofit for API calls
- Gson for JSON parsing
- Material Design Components
- Glide for image loading
- OkHttp logging for debugging

### 3. Build the App
1. Open the project in Android Studio
2. Make sure you have Android SDK 35 installed
3. Build and run the app

### 4. Testing
1. Make sure your web server is running
2. Update the BASE_URL in ApiClient.java
3. Test the app on an emulator or physical device

## File Changes Made

### New/Updated Java Files:
- ApiService.java - Updated to match web API endpoints
- ApiClient.java - Enhanced with logging and better configuration
- MainActivity.java - Matches web index.php functionality
- TripSelectionActivity.java - Matches web search.php
- BookingActivity.java - Matches web booking.php
- DashboardActivity.java - New, matches web dashboard.php
- City.java - Updated model with JSON annotations
- Trip.java - Updated model with JSON annotations

### New Adapter Files:
- TripAdapter.java - For displaying trip search results
- BookingAdapter.java - For displaying user bookings
- ActivityAdapter.java - For displaying user activities

### Updated Layout Files:
- ctivity_main.xml - Matches web index design
- ctivity_trip_selection.xml - For trip search results
- ctivity_booking.xml - For booking form
- item_trip.xml - Trip list item layout

### Updated Resources:
- colors.xml - Matches web CSS color variables
- 	hemes.xml - Updated theme styling
- Various drawable resources for UI components

## Web API Endpoints Used

The app connects to these web endpoints:
- GET api/cities.php - Get all cities
- GET api/search_trips.php - Search for trips
- GET api/destinations.php - Get popular destinations
- GET api/price.php - Get route pricing
- POST api/bookings.php - Create bookings
- POST register.php - User registration
- POST login.php - User login
- GET dashboard.php - Dashboard data

## Troubleshooting

### Common Issues:
1. **Network errors**: Check BASE_URL and network connectivity
2. **JSON parsing errors**: Ensure web API returns data in expected format
3. **Build errors**: Make sure all dependencies are properly synced

### Debug Tips:
- Check Android Studio Logcat for API call logs
- Use Chrome DevTools to verify web API responses
- Test API endpoints directly in a browser first

## Next Steps

1. **Test all functionality** with your web server
2. **Add authentication** (login/register activities)
3. **Implement payment processing** (Stripe integration)
4. **Add push notifications** for booking updates
5. **Implement offline caching** for better user experience

## Support

If you encounter any issues:
1. Check the logcat output for error messages
2. Verify your web API is returning the expected JSON format
3. Ensure all required permissions are granted
4. Test API endpoints independently

The app is now ready to work with your existing web application backend!
