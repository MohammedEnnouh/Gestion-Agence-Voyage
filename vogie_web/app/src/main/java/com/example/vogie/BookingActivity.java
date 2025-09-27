package com.example.vogie;
import android.content.Intent;
import android.os.Bundle;
import android.view.View;
import android.widget.Toast;
import androidx.appcompat.app.AppCompatActivity;
import com.example.vogie.api.ApiClient;
import com.example.vogie.api.ApiResponse;
import com.example.vogie.api.ApiService;
import com.example.vogie.databinding.ActivityBookingBinding;
import com.example.vogie.model.Booking;
import java.util.Locale;
import retrofit2.Call;
import retrofit2.Callback;
import retrofit2.Response;
public class BookingActivity extends AppCompatActivity {
    private ActivityBookingBinding binding;
    private ApiService apiService;
    private int fromId, toId, passengers;
    private String date, time, fromName, toName;
    private double price;
    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        binding = ActivityBookingBinding.inflate(getLayoutInflater());
        setContentView(binding.getRoot());
        apiService = ApiClient.getApiService();

        fromId = getIntent().getIntExtra("from_id", 0);
        toId = getIntent().getIntExtra("to_id", 0);
        date = getIntent().getStringExtra("date");
        time = getIntent().getStringExtra("time");
        passengers = getIntent().getIntExtra("passengers", 1);
        price = getIntent().getDoubleExtra("price", 0.0);
        fromName = getIntent().getStringExtra("from_name");
        toName = getIntent().getStringExtra("to_name");
        setupUI();
    }
    private void setupUI() {

        setSupportActionBar(binding.toolbar);
        if (getSupportActionBar() != null) {
            getSupportActionBar().setDisplayHomeAsUpEnabled(true);
            getSupportActionBar().setTitle("Réservation");
        }

        binding.routeDetails.setText(String.format("%s  %s",
            fromName != null ? fromName : "Ville",
            toName != null ? toName : "Ville"));
        binding.dateDetails.setText(String.format("Date: %s | Heure: %s",
            date != null ? date : "",
            time != null ? time : ""));
        binding.passengerDetails.setText(String.format("Passagers: %d", passengers));
        double totalPrice = price * passengers;
        binding.priceDetails.setText(String.format(Locale.getDefault(),
            "Prix par personne: %.2f MAD\nTotal: %.2f MAD", price, totalPrice));

        binding.confirmBookingButton.setOnClickListener(v -> {
            if (validateForm()) {
                createBooking();
            }
        });
    }
    private boolean validateForm() {
        String fullName = binding.fullNameInput.getText().toString().trim();
        String email = binding.emailInput.getText().toString().trim();
        String phone = binding.phoneInput.getText().toString().trim();
        if (fullName.isEmpty()) {
            binding.fullNameInput.setError("Nom complet requis");
            return false;
        }
        if (email.isEmpty()) {
            binding.emailInput.setError("Email requis");
            return false;
        }
        if (!android.util.Patterns.EMAIL_ADDRESS.matcher(email).matches()) {
            binding.emailInput.setError("Email invalide");
            return false;
        }
        if (phone.isEmpty()) {
            binding.phoneInput.setError("Téléphone requis");
            return false;
        }
        return true;
    }
    private void createBooking() {
        String fullName = binding.fullNameInput.getText().toString().trim();
        String email = binding.emailInput.getText().toString().trim();
        String phone = binding.phoneInput.getText().toString().trim();
        String paymentMethod = "cash";
        binding.progressBar.setVisibility(View.VISIBLE);
        binding.confirmBookingButton.setEnabled(false);
        apiService.createBooking(fullName, email, phone, fromId, toId, date, time, passengers, paymentMethod)
                .enqueue(new Callback<ApiResponse<Booking>>() {
                    @Override
                    public void onResponse(Call<ApiResponse<Booking>> call, Response<ApiResponse<Booking>> response) {
                        binding.progressBar.setVisibility(View.GONE);
                        binding.confirmBookingButton.setEnabled(true);
                        if (response.isSuccessful() && response.body() != null && response.body().success) {
                            Booking booking = response.body().data;

                            Intent intent = new Intent(BookingActivity.this, PaymentMethodActivity.class);
                            intent.putExtra("booking_id", booking.getId());
                            intent.putExtra("total_amount", price * passengers);
                            intent.putExtra("from_name", fromName);
                            intent.putExtra("to_name", toName);
                            intent.putExtra("full_name", binding.fullNameInput.getText().toString().trim());
                            intent.putExtra("email", binding.emailInput.getText().toString().trim());
                            intent.putExtra("phone", binding.phoneInput.getText().toString().trim());
                            startActivity(intent);
                            finish();
                            Toast.makeText(BookingActivity.this, "Réservation créée avec succès!", Toast.LENGTH_SHORT).show();
                        } else {
                            String errorMessage = response.body() != null && response.body().message != null
                                ? response.body().message
                                : "Erreur lors de la création de la réservation";
                            Toast.makeText(BookingActivity.this, errorMessage, Toast.LENGTH_SHORT).show();
                        }
                    }
                    @Override
                    public void onFailure(Call<ApiResponse<Booking>> call, Throwable t) {
                        binding.progressBar.setVisibility(View.GONE);
                        binding.confirmBookingButton.setEnabled(true);
                        Toast.makeText(BookingActivity.this, "Erreur de connexion: " + t.getMessage(), Toast.LENGTH_SHORT).show();
                    }
                });
    }
    @Override
    public boolean onSupportNavigateUp() {
        onBackPressed();
        return true;
    }
}