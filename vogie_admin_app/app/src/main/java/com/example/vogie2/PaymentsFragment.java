package com.example.vogie2;
import android.app.AlertDialog;
import android.app.DatePickerDialog;
import android.app.TimePickerDialog;
import android.os.Bundle;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.ArrayAdapter;
import android.widget.AutoCompleteTextView;
import android.widget.EditText;
import android.widget.ProgressBar;
import android.widget.TextView;
import androidx.annotation.NonNull;
import androidx.annotation.Nullable;
import androidx.fragment.app.Fragment;
import androidx.recyclerview.widget.LinearLayoutManager;
import androidx.recyclerview.widget.RecyclerView;
import com.example.vogie2.api.BookingsApi;
import com.example.vogie2.api.ClientsApi;
import com.example.vogie2.api.TrajetsApi;
import com.example.vogie2.api.VillesApi;
import com.example.vogie2.models.ApiResponse;
import com.example.vogie2.models.Booking;
import com.example.vogie2.models.Client;
import com.example.vogie2.models.Trajet;
import com.example.vogie2.models.Ville;
import com.google.android.material.floatingactionbutton.FloatingActionButton;
import com.google.android.material.snackbar.Snackbar;
import java.util.ArrayList;
import java.util.HashMap;
import java.util.List;
import java.util.Map;
import retrofit2.Call;
import retrofit2.Callback;
import retrofit2.Response;
public class PaymentsFragment extends Fragment implements PaymentsAdapter.Listener {
    private RecyclerView recyclerView;
    private PaymentsAdapter adapter;
    private ProgressBar progressBar;
    private TextView errorText;
    private FloatingActionButton fabAdd;
    private List<Client> clients = new ArrayList<>();
    private List<Ville> cities = new ArrayList<>();
    private List<Trajet> trajets = new ArrayList<>();
    @Nullable
    @Override
    public View onCreateView(@NonNull LayoutInflater inflater, @Nullable ViewGroup container, @Nullable Bundle savedInstanceState) {
        View v = inflater.inflate(R.layout.fragment_payments, container, false);
        recyclerView = v.findViewById(R.id.recyclerView);
        progressBar = v.findViewById(R.id.progressBar);
        errorText = v.findViewById(R.id.errorText);
        fabAdd = v.findViewById(R.id.fabAdd);
        adapter = new PaymentsAdapter(this);
        recyclerView.setLayoutManager(new LinearLayoutManager(requireContext()));
        recyclerView.setAdapter(adapter);
        fabAdd.setOnClickListener(v1 -> showCreateBookingDialog());
        load();
        loadClients();
        loadCities();
        loadTrajets();
        return v;
    }
    private void setLoading(boolean b) { progressBar.setVisibility(b?View.VISIBLE:View.GONE); }
    private void load() {
        setLoading(true);
        errorText.setVisibility(View.GONE);
        BookingsApi api = ApiClient.get().create(BookingsApi.class);
        api.list(null).enqueue(new Callback<ApiResponse<List<Booking>>>() {
            @Override public void onResponse(Call<ApiResponse<List<Booking>>> call, Response<ApiResponse<List<Booking>>> response) {
                setLoading(false);
                if (response.isSuccessful() && response.body()!=null && response.body().ok) {
                    adapter.submit(response.body().data!=null?response.body().data:new ArrayList<>());
                } else {
                    errorText.setVisibility(View.VISIBLE);
                    errorText.setText("Erreur "+response.code());
                }
            }
            @Override public void onFailure(Call<ApiResponse<List<Booking>>> call, Throwable t) {
                setLoading(false);
                errorText.setVisibility(View.VISIBLE);
                errorText.setText("Erreur: "+t.getMessage());
            }
        });
    }
    @Override
    public void onConfirmPayment(Booking b) {
        new AlertDialog.Builder(requireContext())
                .setTitle("Confirmer le paiement")
                .setMessage("Marquer la réservation #"+b.id+" comme payée ?")
                .setPositiveButton("Confirmer", (d,w)->{
                    BookingsApi api = ApiClient.get().create(BookingsApi.class);
                    java.util.Map<String, Object> payload = new java.util.HashMap<>();
                    payload.put("payment_status", "completed");
                    api.update(b.id, payload).enqueue(new Callback<ApiResponse<Map<String, Object>>>() {
                        @Override public void onResponse(Call<ApiResponse<Map<String, Object>>> call, Response<ApiResponse<Map<String, Object>>> response) {
                            if (response.isSuccessful() && response.body()!=null && response.body().ok) {
                                Snackbar.make(recyclerView, "Paiement confirmé", Snackbar.LENGTH_SHORT).show();
                                load();
                            } else {
                                Snackbar.make(recyclerView, "Erreur", Snackbar.LENGTH_SHORT).show();
                            }
                        }
                        @Override public void onFailure(Call<ApiResponse<Map<String, Object>>> call, Throwable t) {
                            Snackbar.make(recyclerView, "Erreur: "+t.getMessage(), Snackbar.LENGTH_SHORT).show();
                        }
                    });
                })
                .setNegativeButton("Annuler", null)
                .show();
    }
    private void loadClients() {
        ClientsApi api = ApiClient.get().create(ClientsApi.class);
        api.list(null).enqueue(new Callback<ApiResponse<List<Client>>>() {
            @Override
            public void onResponse(Call<ApiResponse<List<Client>>> call, Response<ApiResponse<List<Client>>> response) {
                if (response.isSuccessful() && response.body() != null && response.body().ok) {
                    clients = response.body().data != null ? response.body().data : new ArrayList<>();
                }
            }
            @Override
            public void onFailure(Call<ApiResponse<List<Client>>> call, Throwable t) {

            }
        });
    }
    private void loadCities() {
        VillesApi api = ApiClient.get().create(VillesApi.class);
        api.list().enqueue(new Callback<ApiResponse<List<Ville>>>() {
            @Override
            public void onResponse(Call<ApiResponse<List<Ville>>> call, Response<ApiResponse<List<Ville>>> response) {
                if (response.isSuccessful() && response.body() != null && response.body().ok) {
                    cities = response.body().data != null ? response.body().data : new ArrayList<>();
                }
            }
            @Override
            public void onFailure(Call<ApiResponse<List<Ville>>> call, Throwable t) {

            }
        });
    }
    private void loadTrajets() {
        TrajetsApi api = ApiClient.get().create(TrajetsApi.class);
        api.list(null).enqueue(new Callback<ApiResponse<List<Trajet>>>() {
            @Override
            public void onResponse(Call<ApiResponse<List<Trajet>>> call, Response<ApiResponse<List<Trajet>>> response) {
                if (response.isSuccessful() && response.body() != null && response.body().ok) {
                    trajets = response.body().data != null ? response.body().data : new ArrayList<>();
                }
            }
            @Override
            public void onFailure(Call<ApiResponse<List<Trajet>>> call, Throwable t) {

            }
        });
    }
    public void showCreateBookingDialog() {
        View d = LayoutInflater.from(requireContext()).inflate(R.layout.dialog_booking, null, false);
        AutoCompleteTextView spinnerClient = d.findViewById(R.id.spinnerClient);
        AutoCompleteTextView spinnerDepartureCity = d.findViewById(R.id.spinnerDepartureCity);
        AutoCompleteTextView spinnerArrivalCity = d.findViewById(R.id.spinnerArrivalCity);
        EditText etDepartureDate = d.findViewById(R.id.etDepartureDate);
        EditText etDepartureTime = d.findViewById(R.id.etDepartureTime);
        EditText etPassengerCount = d.findViewById(R.id.etPassengerCount);
        AutoCompleteTextView spinnerPaymentMethod = d.findViewById(R.id.spinnerPaymentMethod);
        EditText etTotalAmount = d.findViewById(R.id.etTotalAmount);

        String[] clientNames = clients.stream().map(c -> c.full_name).toArray(String[]::new);
        ArrayAdapter<String> clientAdapter = new ArrayAdapter<>(requireContext(), android.R.layout.simple_dropdown_item_1line, clientNames);
        spinnerClient.setAdapter(clientAdapter);

        String[] cityNames = cities.stream().map(c -> c.nom).toArray(String[]::new);
        ArrayAdapter<String> departureCityAdapter = new ArrayAdapter<>(requireContext(), android.R.layout.simple_dropdown_item_1line, cityNames);
        ArrayAdapter<String> arrivalCityAdapter = new ArrayAdapter<>(requireContext(), android.R.layout.simple_dropdown_item_1line, cityNames);
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
                ArrayAdapter<String> updatedArrivalAdapter = new ArrayAdapter<>(requireContext(),
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
        ArrayAdapter<String> paymentAdapter = new ArrayAdapter<>(requireContext(), android.R.layout.simple_dropdown_item_1line, paymentMethods);
        spinnerPaymentMethod.setAdapter(paymentAdapter);

        etDepartureDate.setOnClickListener(v -> {
            java.util.Calendar calendar = java.util.Calendar.getInstance();
            DatePickerDialog datePickerDialog = new DatePickerDialog(requireContext(),
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
            TimePickerDialog timePickerDialog = new TimePickerDialog(requireContext(),
                    (view, hourOfDay, minute) -> {
                        String time = String.format("%02d:%02d:00", hourOfDay, minute);
                        etDepartureTime.setText(time);
                    },
                    calendar.get(java.util.Calendar.HOUR_OF_DAY),
                    calendar.get(java.util.Calendar.MINUTE),
                    true);
            timePickerDialog.show();
        });
        new AlertDialog.Builder(requireContext())
                .setTitle("Ajouter une réservation")
                .setView(d)
                .setPositiveButton("Créer", (dlg, w) -> {
                    createBooking(spinnerClient, spinnerDepartureCity, spinnerArrivalCity,
                            etDepartureDate, etDepartureTime, etPassengerCount,
                            spinnerPaymentMethod, etTotalAmount);
                })
                .setNegativeButton("Annuler", null)
                .show();
    }
    private void createBooking(AutoCompleteTextView spinnerClient, AutoCompleteTextView spinnerDepartureCity,
                              AutoCompleteTextView spinnerArrivalCity, EditText etDepartureDate,
                              EditText etDepartureTime, EditText etPassengerCount,
                              AutoCompleteTextView spinnerPaymentMethod, EditText etTotalAmount) {
        android.util.Log.d("PaymentsFragment", "Create button clicked");
        String clientName = spinnerClient.getText().toString().trim();
        String departureCity = spinnerDepartureCity.getText().toString().trim();
        String arrivalCity = spinnerArrivalCity.getText().toString().trim();
        String departureDate = etDepartureDate.getText().toString().trim();
        String departureTime = etDepartureTime.getText().toString().trim();
        String passengerCountStr = etPassengerCount.getText().toString().trim();
        String paymentMethod = spinnerPaymentMethod.getText().toString().trim();
        String totalAmountStr = etTotalAmount.getText().toString().trim();
        android.util.Log.d("PaymentsFragment", "Form data - Client: " + clientName + ", Departure: " + departureCity + ", Arrival: " + arrivalCity + ", Date: " + departureDate + ", Time: " + departureTime + ", Passengers: " + passengerCountStr + ", Payment: " + paymentMethod + ", Amount: " + totalAmountStr);

        if (clientName.isEmpty() || departureCity.isEmpty() || arrivalCity.isEmpty() ||
            departureDate.isEmpty() || departureTime.isEmpty() || passengerCountStr.isEmpty() ||
            paymentMethod.isEmpty() || totalAmountStr.isEmpty()) {
            return;
        }

        Client selectedClient = clients.stream().filter(c -> c.full_name.equals(clientName)).findFirst().orElse(null);
        Ville departureVille = cities.stream().filter(c -> c.nom.equals(departureCity)).findFirst().orElse(null);
        Ville arrivalVille = cities.stream().filter(c -> c.nom.equals(arrivalCity)).findFirst().orElse(null);
        if (selectedClient == null || departureVille == null || arrivalVille == null) {
            Snackbar.make(recyclerView, "Sélections invalides", Snackbar.LENGTH_SHORT).show();
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
                Snackbar.make(recyclerView, "Aucun trajet trouvé pour cette route", Snackbar.LENGTH_SHORT).show();
                return;
            }
            Map<String, Object> payload = new HashMap<>();
            payload.put("user_id", selectedClient.id);
            payload.put("trajet_id", matchingTrajet.id);
            payload.put("departure_date", departureDate);
            payload.put("departure_time", departureTime);
            payload.put("passenger_count", passengerCount);
            payload.put("payment_method", paymentMethod);
            android.util.Log.d("PaymentsFragment", "Sending payload: " + payload.toString());
            BookingsApi api = ApiClient.get().create(BookingsApi.class);
            api.create(payload).enqueue(new Callback<ApiResponse<Map<String, Object>>>() {
                @Override
                public void onResponse(Call<ApiResponse<Map<String, Object>>> call, Response<ApiResponse<Map<String, Object>>> response) {
                    android.util.Log.d("PaymentsFragment", "Response code: " + response.code());
                    if (response.body() != null) {
                        android.util.Log.d("PaymentsFragment", "Response body ok: " + response.body().ok);
                        android.util.Log.d("PaymentsFragment", "Response error: " + response.body().error);
                    }
                    if (response.isSuccessful() && response.body() != null && response.body().ok) {
                        Snackbar.make(recyclerView, "Réservation créée avec succès", Snackbar.LENGTH_SHORT).show();
                        load();
                    } else {
                        String errorMsg = "Erreur lors de la création";
                        if (response.body() != null && response.body().error != null) {
                            errorMsg += ": " + response.body().error;
                        }
                        Snackbar.make(recyclerView, errorMsg, Snackbar.LENGTH_LONG).show();
                        android.util.Log.e("PaymentsFragment", "Error: " + errorMsg);
                    }
                }
                @Override
                public void onFailure(Call<ApiResponse<Map<String, Object>>> call, Throwable t) {
                    String errorMsg = "Erreur réseau: " + t.getMessage();
                    Snackbar.make(recyclerView, errorMsg, Snackbar.LENGTH_LONG).show();
                    android.util.Log.e("PaymentsFragment", "Network error: " + t.getMessage());
                }
            });
        } catch (NumberFormatException e) {
            Snackbar.make(recyclerView, "Valeurs numériques invalides", Snackbar.LENGTH_SHORT).show();
        }
    }
    private void calculateTotalAmount(AutoCompleteTextView spinnerDepartureCity, AutoCompleteTextView spinnerArrivalCity,
                                     EditText etPassengerCount, EditText etTotalAmount) {
        String departureCity = spinnerDepartureCity.getText().toString().trim();
        String arrivalCity = spinnerArrivalCity.getText().toString().trim();
        String passengerCountStr = etPassengerCount.getText().toString().trim();
        android.util.Log.d("PaymentsFragment", "Calculating price - Departure: " + departureCity + ", Arrival: " + arrivalCity + ", Passengers: " + passengerCountStr);
        android.util.Log.d("PaymentsFragment", "Available trajets: " + trajets.size());
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
            android.util.Log.d("PaymentsFragment", "Checking trajet: " + trajet.depart + " -> " + trajet.arrivee + " (Price: " + trajet.prix + ")");
            if (trajet.depart != null && trajet.arrivee != null &&
                trajet.depart.equalsIgnoreCase(departureCity) &&
                trajet.arrivee.equalsIgnoreCase(arrivalCity)) {
                matchingTrajet = trajet;
                android.util.Log.d("PaymentsFragment", "Found matching trajet with price: " + trajet.prix);
                break;
            }
        }
        if (matchingTrajet != null) {

            double totalAmount = matchingTrajet.prix * passengerCount;
            etTotalAmount.setText(String.format("%.2f MAD", totalAmount));
            android.util.Log.d("PaymentsFragment", "Calculated total: " + totalAmount);
        } else {

            etTotalAmount.setText("");
            android.util.Log.d("PaymentsFragment", "No matching trajet found");
        }
    }
}