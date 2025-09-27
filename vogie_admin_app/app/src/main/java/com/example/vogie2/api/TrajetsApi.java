package com.example.vogie2.api;
import com.example.vogie2.models.ApiResponse;
import com.example.vogie2.models.Trajet;
import java.util.List;
import java.util.Map;
import retrofit2.Call;
import retrofit2.http.Body;
import retrofit2.http.DELETE;
import retrofit2.http.GET;
import retrofit2.http.POST;
import retrofit2.http.PUT;
import retrofit2.http.Query;
public interface TrajetsApi {
    @GET("trajets.php")
    Call<ApiResponse<List<Trajet>>> list(@Query("q") String q);
    @POST("trajets.php")
    Call<ApiResponse<Map<String, Object>>> create(@Body Trajet payload);
    @PUT("trajets.php")
    Call<ApiResponse<Map<String, Object>>> update(@Query("id") int id, @Body Trajet payload);
    @DELETE("trajets.php")
    Call<ApiResponse<Map<String, Object>>> delete(@Query("id") int id);
}