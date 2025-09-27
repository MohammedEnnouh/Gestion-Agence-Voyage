package com.example.vogie;
import android.app.DatePickerDialog;
import android.content.Intent;
import android.os.Bundle;
import android.view.View;
import android.widget.ArrayAdapter;
import android.widget.Spinner;
import android.widget.Toast;
import androidx.appcompat.app.AppCompatActivity;
import androidx.recyclerview.widget.GridLayoutManager;
import com.example.vogie.adapter.DestinationAdapter;
import com.example.vogie.api.ApiClient;
import com.example.vogie.api.ApiService;
import com.example.vogie.databinding.ActivityHomeBinding;
import com.example.vogie.api.ApiResponse;
import com.example.vogie.model.City;
import com.example.vogie.model.Trip;
import java.text.SimpleDateFormat;
import java.util.ArrayList;
import java.util.Calendar;
import java.util.List;
import java.util.Locale;
import retrofit2.Call;
import retrofit2.Callback;
import retrofit2.Response;
public class HomeActivity extends AppCompatActivity {
    private ActivityHomeBinding binding;
    private ApiService apiService;
    private List<City> cities = new ArrayList<>();
    private List<City> destinations = new ArrayList<>();
    private DestinationAdapter destinationAdapter;
    private Calendar calendar;
    private SimpleDateFormat dateFormat;
    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        binding = ActivityHomeBinding.inflate(getLayoutInflater());
        setContentView(binding.getRoot());
        apiService = ApiClient.getApiService();
        calendar = Calendar.getInstance();
        dateFormat = new SimpleDateFormat("yyyy-MM-dd", Locale.getDefault());
        setupToolbar();
        setupSearchForm();
        setupDestinations();
        loadCities();
        loadDestinations();
    }
    private void setupToolbar() {
        setSupportActionBar(binding.toolbar);
        if (getSupportActionBar() != null) {
            getSupportActionBar().setDisplayShowTitleEnabled(false);
        }
        binding.toolbarTitle.setText("Vogie - Votre compagnon de voyage");
    }
    private void setupSearchForm() {

        binding.travelDate.setOnClickListener(v -> showDatePicker());

        binding.searchButton.setOnClickListener(v -> performSearch());

        binding.passengers.setMinValue(1);
        binding.passengers.setMaxValue(10);
        binding.passengers.setValue(1);
    }
    private void setupDestinations() {
        destinationAdapter = new DestinationAdapter(destinations, city -> {

            Toast.makeText(this, "Selected: " + city.getName(), Toast.LENGTH_SHORT).show();
        });
        binding.destinationsRecyclerView.setLayoutManager(new GridLayoutManager(this, 2));
        binding.destinationsRecyclerView.setAdapter(destinationAdapter);
    }
    private void loadCities() {
        apiService.getCities().enqueue(new Callback<ApiResponse<List<City>>>() {
            @Override
            public void onResponse(Call<ApiResponse<List<City>>> call, Response<ApiResponse<List<City>>> response) {
                if (response.isSuccessful() && response.body() != null && response.body().isSuccess()) {
                    cities = response.body().getData();
                    setupCitySpinners();
                } else {
                    Toast.makeText(HomeActivity.this, "Failed to load cities", Toast.LENGTH_SHORT).show();
                }
            }
            @Override
            public void onFailure(Call<ApiResponse<List<City>>> call, Throwable t) {
                Toast.makeText(HomeActivity.this, "Network error: " + t.getMessage(), Toast.LENGTH_SHORT).show();
            }
        });
    }
    private void loadDestinations() {
        apiService.getDestinations().enqueue(new Callback<ApiResponse<List<City>>>() {
            @Override
            public void onResponse(Call<ApiResponse<List<City>>> call, Response<ApiResponse<List<City>>> response) {
                if (response.isSuccessful() && response.body() != null && response.body().isSuccess()) {
                    destinations = response.body().getData();
                    destinationAdapter.updateList(destinations);
                } else {
                    Toast.makeText(HomeActivity.this, "Failed to load destinations", Toast.LENGTH_SHORT).show();
                }
            }
            @Override
            public void onFailure(Call<ApiResponse<List<City>>> call, Throwable t) {
                Toast.makeText(HomeActivity.this, "Network error: " + t.getMessage(), Toast.LENGTH_SHORT).show();
            }
        });
    }
    private void setupCitySpinners() {
        List<String> cityNames = new ArrayList<>();
        for (City city : cities) {
            cityNames.add(city.getName());
        }
        ArrayAdapter<String> adapter = new ArrayAdapter<>(this, android.R.layout.simple_spinner_item, cityNames);
        adapter.setDropDownViewResource(android.R.layout.simple_spinner_dropdown_item);
        binding.fromCity.setAdapter(adapter);
        binding.toCity.setAdapter(adapter);
    }
    private void showDatePicker() {
        DatePickerDialog datePickerDialog = new DatePickerDialog(
                this,
                (view, year, month, dayOfMonth) -> {
                    calendar.set(Calendar.YEAR, year);
                    calendar.set(Calendar.MONTH, month);
                    calendar.set(Calendar.DAY_OF_MONTH, dayOfMonth);
                    binding.travelDate.setText(dateFormat.format(calendar.getTime()));
                },
                calendar.get(Calendar.YEAR),
                calendar.get(Calendar.MONTH),
                calendar.get(Calendar.DAY_OF_MONTH)
        );

        datePickerDialog.getDatePicker().setMinDate(System.currentTimeMillis());
        datePickerDialog.show();
    }
    private void performSearch() {

        if (binding.fromCity.getSelectedItem() == null || binding.toCity.getSelectedItem() == null) {
            Toast.makeText(this, "Please select departure and arrival cities", Toast.LENGTH_SHORT).show();
            return;
        }
        if (binding.travelDate.getText().toString().isEmpty()) {
            Toast.makeText(this, "Please select departure date", Toast.LENGTH_SHORT).show();
            return;
        }

        String fromCityName = binding.fromCity.getSelectedItem().toString();
        String toCityName = binding.toCity.getSelectedItem().toString();
        String date = binding.travelDate.getText().toString();
        int passengers = binding.passengers.getValue();

        City fromCity = findCityByName(fromCityName);
        City toCity = findCityByName(toCityName);
        if (fromCity == null || toCity == null) {
            Toast.makeText(this, "City not found", Toast.LENGTH_SHORT).show();
            return;
        }

        searchTrips(fromCity.getId(), toCity.getId(), date, passengers);
    }
    private City findCityByName(String cityName) {
        for (City city : cities) {
            if (city.getName().equals(cityName)) {
                return city;
            }
        }
        return null;
    }
    private void searchTrips(int fromId, int toId, String date, int passengers) {
        binding.progressBar.setVisibility(View.VISIBLE);
        binding.searchButton.setEnabled(false);
        apiService.searchTrips(fromId, toId, date, passengers).enqueue(new Callback<ApiResponse<List<Trip>>>() {
            @Override
            public void onResponse(Call<ApiResponse<List<Trip>>> call, Response<ApiResponse<List<Trip>>> response) {
                binding.progressBar.setVisibility(View.GONE);
                binding.searchButton.setEnabled(true);
                if (response.isSuccessful() && response.body() != null && response.body().isSuccess()) {
                    List<Trip> trips = response.body().getData();
                    if (trips.isEmpty()) {
                        Toast.makeText(HomeActivity.this, "No trips available for selected criteria", Toast.LENGTH_SHORT).show();
                    } else {

                        Intent intent = new Intent(HomeActivity.this, TripSelectionActivity.class);
                        intent.putExtra("from_city_id", fromId);
                        intent.putExtra("to_city_id", toId);
                        intent.putExtra("date", date);
                        intent.putExtra("passengers", passengers);
                        startActivity(intent);
                    }
                } else {
                    Toast.makeText(HomeActivity.this, "Failed to search trips", Toast.LENGTH_SHORT).show();
                }
            }
            @Override
            public void onFailure(Call<ApiResponse<List<Trip>>> call, Throwable t) {
                binding.progressBar.setVisibility(View.GONE);
                binding.searchButton.setEnabled(true);
                Toast.makeText(HomeActivity.this, "Network error: " + t.getMessage(), Toast.LENGTH_SHORT).show();
            }
        });
    }
}