package com.example.vogie2;
import android.app.AlertDialog;
import android.os.Bundle;
import android.view.LayoutInflater;
import android.view.View;
import android.util.Log;
import android.widget.EditText;
import android.widget.Switch;
import android.widget.Toast;
import androidx.annotation.NonNull;
import androidx.annotation.Nullable;
import androidx.appcompat.app.AppCompatActivity;
import androidx.fragment.app.Fragment;
import com.example.vogie2.api.BookingsApi;
import com.example.vogie2.api.ClientsApi;
import com.example.vogie2.api.TrajetsApi;
import com.example.vogie2.api.VillesApi;
import com.example.vogie2.models.ApiResponse;
import com.example.vogie2.models.Client;
import com.example.vogie2.models.Trajet;
import com.example.vogie2.models.Ville;
import com.google.android.material.snackbar.Snackbar;
import java.util.ArrayList;
import java.util.HashMap;
import java.util.List;
import java.util.Map;
import retrofit2.Call;
import retrofit2.Callback;
import retrofit2.Response;
import android.app.DatePickerDialog;
import android.app.TimePickerDialog;
import android.widget.ArrayAdapter;
import android.widget.AutoCompleteTextView;
import com.google.android.material.appbar.MaterialToolbar;
import com.google.android.material.bottomnavigation.BottomNavigationView;
public class HomeActivity extends AppCompatActivity {
    private MaterialToolbar toolbar;
    private BottomNavigationView bottomNav;
    private View globalLoading;
    @Override
    protected void onCreate(@Nullable Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);

        getWindow().setBackgroundDrawableResource(android.R.color.white);
        getWindow().getDecorView().setBackgroundColor(getResources().getColor(android.R.color.white, null));
        setContentView(R.layout.activity_home);

        findViewById(android.R.id.content).setBackgroundColor(getResources().getColor(android.R.color.white, null));
        try {
            toolbar = findViewById(R.id.toolbar);
            setSupportActionBar(toolbar);

            try {
                toolbar.setTitleCentered(false);
                toolbar.setSubtitleCentered(false);
            } catch (NoSuchMethodError ignored) {  }

            int logoRes = getResources().getIdentifier("vogie_logo", "drawable", getPackageName());
            if (logoRes != 0) {
                toolbar.setLogo(logoRes);
            } else {
                toolbar.setLogo(getApplicationInfo().icon);
            }
            if (getSupportActionBar() != null) {
                getSupportActionBar().setDisplayShowHomeEnabled(true);
                getSupportActionBar().setDisplayUseLogoEnabled(true);
                getSupportActionBar().setDisplayShowTitleEnabled(true);
                getSupportActionBar().setTitle("Vogie Dashboard");
            }

            globalLoading = findViewById(R.id.globalLoading);
            bottomNav = findViewById(R.id.bottomNav);
            bottomNav.setOnItemSelectedListener(item -> {
                Fragment f;
                int id = item.getItemId();
                if (id == R.id.nav_dashboard) {
                    f = new DashboardFragment();
                    if (getSupportActionBar()!=null) getSupportActionBar().setTitle("Dashboard");
                } else if (id == R.id.nav_add) {

                    showAddChooser();
                    return false;
                } else if (id == R.id.nav_cities) {
                    f = new VillesFragment();
                    if (getSupportActionBar()!=null) getSupportActionBar().setTitle("Villes");
                } else if (id == R.id.nav_times) {
                    f = new TrajetsFragment();
                    if (getSupportActionBar()!=null) getSupportActionBar().setTitle("Trajets");
                } else if (id == R.id.nav_payments) {
                    f = new PaymentsFragment();
                    if (getSupportActionBar()!=null) getSupportActionBar().setTitle("Paiements");
                } else {
                    f = new DashboardFragment();
                }
                getSupportFragmentManager().beginTransaction()
                        .setCustomAnimations(android.R.anim.fade_in, android.R.anim.fade_out)
                        .replace(R.id.fragmentContainer, f)
                        .commit();
                return true;
            });
            bottomNav.setSelectedItemId(R.id.nav_dashboard);

        } catch (Throwable e) {
            Log.e("HomeActivity", "Initialization error", e);
            Toast.makeText(this, "Erreur d'initialisation: " + e.getClass().getSimpleName(), Toast.LENGTH_LONG).show();

        }
    }
    private void showAddChooser() {
        com.google.android.material.bottomsheet.BottomSheetDialog sheet = new com.google.android.material.bottomsheet.BottomSheetDialog(this);
        android.widget.LinearLayout root = new android.widget.LinearLayout(this);
        root.setOrientation(android.widget.LinearLayout.VERTICAL);
        int pad = (int) (16 * getResources().getDisplayMetrics().density);
        root.setPadding(pad, pad, pad, pad);
        android.widget.TextView t1 = new android.widget.TextView(this); t1.setText("Add User"); t1.setTextSize(16); t1.setPadding(0,pad,0,pad);
        android.widget.TextView t2 = new android.widget.TextView(this); t2.setText("Add Time (Trajet)"); t2.setTextSize(16); t2.setPadding(0,pad,0,pad);
        android.widget.TextView t3 = new android.widget.TextView(this); t3.setText("Add Booking"); t3.setTextSize(16); t3.setPadding(0,pad,0,pad);
        root.addView(t1); root.addView(t2); root.addView(t3);
        t1.setOnClickListener(v -> {
            sheet.dismiss();
            showAddUserDialog();
        });
        t2.setOnClickListener(v -> {
            sheet.dismiss();
            showAddTrajetDialog();
        });
        t3.setOnClickListener(v -> {
            sheet.dismiss();
            showAddBookingDialog();
        });
        sheet.setContentView(root);
        sheet.show();
    }
    public void showGlobalLoading(boolean show) {
        if (globalLoading != null) {
            globalLoading.setVisibility(show ? View.VISIBLE : View.GONE);
        }
    }

    public void selectTab(int menuItemId) {
        if (bottomNav != null) {
            bottomNav.setSelectedItemId(menuItemId);
        }
    }
    @Override
    protected void onResume() {
        super.onResume();

        getWindow().getDecorView().setBackgroundColor(getResources().getColor(android.R.color.white, null));
        findViewById(android.R.id.content).setBackgroundColor(getResources().getColor(android.R.color.white, null));
    }
    private void showAddUserDialog() {
        View d = LayoutInflater.from(this).inflate(R.layout.dialog_user, null, false);
        EditText etName = d.findViewById(R.id.editUserName);
        EditText etEmail = d.findViewById(R.id.editUserEmail);
        EditText etPhone = d.findViewById(R.id.editUserPhone);
        EditText etPassword = d.findViewById(R.id.editUserPassword);
        Switch swActive = d.findViewById(R.id.switchActive);
        new AlertDialog.Builder(this)
                .setTitle("Add User")
                .setView(d)
                .setPositiveButton("Add", (dlg,w)->{
                    String name = etName.getText().toString().trim();
                    String email = etEmail.getText().toString().trim();
                    String phone = etPhone.getText().toString().trim();
                    String password = etPassword.getText().toString().trim();
                    if (name.isEmpty()) {
                        Toast.makeText(this, "Name required", Toast.LENGTH_SHORT).show();
                        return;
                    }
                    if (email.isEmpty()) {
                        Toast.makeText(this, "Email required", Toast.LENGTH_SHORT).show();
                        return;
                    }
                    ClientsApi api = ApiClient.get().create(ClientsApi.class);
                    Client client = new Client();
                    client.full_name = name;
                    client.email = email;
                    client.phone = phone.isEmpty() ? null : phone;
                    client.is_active = swActive.isChecked() ? 1 : 0;
                    api.create(client).enqueue(new Callback<ApiResponse<Map<String, Object>>>() {
                        @Override
                        public void onResponse(Call<ApiResponse<Map<String, Object>>> call, Response<ApiResponse<Map<String, Object>>> response) {
                            if (response.isSuccessful() && response.body() != null && response.body().ok) {
                                Toast.makeText(HomeActivity.this, "User added successfully", Toast.LENGTH_SHORT).show();
                            } else {
                                Toast.makeText(HomeActivity.this, "Error adding user", Toast.LENGTH_SHORT).show();
                            }
                        }
                        @Override
                        public void onFailure(Call<ApiResponse<Map<String, Object>>> call, Throwable t) {
                            Toast.makeText(HomeActivity.this, "Error: " + t.getMessage(), Toast.LENGTH_SHORT).show();
                        }
                    });
                })
                .setNegativeButton("Cancel", null)
                .show();
    }
    private void showAddTrajetDialog() {
        View dialog = LayoutInflater.from(this).inflate(R.layout.dialog_trajet, null, false);
        EditText etDepart = dialog.findViewById(R.id.etDepart);
        EditText etArrivee = dialog.findViewById(R.id.etArrivee);
        EditText etHd = dialog.findViewById(R.id.etHeureDepart);
        EditText etHa = dialog.findViewById(R.id.etHeureArrivee);
        EditText etPrix = dialog.findViewById(R.id.etPrix);
        new AlertDialog.Builder(this)
                .setTitle("Ajouter un trajet")
                .setView(dialog)
                .setPositiveButton("Ajouter", (d, w) -> {
                    String depart = etDepart.getText().toString().trim();
                    String arrivee = etArrivee.getText().toString().trim();
                    String hd = etHd.getText().toString().trim();
                    String ha = etHa.getText().toString().trim();
                    double prix = 0;
                    try { prix = Double.parseDouble(etPrix.getText().toString().trim()); } catch (Exception ignored) {}
                    if (depart.isEmpty() || arrivee.isEmpty() || hd.isEmpty() || ha.isEmpty() || prix <= 0) {
                        Toast.makeText(this, "Champs invalides", Toast.LENGTH_SHORT).show();
                        return;
                    }

                    Toast.makeText(this, "Trajet creation dialog ready", Toast.LENGTH_SHORT).show();
                })
                .setNegativeButton("Annuler", null)
                .show();
    }
    private void showAddBookingDialog() {

        loadBookingData();
    }
    private List<Client> clients = new ArrayList<>();
    private List<Ville> cities = new ArrayList<>();
    private List<Trajet> trajets = new ArrayList<>();
    private int loadingCount = 0;
    private void loadBookingData() {
        loadingCount = 3;

        ClientsApi clientsApi = ApiClient.get().create(ClientsApi.class);
        clientsApi.list(null).enqueue(new Callback<ApiResponse<List<Client>>>() {
            @Override
            public void onResponse(Call<ApiResponse<List<Client>>> call, Response<ApiResponse<List<Client>>> response) {
                if (response.isSuccessful() && response.body() != null && response.body().ok) {
                    clients = response.body().data != null ? response.body().data : new ArrayList<>();
                }
                checkDataLoadingComplete();
            }
            @Override
            public void onFailure(Call<ApiResponse<List<Client>>> call, Throwable t) {
                clients = new ArrayList<>();
                checkDataLoadingComplete();
            }
        });

        VillesApi villesApi = ApiClient.get().create(VillesApi.class);
        villesApi.list().enqueue(new Callback<ApiResponse<List<Ville>>>() {
            @Override
            public void onResponse(Call<ApiResponse<List<Ville>>> call, Response<ApiResponse<List<Ville>>> response) {
                if (response.isSuccessful() && response.body() != null && response.body().ok) {
                    cities = response.body().data != null ? response.body().data : new ArrayList<>();
                }
                checkDataLoadingComplete();
            }
            @Override
            public void onFailure(Call<ApiResponse<List<Ville>>> call, Throwable t) {
                cities = new ArrayList<>();
                checkDataLoadingComplete();
            }
        });

        TrajetsApi trajetsApi = ApiClient.get().create(TrajetsApi.class);
        trajetsApi.list(null).enqueue(new Callback<ApiResponse<List<Trajet>>>() {
            @Override
            public void onResponse(Call<ApiResponse<List<Trajet>>> call, Response<ApiResponse<List<Trajet>>> response) {
                if (response.isSuccessful() && response.body() != null && response.body().ok) {
                    trajets = response.body().data != null ? response.body().data : new ArrayList<>();
                }
                checkDataLoadingComplete();
            }
            @Override
            public void onFailure(Call<ApiResponse<List<Trajet>>> call, Throwable t) {
                trajets = new ArrayList<>();
                checkDataLoadingComplete();
            }
        });
    }
    private void checkDataLoadingComplete() {
        loadingCount--;
        if (loadingCount <= 0) {

            showBookingDialog();
        }
    }
    private void showBookingDialog() {
        View d = LayoutInflater.from(this).inflate(R.layout.dialog_booking, null, false);
        AutoCompleteTextView spinnerClient = d.findViewById(R.id.spinnerClient);
        AutoCompleteTextView spinnerDepartureCity = d.findViewById(R.id.spinnerDepartureCity);
        AutoCompleteTextView spinnerArrivalCity = d.findViewById(R.id.spinnerArrivalCity);
        EditText etDepartureDate = d.findViewById(R.id.etDepartureDate);
        EditText etDepartureTime = d.findViewById(R.id.etDepartureTime);
        EditText etPassengerCount = d.findViewById(R.id.etPassengerCount);
        AutoCompleteTextView spinnerPaymentMethod = d.findViewById(R.id.spinnerPaymentMethod);
        EditText etTotalAmount = d.findViewById(R.id.etTotalAmount);

        String[] clientNames = clients.stream().map(c -> c.full_name).toArray(String[]::new);
        ArrayAdapter<String> clientAdapter = new ArrayAdapter<>(this, android.R.layout.simple_dropdown_item_1line, clientNames);
        spinnerClient.setAdapter(clientAdapter);

        String[] cityNames = cities.stream().map(c -> c.nom).toArray(String[]::new);
        ArrayAdapter<String> departureCityAdapter = new ArrayAdapter<>(this, android.R.layout.simple_dropdown_item_1line, cityNames);
        ArrayAdapter<String> arrivalCityAdapter = new ArrayAdapter<>(this, android.R.layout.simple_dropdown_item_1line, cityNames);
        spinnerDepartureCity.setAdapter(departureCityAdapter);
        spinnerArrivalCity.setAdapter(arrivalCityAdapter);

        etTotalAmount.setFocusable(false);
        etTotalAmount.setClickable(false);
        etTotalAmount.setKeyListener(null);

        spinnerDepartureCity.addTextChangedListener(new android.text.TextWatcher() {
            @Override
            public void beforeTextChanged(CharSequence s, int start, int count, int after) {}
            @Override
            public void onTextChanged(CharSequence s, int start, int before, int count) {}
            @Override
            public void afterTextChanged(android.text.Editable s) {

                String selectedDepartureCity = s.toString().trim();
                java.util.List<String> availableArrivalCities = new java.util.ArrayList<>();
                for (String cityName : cityNames) {
                    if (!cityName.equals(selectedDepartureCity)) {
                        availableArrivalCities.add(cityName);
                    }
                }
                ArrayAdapter<String> updatedArrivalAdapter = new ArrayAdapter<>(HomeActivity.this,
                    android.R.layout.simple_dropdown_item_1line, availableArrivalCities);
                spinnerArrivalCity.setAdapter(updatedArrivalAdapter);

                if (spinnerArrivalCity.getText().toString().equals(selectedDepartureCity)) {
                    spinnerArrivalCity.setText("");
                }
                calculateTotalAmount(spinnerDepartureCity, spinnerArrivalCity, etPassengerCount, etTotalAmount);
            }
        });
        spinnerArrivalCity.addTextChangedListener(new android.text.TextWatcher() {
            @Override
            public void beforeTextChanged(CharSequence s, int start, int count, int after) {}
            @Override
            public void onTextChanged(CharSequence s, int start, int before, int count) {}
            @Override
            public void afterTextChanged(android.text.Editable s) {
                calculateTotalAmount(spinnerDepartureCity, spinnerArrivalCity, etPassengerCount, etTotalAmount);
            }
        });
        etPassengerCount.addTextChangedListener(new android.text.TextWatcher() {
            @Override
            public void beforeTextChanged(CharSequence s, int start, int count, int after) {}
            @Override
            public void onTextChanged(CharSequence s, int start, int before, int count) {}
            @Override
            public void afterTextChanged(android.text.Editable s) {
                calculateTotalAmount(spinnerDepartureCity, spinnerArrivalCity, etPassengerCount, etTotalAmount);
            }
        });

        String[] paymentMethods = {"cash", "stripe"};
        ArrayAdapter<String> paymentAdapter = new ArrayAdapter<>(this, android.R.layout.simple_dropdown_item_1line, paymentMethods);
        spinnerPaymentMethod.setAdapter(paymentAdapter);

        etDepartureDate.setOnClickListener(v -> {
            java.util.Calendar calendar = java.util.Calendar.getInstance();
            DatePickerDialog datePickerDialog = new DatePickerDialog(this,
                    (view, year, month, dayOfMonth) -> {
                        String date = String.format("%04d-%02d-%02d", year, month + 1, dayOfMonth);
                        etDepartureDate.setText(date);
                    },
                    calendar.get(java.util.Calendar.YEAR),
                    calendar.get(java.util.Calendar.MONTH),
                    calendar.get(java.util.Calendar.DAY_OF_MONTH));
            datePickerDialog.show();
        });

        etDepartureTime.setOnClickListener(v -> {
            java.util.Calendar calendar = java.util.Calendar.getInstance();
            TimePickerDialog timePickerDialog = new TimePickerDialog(this,
                    (view, hourOfDay, minute) -> {
                        String time = String.format("%02d:%02d:00", hourOfDay, minute);
                        etDepartureTime.setText(time);
                    },
                    calendar.get(java.util.Calendar.HOUR_OF_DAY),
                    calendar.get(java.util.Calendar.MINUTE),
                    true);
            timePickerDialog.show();
        });
        new AlertDialog.Builder(this)
                .setTitle("Ajouter une réservation")
                .setView(d)
                .setPositiveButton("Créer", (dlg, w) -> {
                    createBookingFromDialog(spinnerClient, spinnerDepartureCity, spinnerArrivalCity,
                            etDepartureDate, etDepartureTime, etPassengerCount,
                            spinnerPaymentMethod, etTotalAmount);
                })
                .setNegativeButton("Annuler", null)
                .show();
    }
    private void createBookingFromDialog(AutoCompleteTextView spinnerClient, AutoCompleteTextView spinnerDepartureCity,
                                        AutoCompleteTextView spinnerArrivalCity, EditText etDepartureDate,
                                        EditText etDepartureTime, EditText etPassengerCount,
                                        AutoCompleteTextView spinnerPaymentMethod, EditText etTotalAmount) {
        String clientName = spinnerClient.getText().toString().trim();
        String departureCity = spinnerDepartureCity.getText().toString().trim();
        String arrivalCity = spinnerArrivalCity.getText().toString().trim();
        String departureDate = etDepartureDate.getText().toString().trim();
        String departureTime = etDepartureTime.getText().toString().trim();
        String passengerCountStr = etPassengerCount.getText().toString().trim();
        String paymentMethod = spinnerPaymentMethod.getText().toString().trim();
        String totalAmountStr = etTotalAmount.getText().toString().trim();

        if (clientName.isEmpty() || departureCity.isEmpty() || arrivalCity.isEmpty() ||
            departureDate.isEmpty() || departureTime.isEmpty() || passengerCountStr.isEmpty() ||
            paymentMethod.isEmpty() || totalAmountStr.isEmpty()) {
            Toast.makeText(this, "Tous les champs sont requis", Toast.LENGTH_SHORT).show();
            return;
        }

        Client selectedClient = clients.stream().filter(c -> c.full_name.equals(clientName)).findFirst().orElse(null);
        Ville departureVille = cities.stream().filter(c -> c.nom.equals(departureCity)).findFirst().orElse(null);
        Ville arrivalVille = cities.stream().filter(c -> c.nom.equals(arrivalCity)).findFirst().orElse(null);
        if (selectedClient == null || departureVille == null || arrivalVille == null) {
            Toast.makeText(this, "Sélections invalides", Toast.LENGTH_SHORT).show();
            return;
        }
        try {
            int passengerCount = Integer.parseInt(passengerCountStr);

            String numericAmount = totalAmountStr.replace("MAD", "").trim();
            double totalAmount = Double.parseDouble(numericAmount);

            Trajet matchingTrajet = null;
            for (Trajet trajet : trajets) {
                if (trajet.depart != null && trajet.arrivee != null &&
                    trajet.depart.equalsIgnoreCase(departureCity) &&
                    trajet.arrivee.equalsIgnoreCase(arrivalCity)) {
                    matchingTrajet = trajet;
                    break;
                }
            }
            if (matchingTrajet == null) {
                Toast.makeText(this, "Aucun trajet trouvé pour cette route", Toast.LENGTH_SHORT).show();
                return;
            }
            Map<String, Object> payload = new HashMap<>();
            payload.put("user_id", selectedClient.id);
            payload.put("trajet_id", matchingTrajet.id);
            payload.put("departure_date", departureDate);
            payload.put("departure_time", departureTime);
            payload.put("passenger_count", passengerCount);
            payload.put("payment_method", paymentMethod);
            Log.d("BookingCreation", "Sending payload: " + payload.toString());
            BookingsApi api = ApiClient.get().create(BookingsApi.class);
            api.create(payload).enqueue(new Callback<ApiResponse<Map<String, Object>>>() {
                @Override
                public void onResponse(Call<ApiResponse<Map<String, Object>>> call, Response<ApiResponse<Map<String, Object>>> response) {
                    Log.d("BookingCreation", "Response code: " + response.code());
                    if (response.body() != null) {
                        Log.d("BookingCreation", "Response body ok: " + response.body().ok);
                        Log.d("BookingCreation", "Response error: " + response.body().error);
                    }
                    if (response.isSuccessful() && response.body() != null && response.body().ok) {
                        Toast.makeText(HomeActivity.this, "Réservation créée avec succès", Toast.LENGTH_SHORT).show();
                    } else {
                        String errorMsg = "Erreur lors de la création";
                        if (response.body() != null && response.body().error != null) {
                            errorMsg += ": " + response.body().error;
                        }
                        Toast.makeText(HomeActivity.this, errorMsg, Toast.LENGTH_LONG).show();
                        Log.e("BookingCreation", "Error: " + errorMsg);
                    }
                }
                @Override
                public void onFailure(Call<ApiResponse<Map<String, Object>>> call, Throwable t) {
                    String errorMsg = "Erreur réseau: " + t.getMessage();
                    Toast.makeText(HomeActivity.this, errorMsg, Toast.LENGTH_LONG).show();
                    Log.e("BookingCreation", "Network error: " + t.getMessage());
                }
            });
        } catch (NumberFormatException e) {
            Toast.makeText(this, "Valeurs numériques invalides", Toast.LENGTH_SHORT).show();
        }
    }
    private void calculateTotalAmount(AutoCompleteTextView spinnerDepartureCity, AutoCompleteTextView spinnerArrivalCity,
                                     EditText etPassengerCount, EditText etTotalAmount) {
        String departureCity = spinnerDepartureCity.getText().toString().trim();
        String arrivalCity = spinnerArrivalCity.getText().toString().trim();
        String passengerCountStr = etPassengerCount.getText().toString().trim();
        Log.d("BookingDialog", "Calculating price - Departure: " + departureCity + ", Arrival: " + arrivalCity + ", Passengers: " + passengerCountStr);
        Log.d("BookingDialog", "Available trajets: " + trajets.size());
        if (departureCity.isEmpty() || arrivalCity.isEmpty()) {
            etTotalAmount.setText("");
            return;
        }

        int passengerCount = 1;
        if (!passengerCountStr.isEmpty()) {
            try {
                passengerCount = Integer.parseInt(passengerCountStr);
                if (passengerCount <= 0) passengerCount = 1;
            } catch (NumberFormatException e) {
                passengerCount = 1;
            }
        }

        Trajet matchingTrajet = null;
        for (Trajet trajet : trajets) {
            Log.d("BookingDialog", "Checking trajet: " + trajet.depart + " -> " + trajet.arrivee + " (Price: " + trajet.prix + ")");
            if (trajet.depart != null && trajet.arrivee != null &&
                trajet.depart.equalsIgnoreCase(departureCity) &&
                trajet.arrivee.equalsIgnoreCase(arrivalCity)) {
                matchingTrajet = trajet;
                Log.d("BookingDialog", "Found matching trajet with price: " + trajet.prix);
                break;
            }
        }
        if (matchingTrajet != null) {

            double totalAmount = matchingTrajet.prix * passengerCount;
            etTotalAmount.setText(String.format("%.2f MAD", totalAmount));
            Log.d("BookingDialog", "Calculated total: " + totalAmount);
        } else {

            etTotalAmount.setText("");
            Log.d("BookingDialog", "No matching trajet found");
        }
    }
}