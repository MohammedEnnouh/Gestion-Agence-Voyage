package com.example.vogie;
import android.content.Intent;
import android.os.Bundle;
import android.view.View;
import android.widget.ArrayAdapter;
import android.widget.Toast;
import androidx.appcompat.app.AppCompatActivity;
import androidx.recyclerview.widget.GridLayoutManager;
import com.example.vogie.adapter.CityAdapter;
import com.example.vogie.api.ApiClient;
import com.example.vogie.api.ApiResponse;
import com.example.vogie.api.ApiService;
import com.example.vogie.databinding.ActivityMainBinding;
import com.example.vogie.model.City;
import com.google.android.material.datepicker.MaterialDatePicker;
import java.text.SimpleDateFormat;
import java.util.ArrayList;
import java.util.Calendar;
import java.util.List;
import java.util.Locale;
import retrofit2.Call;
import retrofit2.Callback;
import retrofit2.Response;
public class MainActivity extends AppCompatActivity {
    private ActivityMainBinding binding;
    private ApiService apiService;
    private List<City> cities = new ArrayList<>();
    private List<City> destinations = new ArrayList<>();
    private CityAdapter destinationAdapter;
    private ArrayAdapter<City> fromCityAdapter, toCityAdapter;
    private Calendar selectedDate;
    private SimpleDateFormat dateFormat;
    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        binding = ActivityMainBinding.inflate(getLayoutInflater());
        setContentView(binding.getRoot());
        apiService = ApiClient.getApiService();
        selectedDate = Calendar.getInstance();
        dateFormat = new SimpleDateFormat("yyyy-MM-dd", Locale.getDefault());
        setupUI();
        loadCities();
        loadDestinations();
    }
    private void setupUI() {

        setSupportActionBar(binding.toolbar);
        if (getSupportActionBar() != null) {
            getSupportActionBar().setTitle("Vogie - Votre compagnon de voyage");
        }

        binding.dateInput.setOnClickListener(v -> showDatePicker());

        binding.searchButton.setOnClickListener(v -> performSearch());

        if (binding.destinationsRecyclerView != null) {
            destinationAdapter = new CityAdapter(destinations, city -> {

                Intent intent = new Intent(this, CityDetailActivity.class);
                intent.putExtra("city", city);
                startActivity(intent);
            });
            binding.destinationsRecyclerView.setLayoutManager(new GridLayoutManager(this, 2));
            binding.destinationsRecyclerView.setAdapter(destinationAdapter);
        }

        fromCityAdapter = new ArrayAdapter<>(this, android.R.layout.simple_spinner_item, cities);
        fromCityAdapter.setDropDownViewResource(android.R.layout.simple_spinner_dropdown_item);
        binding.fromCitySpinner.setAdapter(fromCityAdapter);
        toCityAdapter = new ArrayAdapter<>(this, android.R.layout.simple_spinner_item, cities);
        toCityAdapter.setDropDownViewResource(android.R.layout.simple_spinner_dropdown_item);
        binding.toCitySpinner.setAdapter(toCityAdapter);

        String[] passengerCounts = {"1 passager", "2 passagers", "3 passagers", "4 passagers", "5 passagers"};
        ArrayAdapter<String> passengerAdapter = new ArrayAdapter<>(this, android.R.layout.simple_spinner_item, passengerCounts);
        passengerAdapter.setDropDownViewResource(android.R.layout.simple_spinner_dropdown_item);
        binding.passengerSpinner.setAdapter(passengerAdapter);

        binding.fromCitySpinner.setOnItemSelectedListener(new android.widget.AdapterView.OnItemSelectedListener() {
            @Override
            public void onItemSelected(android.widget.AdapterView<?> parent, View view, int position, long id) {
                updatePriceDisplay();
            }
            @Override
            public void onNothingSelected(android.widget.AdapterView<?> parent) {}
        });
        binding.toCitySpinner.setOnItemSelectedListener(new android.widget.AdapterView.OnItemSelectedListener() {
            @Override
            public void onItemSelected(android.widget.AdapterView<?> parent, View view, int position, long id) {
                updatePriceDisplay();
            }
            @Override
            public void onNothingSelected(android.widget.AdapterView<?> parent) {}
        });
    }
    private void loadCities() {
        apiService.getCities().enqueue(new Callback<ApiResponse<List<City>>>() {
            @Override
            public void onResponse(Call<ApiResponse<List<City>>> call, Response<ApiResponse<List<City>>> response) {
                if (response.isSuccessful() && response.body() != null && response.body().success) {
                    cities.clear();
                    cities.addAll(response.body().data);
                    fromCityAdapter.notifyDataSetChanged();
                    toCityAdapter.notifyDataSetChanged();
                } else {
                    Toast.makeText(MainActivity.this, "Erreur de chargement des villes", Toast.LENGTH_SHORT).show();
                }
            }
            @Override
            public void onFailure(Call<ApiResponse<List<City>>> call, Throwable t) {
                Toast.makeText(MainActivity.this, "Erreur de chargement des villes: " + t.getMessage(), Toast.LENGTH_SHORT).show();
            }
        });
    }
    private void loadDestinations() {
        apiService.getDestinations().enqueue(new Callback<ApiResponse<List<City>>>() {
            @Override
            public void onResponse(Call<ApiResponse<List<City>>> call, Response<ApiResponse<List<City>>> response) {
                if (response.isSuccessful() && response.body() != null && response.body().success) {
                    destinations.clear();
                    destinations.addAll(response.body().data);
                    if (destinationAdapter != null) {
                        destinationAdapter.notifyDataSetChanged();
                    }
                } else {
                    Toast.makeText(MainActivity.this, "Erreur de chargement des destinations", Toast.LENGTH_SHORT).show();
                }
            }
            @Override
            public void onFailure(Call<ApiResponse<List<City>>> call, Throwable t) {
                Toast.makeText(MainActivity.this, "Erreur de chargement des destinations: " + t.getMessage(), Toast.LENGTH_SHORT).show();
            }
        });
    }
    private void showDatePicker() {
        MaterialDatePicker<Long> datePicker = MaterialDatePicker.Builder.datePicker()
                .setTitleText("Sélectionner la date de départ")
                .setSelection(MaterialDatePicker.todayInUtcMilliseconds())
                .build();
        datePicker.show(getSupportFragmentManager(), "DATE_PICKER");
        datePicker.addOnPositiveButtonClickListener(selection -> {
            selectedDate.setTimeInMillis(selection);
            binding.dateInput.setText(dateFormat.format(selectedDate.getTime()));
        });
    }
    private void updatePriceDisplay() {
        City fromCity = (City) binding.fromCitySpinner.getSelectedItem();
        City toCity = (City) binding.toCitySpinner.getSelectedItem();
        if (fromCity != null && toCity != null && fromCity.getId() != toCity.getId()) {
            apiService.getPrice(fromCity.getId(), toCity.getId()).enqueue(new Callback<ApiResponse<ApiService.PriceResponse>>() {
                @Override
                public void onResponse(Call<ApiResponse<ApiService.PriceResponse>> call, Response<ApiResponse<ApiService.PriceResponse>> response) {
                    if (response.isSuccessful() && response.body() != null && response.body().success) {
                        double price = response.body().data.price_per_person;
                        int passengers = binding.passengerSpinner.getSelectedItemPosition() + 1;
                        double total = price * passengers;
                        if (binding.priceDisplay != null) {
                            binding.priceDisplay.setText(String.format(Locale.getDefault(),
                                "Prix: %.2f € par personne\nTotal: %.2f €", price, total));
                            binding.priceDisplay.setVisibility(View.VISIBLE);
                        }
                    }
                }
                @Override
                public void onFailure(Call<ApiResponse<ApiService.PriceResponse>> call, Throwable t) {
                    if (binding.priceDisplay != null) {
                        binding.priceDisplay.setText("Prix non disponible");
                    }
                }
            });
        }
    }
    private void performSearch() {

        City fromCity = (City) binding.fromCitySpinner.getSelectedItem();
        City toCity = (City) binding.toCitySpinner.getSelectedItem();
        if (fromCity == null || toCity == null) {
            Toast.makeText(this, "Veuillez sélectionner les villes de départ et d'arrivée", Toast.LENGTH_SHORT).show();
            return;
        }
        if (fromCity.getId() == toCity.getId()) {
            Toast.makeText(this, "Les villes de départ et d'arrivée doivent être différentes", Toast.LENGTH_SHORT).show();
            return;
        }

        Intent intent = new Intent(this, SearchResultsActivity.class);
        intent.putExtra("from_city_id", fromCity.getId());
        intent.putExtra("to_city_id", toCity.getId());
        intent.putExtra("date", dateFormat.format(selectedDate.getTime()));
        intent.putExtra("passenger_count", binding.passengerSpinner.getSelectedItemPosition() + 1);
        startActivity(intent);
    }
}