# Vogie Android Application

This Android application mirrors the Vogie web application's design and functionality, providing a native mobile experience for booking travel services in Morocco.

## Features

### 🏠 Home Screen
- **Search Form**: Plan your trip by selecting departure and arrival cities, date, and number of passengers
- **Popular Destinations**: Browse featured destinations in a beautiful grid layout
- **Modern UI**: Material Design with the same color scheme as the web application

### 🔍 Trip Search
- **Real-time Search**: Search for available trips based on your criteria
- **Trip Selection**: View available departure times with pricing
- **Responsive Design**: Optimized for mobile devices

### 📝 Booking Process
- **Personal Information**: Enter your details (name, email, phone)
- **Payment Options**: Choose between online payment (Stripe) or cash payment
- **Booking Confirmation**: Receive confirmation with booking reference

### 🎨 Design Features
- **Consistent Branding**: Same color scheme and typography as the web app
- **Material Design**: Modern Android UI components
- **Responsive Layout**: Works on different screen sizes
- **Smooth Navigation**: Intuitive user flow

## Technical Stack

### Android Components
- **Language**: Java
- **Minimum SDK**: API 31 (Android 12)
- **Target SDK**: API 35 (Android 15)
- **Architecture**: MVVM with ViewBinding
- **UI Framework**: Material Design Components

### Dependencies
- **Retrofit**: HTTP client for API calls
- **Gson**: JSON parsing
- **Glide**: Image loading
- **Material Design**: UI components
- **Stripe**: Payment processing

### API Integration
- **Base URL**: `http://10.0.2.2/vogie/web/` (for emulator)
- **Endpoints**: 
  - `/api/cities.php` - Get all cities
  - `/api/search_trips.php` - Search available trips
  - `/api/destinations.php` - Get popular destinations
  - `/api/bookings.php` - Create bookings

## Database Integration

The Android app connects to the same MySQL database as the web application:

### Tables Used
- **cities**: Available cities for departure/arrival
- **routes**: Available routes with pricing
- **route_times**: Available departure times
- **users**: User information
- **bookings**: Booking records

### Data Flow
1. App loads cities and destinations from the database
2. User searches for trips using city IDs and date
3. App displays available times and pricing
4. User creates booking with personal information
5. Booking is saved to the database with payment status

## Setup Instructions

### Prerequisites
- Android Studio Arctic Fox or later
- XAMPP with MySQL and Apache
- Vogie web application running on localhost

### Installation
1. Clone the repository
2. Open the project in Android Studio
3. Update the API base URL in `ApiClient.java` if needed:
   - For emulator: `http://10.0.2.2/vogie/web/`
   - For physical device: `http://your-ip/vogie/web/`
4. Build and run the application

### Database Setup
1. Import the `vogie_web.sql` file into your MySQL database
2. Ensure the web application is running and accessible
3. Test the API endpoints in a browser

## Project Structure

```
app/src/main/java/com/example/vogie/
├── api/
│   ├── ApiClient.java          # Retrofit configuration
│   ├── ApiService.java         # API interface
│   └── ApiResponse.java        # Response wrapper
├── model/
│   ├── City.java              # City data model
│   ├── Trip.java              # Trip data model
│   ├── Booking.java           # Booking data model
│   └── User.java              # User data model
├── adapter/
│   ├── DestinationAdapter.java # Popular destinations adapter
│   └── TripAdapter.java        # Available trips adapter
├── HomeActivity.java           # Main search screen
├── TripSelectionActivity.java  # Trip selection screen
├── BookingActivity.java        # Booking form screen
└── ... (other activities)
```

## Color Scheme

The app uses the same color scheme as the web application:

- **Primary Color**: `#E91E63` (Pink)
- **Primary Dark**: `#C2185B` (Dark Pink)
- **Secondary**: `#858796` (Gray)
- **Success**: `#1cc88a` (Green)
- **Warning**: `#f6c23e` (Yellow)
- **Danger**: `#e74a3b` (Red)

## Features Comparison

| Feature | Web App | Android App |
|---------|---------|-------------|
| Trip Search | ✅ | ✅ |
| City Selection | ✅ | ✅ |
| Date Picker | ✅ | ✅ |
| Passenger Count | ✅ | ✅ |
| Trip Selection | ✅ | ✅ |
| Booking Form | ✅ | ✅ |
| Payment Options | ✅ | ✅ |
| Booking Confirmation | ✅ | ✅ |
| Popular Destinations | ✅ | ✅ |
| Responsive Design | ✅ | ✅ |

## Future Enhancements

- [ ] User authentication and profiles
- [ ] Booking history
- [ ] Push notifications
- [ ] Offline support
- [ ] Multi-language support
- [ ] Dark mode
- [ ] QR code generation for tickets

## Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Test thoroughly
5. Submit a pull request

## License

This project is part of the Vogie travel booking system. 