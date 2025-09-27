package com.example.vogie;
import android.content.Intent;
import android.os.Bundle;
import android.view.MenuItem;
import android.view.View;
import android.widget.ImageView;
import android.widget.Toast;
import androidx.annotation.NonNull;
import androidx.appcompat.app.AppCompatActivity;
import androidx.core.content.ContextCompat;
import androidx.recyclerview.widget.GridLayoutManager;
import androidx.recyclerview.widget.LinearLayoutManager;
import androidx.recyclerview.widget.RecyclerView;
import com.example.vogie.adapter.CityAdapter;
import com.example.vogie.adapter.FeaturedDestinationAdapter;
import com.example.vogie.api.ApiClient;
import com.example.vogie.api.ApiService;
import com.example.vogie.databinding.ActivityMainBinding;
import com.example.vogie.model.City;
import com.example.vogie.model.Destination;
import com.google.android.material.bottomnavigation.BottomNavigationView;
import com.google.android.material.datepicker.MaterialDatePicker;
import com.google.android.material.dialog.MaterialAlertDialogBuilder;
import com.google.android.material.snackbar.Snackbar;
import java.text.NumberFormat;
import java.text.SimpleDateFormat;
import java.util.ArrayList;
import java.util.Calendar;
import java.util.Date;
import java.util.List;
import java.util.Locale;
import retrofit2.Call;
import retrofit2.Callback;
import retrofit2.Response;
public class MainActivity extends AppCompatActivity {
    private ActivityMainBinding binding;
    private CityAdapter popularDestinationsAdapter;
    private FeaturedDestinationAdapter featuredDestinationsAdapter;
    private final List<City> popularDestinations = new ArrayList<>();
    private final List<Destination> featuredDestinations = new ArrayList<>();
    private ApiService apiService;
    private final SimpleDateFormat dateFormat = new SimpleDateFormat("MMM dd, yyyy", Locale.getDefault());
    private final NumberFormat currencyFormat = NumberFormat.getCurrencyInstance();
    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        binding = ActivityMainBinding.inflate(getLayoutInflater());
        setContentView(binding.getRoot());

        apiService = ApiClient.getApiService();
        setupToolbar();
        setupBottomNavigation();
        setupSearchViews();
        setupRecyclerViews();
        loadData();

        Calendar calendar = Calendar.getInstance();
        calendar.add(Calendar.DAY_OF_YEAR, 1);
        binding.etDate.setText(dateFormat.format(calendar.getTime()));
        setupSearch();
        setupBottomNavigation();

        loadCitiesFromApi();
    }
    private void setupToolbar() {
        setSupportActionBar(binding.toolbar);
        if (getSupportActionBar() != null) {
            getSupportActionBar().setDisplayShowTitleEnabled(false);
        }

        binding.btnSearch.setOnClickListener(v -> {

            String query = binding.searchView.getQuery().toString().trim();
            if (!query.isEmpty()) {
                searchCities(query);
            }
        });
    }
    private void setupBottomNavigation() {
        binding.bottomNavigation.setOnNavigationItemSelectedListener(item -> {
            int itemId = item.getItemId();
            if (itemId == R.id.nav_home) {

                return true;
            } else if (itemId == R.id.nav_search) {

                binding.appBarLayout.setExpanded(true, true);
                return true;
            } else if (itemId == R.id.nav_favorites) {

                startActivity(new Intent(this, FavoritesActivity.class));
                return true;
            } else if (itemId == R.id.nav_profile) {

                startActivity(new Intent(this, ProfileActivity.class));
                return true;
            }
            return false;
        });
    }
    private void setupSearchViews() {

        binding.etDate.setOnClickListener(v -> showDatePicker());

        binding.etPassengers.setOnClickListener(v -> showPassengerSelector());

        binding.btnSwap.setOnClickListener(v -> {
            String from = binding.etFrom.getText().toString();
            String to = binding.etTo.getText().toString();
            binding.etFrom.setText(to);
            binding.etTo.setText(from);
        });

        binding.btnSearchFlights.setOnClickListener(v -> searchFlights());
    }
    private void setupRecyclerViews() {

        binding.rvFeaturedDestinations.setLayoutManager(
            new LinearLayoutManager(this, LinearLayoutManager.HORIZONTAL, false)
        );
        featuredDestinationsAdapter = new FeaturedDestinationAdapter(featuredDestinations, this::onFeaturedDestinationClick);
        binding.rvFeaturedDestinations.setAdapter(featuredDestinationsAdapter);

        binding.rvPopularDestinations.setLayoutManager(new GridLayoutManager(this, 2));
        popularDestinationsAdapter = new CityAdapter(popularDestinations, this::onPopularDestinationClick);
        binding.rvPopularDestinations.setAdapter(popularDestinationsAdapter);
    }
    private void loadData() {

        loadFeaturedDestinations();

        loadPopularDestinations();
    }
    private void showDatePicker() {
        MaterialDatePicker<Long> datePicker = MaterialDatePicker.Builder.datePicker()
            .setTitleText("Select departure date")
            .setSelection(MaterialDatePicker.todayInUtcMilliseconds())
            .build();
        datePicker.addOnPositiveButtonClickListener(selection -> {
            Calendar calendar = Calendar.getInstance();
            calendar.setTimeInMillis(selection);
            binding.etDate.setText(dateFormat.format(calendar.getTime()));
        });
        datePicker.show(getSupportFragmentManager(), "DATE_PICKER");
    }
    private void showPassengerSelector() {
        String[] items = new String[9];
        for (int i = 0; i < 9; i++) {
            items[i] = String.valueOf(i + 1);
        }
        new MaterialAlertDialogBuilder(this)
            .setTitle("Select number of passengers")
            .setItems(items, (dialog, which) -> {
                binding.etPassengers.setText(items[which]);
            })
            .show();
    }
    private void searchFlights() {
        String from = binding.etFrom.getText().toString().trim();
        String to = binding.etTo.getText().toString().trim();
        String date = binding.etDate.getText().toString().trim();
        String passengers = binding.etPassengers.getText().toString().trim();
        if (from.isEmpty() || to.isEmpty()) {
            Snackbar.make(binding.getRoot(), "Please enter both departure and destination cities",
                Snackbar.LENGTH_SHORT).show();
            return;
        }

        Intent intent = new Intent(this, SearchResultsActivity.class);
        intent.putExtra("from", from);
        intent.putExtra("to", to);
        intent.putExtra("date", date);
        intent.putExtra("passengers", passengers);
        startActivity(intent);
    }
    private void onFeaturedDestinationClick(Destination destination) {

        Intent intent = new Intent(this, DestinationDetailsActivity.class);
        intent.putExtra("destination", destination);
        startActivity(intent);
    }
    private void onPopularDestinationClick(City city) {

        binding.etTo.setText(city.getName());
        binding.appBarLayout.setExpanded(true, true);
        binding.etTo.requestFocus();
    }
    private void loadFeaturedDestinations() {

        binding.progressBar.setVisibility(View.VISIBLE);

        new android.os.Handler().postDelayed(() -> {
            featuredDestinations.clear();
            featuredDestinations.add(new Destination("Paris, France", "From $299", R.drawable.paris));
            featuredDestinations.add(new Destination("Tokyo, Japan", "From $899", R.drawable.tokyo));
            featuredDestinations.add(new Destination("New York, USA", "From $399", R.drawable.new_york));
            featuredDestinations.add(new Destination("Bali, Indonesia", "From $599", R.drawable.bali));
            featuredDestinationsAdapter.notifyDataSetChanged();
            binding.progressBar.setVisibility(View.GONE);
        }, 1000);
    }
    private void loadPopularDestinations() {

        binding.progressBar.setVisibility(View.VISIBLE);

        new android.os.Handler().postDelayed(() -> {
            popularDestinations.clear();
            popularDestinations.add(new City("1", "Paris", R.drawable.paris, "City of Love"));
            popularDestinations.add(new City("2", "Tokyo", R.drawable.tokyo, "Vibrant Metropolis"));
            popularDestinations.add(new City("3", "New York", R.drawable.new_york, "The Big Apple"));
            popularDestinations.add(new City("4", "Bali", R.drawable.bali, "Tropical Paradise"));
            popularDestinations.add(new City("5", "Rome", R.drawable.rome, "Eternal City"));
            popularDestinations.add(new City("6", "Sydney", R.drawable.sydney, "Harbor City"));
            popularDestinationsAdapter.notifyDataSetChanged();
            binding.progressBar.setVisibility(View.GONE);
        }, 1000);
    }
    private void setupCitiesList() {
        cityAdapter = new CityAdapter(filteredCities, new CityAdapter.OnItemClickListener() {
            @Override
            public boolean onItemClick(City city) {

                if (city != null) {
                    Intent intent = new Intent(MainActivity.this, CityDetailActivity.class);

                    intent.putExtra("city_id", city.getId());
                    intent.putExtra("city_name", city.getName());
                    intent.putExtra("city_image_res_id", city.getImageResId());
                    intent.putExtra("city_description", city.getDescription());
                    startActivity(intent);
                    return true;
                }
                return false;
            }
        });
        binding.citiesRecyclerView.setLayoutManager(new GridLayoutManager(this, 2));
        binding.citiesRecyclerView.setAdapter(cityAdapter);
        binding.citiesRecyclerView.setHasFixedSize(true);
    }
    private void loadCitiesFromApi() {

        binding.progressBar.setVisibility(View.VISIBLE);
        binding.citiesRecyclerView.setVisibility(View.GONE);
        binding.emptyStateContainer.setVisibility(View.GONE);
        Call<com.example.vogie.api.ApiResponse<List<com.example.vogie.model.City>>> call = apiService.getCities();
        call.enqueue(new Callback<com.example.vogie.api.ApiResponse<List<com.example.vogie.model.City>>>() {
            @Override
            public void onResponse(Call<com.example.vogie.api.ApiResponse<List<com.example.vogie.model.City>>> call, Response<com.example.vogie.api.ApiResponse<List<com.example.vogie.model.City>>> response) {
                binding.progressBar.setVisibility(View.GONE);
                if (response.isSuccessful() && response.body() != null && response.body().isSuccess()) {
                    List<com.example.vogie.model.City> apiCities = response.body().getData();
                    if (apiCities != null && !apiCities.isEmpty()) {
                        cities.clear();

                        for (com.example.vogie.model.City apiCity : apiCities) {
                            cities.add(new City(
                                apiCity.getId(),
                                apiCity.getName(),
                                R.drawable.v,
                                apiCity.getDescription() != null ? apiCity.getDescription() : "Découvrez les merveilles de " + apiCity.getName()
                            ));
                        }
                        filterCities("");
                    } else {
                        showEmptyState("Aucune ville disponible");
                    }
                } else {
                    showEmptyState("Erreur lors du chargement des villes");

                    initializeFallbackCities();
                }
            }
            @Override
            public void onFailure(Call<com.example.vogie.api.ApiResponse<List<com.example.vogie.model.City>>> call, Throwable t) {
                binding.progressBar.setVisibility(View.GONE);
                showEmptyState("Erreur de connexion");

                initializeFallbackCities();
            }
        });
    }
    private void initializeFallbackCities() {

        cities.clear();

        cities.add(new City(1, "Marrakech", R.drawable.v,
            "Marrakech, une ancienne ville impériale dans l'ouest du Maroc, est un centre économique majeur et abrite des mosquées, palais et jardins."));
        cities.add(new City(2, "Chefchaouen", R.drawable.v,
            "Chefchaouen est une ville dans les montagnes du Rif au nord-ouest du Maroc. Elle est connue pour les bâtiments bleus de sa vieille ville."));
        cities.add(new City(3, "Fes", R.drawable.v,
            "Fes est une ville du nord-est du Maroc souvent appelée la capitale culturelle du pays."));
        cities.add(new City(4, "Casablanca", R.drawable.v,
            "Casablanca est une ville portuaire et un centre commercial dans l'ouest du Maroc, face à l'océan Atlantique."));
        cities.add(new City(5, "Rabat", R.drawable.v,
            "Rabat, la capitale du Maroc, s'étend le long des rives de la rivière Bouregreg et de l'océan Atlantique."));
        cities.add(new City(6, "Tangier", R.drawable.v,
            "Tanger est une ville portuaire au Maroc située à l'entrée ouest du détroit de Gibraltar."));
        cities.add(new City(7, "Essaouira", R.drawable.v,
            "Essaouira est une ville portuaire et une station balnéaire sur la côte atlantique du Maroc."));
        cities.add(new City(8, "Meknes", R.drawable.v,
            "Meknes est une ville du nord du Maroc connue pour son passé impérial, avec des monuments historiques."));
        cities.add(new City(9, "Ouarzazate", R.drawable.v,
            "Ouarzazate est une ville dans la région Drâa-Tafilalet du centre-sud du Maroc, connue comme une porte d'entrée vers le désert du Sahara."));
        cities.add(new City(10, "Agadir", R.drawable.v,
            "Agadir est une ville sur la côte atlantique sud du Maroc, connue pour ses plages et ses stations balnéaires."));
        cities.add(new City(11, "Tetouan", R.drawable.v,
            "Tétouan est une ville du nord du Maroc, connue pour son architecture influencée par l'Andalousie."));
        cities.add(new City(12, "Dakhla", R.drawable.v,
            "Dakhla est une ville dans le territoire contesté du Sahara occidental, actuellement administré par le Maroc."));
        filterCities("");
    }
    private void showEmptyState(String message) {
        binding.emptyStateContainer.setVisibility(View.VISIBLE);
        binding.citiesRecyclerView.setVisibility(View.GONE);

    }
    private void setupSearch() {

        android.widget.SearchView searchView = binding.searchView;

        searchView.setQueryHint("Rechercher des villes...");

        searchView.setOnQueryTextListener(new android.widget.SearchView.OnQueryTextListener() {
            @Override
            public boolean onQueryTextSubmit(String query) {
                return false;
            }
            @Override
            public boolean onQueryTextChange(String newText) {
                filterCities(newText);
                return true;
            }
        });

        try {
            int searchIconId = getResources().getIdentifier("search_mag_icon", "id", getPackageName());
            if (searchIconId != 0) {
                ImageView searchIcon = searchView.findViewById(searchIconId);
                if (searchIcon != null) {
                    searchIcon.setColorFilter(ContextCompat.getColor(MainActivity.this, android.R.color.darker_gray));
                }
            }
            int closeButtonId = getResources().getIdentifier("search_close_btn", "id", getPackageName());
            if (closeButtonId != 0) {
                ImageView closeButton = searchView.findViewById(closeButtonId);
                if (closeButton != null) {
                    closeButton.setColorFilter(ContextCompat.getColor(MainActivity.this, android.R.color.darker_gray));
                }
            }
        } catch (Exception e) {
            e.printStackTrace();
        }
    }
    private void filterCities(String query) {
        List<City> filteredList = new ArrayList<>();
        if (query == null || query.isEmpty()) {
            filteredList.addAll(cities);
        } else {
            String searchQuery = query.toLowerCase(Locale.getDefault()).trim();
            for (City city : cities) {

                String cityName = city.getName();
                String cityDesc = city.getDescription();
                if (cityName.toLowerCase(Locale.getDefault()).contains(searchQuery) ||
                    cityDesc.toLowerCase(Locale.getDefault()).contains(searchQuery)) {
                    filteredList.add(city);
                }
            }
        }

        cityAdapter.updateList(filteredList);

        if (filteredList.isEmpty()) {
            binding.emptyStateContainer.setVisibility(View.VISIBLE);
            binding.citiesRecyclerView.setVisibility(View.GONE);
        } else {
            binding.emptyStateContainer.setVisibility(View.GONE);
            binding.citiesRecyclerView.setVisibility(View.VISIBLE);
        }
    }
    private void setupBottomNavigation() {
        binding.bottomNavigation.setSelectedItemId(R.id.nav_home);
        binding.bottomNavigation.setOnItemSelectedListener(item -> {
            int itemId = item.getItemId();
            if (itemId == R.id.nav_home) {

                return true;
            } else if (itemId == R.id.nav_favorites) {
                showFavorites();
                return true;
            } else if (itemId == R.id.nav_profile) {
                showProfile();
                return true;
            }
            return false;
        });
    }
    private void showFavorites() {

        Toast.makeText(this, "Les favoris seront affichés ici", Toast.LENGTH_SHORT).show();
    }
    private void showProfile() {

        Toast.makeText(this, "Le profil sera affiché ici", Toast.LENGTH_SHORT).show();
    }
}