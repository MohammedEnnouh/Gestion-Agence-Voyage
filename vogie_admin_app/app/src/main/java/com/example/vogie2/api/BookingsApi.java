package com.example.vogie2.api;
import com.example.vogie2.models.ApiResponse;
import com.example.vogie2.models.Booking;
import java.util.List;
import java.util.Map;
import retrofit2.Call;
import retrofit2.http.Body;
import retrofit2.http.DELETE;
import retrofit2.http.GET;
import retrofit2.http.POST;
import retrofit2.http.PUT;
import retrofit2.http.Query;
public interface BookingsApi {
    @GET("bookings.php")
    Call<ApiResponse<List<Booking>>> list(@Query("status") String status);
    @POST("bookings.php")
    Call<ApiResponse<Map<String, Object>>> create(@Body Map<String, Object> payload);
    @PUT("bookings.php")
    Call<ApiResponse<Map<String, Object>>> update(@Query("id") int id, @Body Map<String, Object> payload);
    @DELETE("bookings.php")
    Call<ApiResponse<Map<String, Object>>> delete(@Query("id") int id);
}