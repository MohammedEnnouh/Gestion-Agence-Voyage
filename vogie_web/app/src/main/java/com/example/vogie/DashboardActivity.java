package com.example.vogie;
import android.content.SharedPreferences;
import android.os.Bundle;
import android.view.View;
import android.widget.Toast;
import androidx.appcompat.app.AppCompatActivity;
import androidx.recyclerview.widget.LinearLayoutManager;
import com.example.vogie.adapter.BookingAdapter;
import com.example.vogie.adapter.ActivityAdapter;
import com.example.vogie.api.ApiClient;
import com.example.vogie.api.ApiResponse;
import com.example.vogie.api.ApiService;
import com.example.vogie.databinding.ActivityDashboardBinding;
import com.example.vogie.model.Booking;
import java.util.ArrayList;
import java.util.List;
import retrofit2.Call;
import retrofit2.Callback;
import retrofit2.Response;
public class DashboardActivity extends AppCompatActivity {
    private ActivityDashboardBinding binding;
    private ApiService apiService;
    private List<Booking> upcomingBookings = new ArrayList<>();
    private List<ApiService.DashboardData.Activity> recentActivities = new ArrayList<>();
    private BookingAdapter bookingAdapter;
    private ActivityAdapter activityAdapter;
    private int userId;
    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        binding = ActivityDashboardBinding.inflate(getLayoutInflater());
        setContentView(binding.getRoot());
        apiService = ApiClient.getApiService();

        SharedPreferences prefs = getSharedPreferences("vogie_prefs", MODE_PRIVATE);
        userId = prefs.getInt("user_id", 0);
        if (userId == 0) {
            Toast.makeText(this, "Veuillez vous connecter", Toast.LENGTH_SHORT).show();
            finish();
            return;
        }
        setupUI();
        loadDashboardData();
    }
    private void setupUI() {

        setSupportActionBar(binding.toolbar);
        if (getSupportActionBar() != null) {
            getSupportActionBar().setTitle("Tableau de bord");
        }

        bookingAdapter = new BookingAdapter(upcomingBookings, booking -> {

            Toast.makeText(this, "Réservation #" + booking.getId(), Toast.LENGTH_SHORT).show();
        });
        binding.upcomingBookingsRecyclerView.setLayoutManager(new LinearLayoutManager(this, LinearLayoutManager.HORIZONTAL, false));
        binding.upcomingBookingsRecyclerView.setAdapter(bookingAdapter);

        activityAdapter = new ActivityAdapter(recentActivities);
        binding.recentActivitiesRecyclerView.setLayoutManager(new LinearLayoutManager(this));
        binding.recentActivitiesRecyclerView.setAdapter(activityAdapter);
    }
    private void loadDashboardData() {
        binding.progressBar.setVisibility(View.VISIBLE);
        apiService.getDashboardData(userId).enqueue(new Callback<ApiResponse<ApiService.DashboardData>>() {
            @Override
            public void onResponse(Call<ApiResponse<ApiService.DashboardData>> call, Response<ApiResponse<ApiService.DashboardData>> response) {
                binding.progressBar.setVisibility(View.GONE);
                if (response.isSuccessful() && response.body() != null && response.body().success) {
                    ApiService.DashboardData data = response.body().data;

                    binding.totalBookingsCount.setText(String.valueOf(data.total_bookings));
                    binding.wishlistCount.setText(String.valueOf(data.wishlist_count));
                    binding.upcomingTripsCount.setText(String.valueOf(data.upcoming_bookings.size()));

                    upcomingBookings.clear();
                    upcomingBookings.addAll(data.upcoming_bookings);
                    bookingAdapter.notifyDataSetChanged();

                    recentActivities.clear();
                    recentActivities.addAll(data.recent_activities);
                    activityAdapter.notifyDataSetChanged();

                    binding.noBookingsText.setVisibility(upcomingBookings.isEmpty() ? View.VISIBLE : View.GONE);
                    binding.noActivitiesText.setVisibility(recentActivities.isEmpty() ? View.VISIBLE : View.GONE);
                } else {
                    Toast.makeText(DashboardActivity.this, "Erreur lors du chargement du tableau de bord", Toast.LENGTH_SHORT).show();
                }
            }
            @Override
            public void onFailure(Call<ApiResponse<ApiService.DashboardData>> call, Throwable t) {
                binding.progressBar.setVisibility(View.GONE);
                Toast.makeText(DashboardActivity.this, "Erreur de connexion: " + t.getMessage(), Toast.LENGTH_SHORT).show();
            }
        });
    }
    @Override
    protected void onResume() {
        super.onResume();
        loadDashboardData();
    }
}