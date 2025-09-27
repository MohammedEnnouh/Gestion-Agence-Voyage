package com.example.vogie2.api;
import com.example.vogie2.models.ApiResponse;
import com.example.vogie2.models.Client;
import java.util.List;
import java.util.Map;
import retrofit2.Call;
import retrofit2.http.Body;
import retrofit2.http.DELETE;
import retrofit2.http.GET;
import retrofit2.http.POST;
import retrofit2.http.PUT;
import retrofit2.http.Query;
public interface ClientsApi {
    @GET("clients.php")
    Call<ApiResponse<List<Client>>> list(@Query("q") String q);
    @POST("clients.php")
    Call<ApiResponse<Map<String, Object>>> create(@Body Client payload);
    @PUT("clients.php")
    Call<ApiResponse<Map<String, Object>>> update(@Query("id") int id, @Body Client payload);
    @DELETE("clients.php")
    Call<ApiResponse<Map<String, Object>>> delete(@Query("id") int id);
}