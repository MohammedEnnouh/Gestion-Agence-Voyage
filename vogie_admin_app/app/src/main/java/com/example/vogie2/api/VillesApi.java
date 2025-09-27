package com.example.vogie2.api;
import com.example.vogie2.models.ApiResponse;
import com.example.vogie2.models.Ville;
import java.util.List;
import java.util.Map;
import retrofit2.Call;
import retrofit2.http.Body;
import retrofit2.http.DELETE;
import retrofit2.http.GET;
import retrofit2.http.POST;
import retrofit2.http.Query;
public interface VillesApi {
    @GET("villes.php")
    Call<ApiResponse<List<Ville>>> list();
    @POST("villes.php")
    Call<ApiResponse<Map<String, Object>>> create(@Body Ville payload);
    @DELETE("villes.php")
    Call<ApiResponse<Map<String, Object>>> delete(@Query("id") int id);
}