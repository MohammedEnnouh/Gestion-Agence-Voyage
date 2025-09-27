package com.example.vogie2.api;
import com.example.vogie2.models.ApiResponse;
import com.example.vogie2.models.User;
import java.util.Map;
import retrofit2.Call;
import retrofit2.http.Body;
import retrofit2.http.POST;
public interface AuthApi {
    @POST("login.php")
    Call<ApiResponse<User>> login(@Body Map<String, String> body);
}