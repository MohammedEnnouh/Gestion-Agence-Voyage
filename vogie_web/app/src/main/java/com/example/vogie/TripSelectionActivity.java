package com.example.vogie;
import android.content.Intent;
import android.os.Bundle;
import android.view.View;
import android.widget.Toast;
import androidx.appcompat.app.AppCompatActivity;
import androidx.recyclerview.widget.LinearLayoutManager;
import com.example.vogie.adapter.TripAdapter;
import com.example.vogie.api.ApiClient;
import com.example.vogie.api.ApiResponse;
import com.example.vogie.api.ApiService;
import com.example.vogie.databinding.ActivityTripSelectionBinding;
import com.example.vogie.model.Trip;
import java.util.ArrayList;
import java.util.List;
import retrofit2.Call;
import retrofit2.Callback;
import retrofit2.Response;
public class TripSelectionActivity extends AppCompatActivity {
    private ActivityTripSelectionBinding binding;
    private ApiService apiService;
    private List<Trip> trips = new ArrayList<>();
    private TripAdapter tripAdapter;
    private int fromId, toId, passengers;
    private String date, fromName, toName;
    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        binding = ActivityTripSelectionBinding.inflate(getLayoutInflater());
        setContentView(binding.getRoot());
        apiService = ApiClient.getApiService();

        fromId = getIntent().getIntExtra("from_id", 0);
        toId = getIntent().getIntExtra("to_id", 0);
        date = getIntent().getStringExtra("date");
        passengers = getIntent().getIntExtra("passengers", 1);
        fromName = getIntent().getStringExtra("from_name");
        toName = getIntent().getStringExtra("to_name");
        setupUI();
        loadTrips();
    }
    private void setupUI() {

        setSupportActionBar(binding.toolbar);
        if (getSupportActionBar() != null) {
            getSupportActionBar().setDisplayHomeAsUpEnabled(true);
            getSupportActionBar().setTitle("Résultats de recherche");
        }

        String routeInfo = String.format("%s  %s | Date: %s | Passagers: %d",
            fromName != null ? fromName : "Ville",
            toName != null ? toName : "Ville",
            date != null ? date : "",
            passengers);
        binding.routeInfo.setText(routeInfo);

        tripAdapter = new TripAdapter(trips, trip -> {

            Intent intent = new Intent(this, BookingActivity.class);
            intent.putExtra("from_id", fromId);
            intent.putExtra("to_id", toId);
            intent.putExtra("date", date);
            intent.putExtra("time", trip.getTime());
            intent.putExtra("passengers", passengers);
            intent.putExtra("price", trip.getPricePerPerson());
            intent.putExtra("from_name", fromName);
            intent.putExtra("to_name", toName);
            startActivity(intent);
        });
        binding.tripsRecyclerView.setLayoutManager(new LinearLayoutManager(this));
        binding.tripsRecyclerView.setAdapter(tripAdapter);
    }
    private void loadTrips() {
        binding.progressBar.setVisibility(View.VISIBLE);
        binding.noResultsText.setVisibility(View.GONE);
        apiService.searchTrips(fromId, toId, date, passengers).enqueue(new Callback<ApiResponse<List<Trip>>>() {
            @Override
            public void onResponse(Call<ApiResponse<List<Trip>>> call, Response<ApiResponse<List<Trip>>> response) {
                binding.progressBar.setVisibility(View.GONE);
                if (response.isSuccessful() && response.body() != null && response.body().success) {
                    trips.clear();
                    trips.addAll(response.body().data);
                    tripAdapter.notifyDataSetChanged();
                    if (trips.isEmpty()) {
                        binding.noResultsText.setVisibility(View.VISIBLE);
                        binding.noResultsText.setText("Aucun trajet disponible pour cette date");
                    }
                } else {
                    binding.noResultsText.setVisibility(View.VISIBLE);
                    binding.noResultsText.setText("Erreur lors de la recherche");
                    Toast.makeText(TripSelectionActivity.this, "Erreur lors de la recherche", Toast.LENGTH_SHORT).show();
                }
            }
            @Override
            public void onFailure(Call<ApiResponse<List<Trip>>> call, Throwable t) {
                binding.progressBar.setVisibility(View.GONE);
                binding.noResultsText.setVisibility(View.VISIBLE);
                binding.noResultsText.setText("Erreur de connexion");
                Toast.makeText(TripSelectionActivity.this, "Erreur de connexion: " + t.getMessage(), Toast.LENGTH_SHORT).show();
            }
        });
    }
    @Override
    public boolean onSupportNavigateUp() {
        onBackPressed();
        return true;
    }
}