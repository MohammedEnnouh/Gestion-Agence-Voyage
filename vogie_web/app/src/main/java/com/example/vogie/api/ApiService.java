package com.example.vogie.api;
import com.example.vogie.model.Booking;
import com.example.vogie.model.City;
import com.example.vogie.model.Trip;
import com.example.vogie.model.User;
import com.example.vogie.model.Route;
import java.util.List;
import retrofit2.Call;
import retrofit2.http.Field;
import retrofit2.http.FormUrlEncoded;
import retrofit2.http.GET;
import retrofit2.http.POST;
import retrofit2.http.Query;
public interface ApiService {

    @GET("api/cities.php")
    Call<ApiResponse<List<City>>> getCities();

    @GET("api/search_trips.php")
    Call<ApiResponse<List<Trip>>> searchTrips(
        @Query("from_id") int fromId,
        @Query("to_id") int toId,
        @Query("date") String date,
        @Query("pax") int passengers
    );

    @GET("api/destinations.php")
    Call<ApiResponse<List<City>>> getDestinations();

    @GET("api/price.php")
    Call<ApiResponse<PriceResponse>> getPrice(
        @Query("from_id") int fromId,
        @Query("to_id") int toId
    );

    @GET("api/routes.php")
    Call<ApiResponse<List<Route>>> getRoutes();

    @GET("api/route_times.php")
    Call<ApiResponse<List<String>>> getRouteTimes(
        @Query("route_id") int routeId
    );

    @FormUrlEncoded
    @POST("register.php")
    Call<ApiResponse<User>> register(
        @Field("full_name") String fullName,
        @Field("email") String email,
        @Field("phone") String phone,
        @Field("password") String password,
        @Field("confirm_password") String confirmPassword
    );

    @FormUrlEncoded
    @POST("login.php")
    Call<ApiResponse<User>> login(
        @Field("email") String email,
        @Field("password") String password
    );

    @FormUrlEncoded
    @POST("api/bookings.php")
    Call<ApiResponse<Booking>> createBooking(
        @Field("full_name") String fullName,
        @Field("email") String email,
        @Field("phone") String phone,
        @Field("from_city_id") int fromCityId,
        @Field("to_city_id") int toCityId,
        @Field("departure_date") String departureDate,
        @Field("departure_time") String departureTime,
        @Field("passenger_count") int passengerCount,
        @Field("payment_method") String paymentMethod
    );

    @GET("api/bookings.php")
    Call<ApiResponse<List<Booking>>> getUserBookings(
        @Query("user_id") int userId
    );

    @GET("dashboard.php")
    Call<ApiResponse<DashboardData>> getDashboardData(
        @Query("user_id") int userId
    );

    class PriceResponse {
        public double price_per_person;
        public double total_price;
    }
    class DashboardData {
        public List<Booking> upcoming_bookings;
        public int wishlist_count;
        public int total_bookings;
        public List<Activity> recent_activities;
        public static class Activity {
            public String type;
            public String title;
            public String message;
            public String time_ago;
            public String icon;
            public String color;
        }
    }
}