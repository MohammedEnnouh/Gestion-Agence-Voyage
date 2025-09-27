package com.example.vogie;
import android.content.Intent;
import android.os.Bundle;
import android.widget.Button;
import android.widget.TextView;
import androidx.appcompat.app.AppCompatActivity;
import android.widget.Toast;
public class BookingConfirmationActivity extends AppCompatActivity {
    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_booking_confirmation);

        Intent intent = getIntent();
        String bookingId = intent.getStringExtra("booking_id");
        String cityName = intent.getStringExtra("city_name");
        String paymentMethod = intent.getStringExtra("payment_method");

        TextView tvBookingId = findViewById(R.id.tvBookingId);
        TextView tvDestination = findViewById(R.id.tvDestination);
        TextView tvPaymentMethod = findViewById(R.id.tvPaymentMethod);
        TextView tvConfirmationMessage = findViewById(R.id.tvConfirmationMessage);
        tvBookingId.setText(bookingId);
        tvDestination.setText(cityName);
        tvPaymentMethod.setText(paymentMethod);

        if ("Credit/Debit Card".equals(paymentMethod)) {
            tvConfirmationMessage.setText("Your payment was successful and your booking is confirmed!");
        } else {
            tvConfirmationMessage.setText("Your booking is confirmed! Please show the QR code to the driver when boarding.");
        }

        Button btnViewBooking = findViewById(R.id.btnViewBooking);
        Button btnDone = findViewById(R.id.btnDone);
        btnViewBooking.setOnClickListener(v -> {

            Toast.makeText(this, "View booking details will be implemented", Toast.LENGTH_SHORT).show();
        });
        btnDone.setOnClickListener(v -> {

            Intent mainIntent = new Intent(BookingConfirmationActivity.this, MainActivity.class);
            mainIntent.setFlags(Intent.FLAG_ACTIVITY_CLEAR_TOP | Intent.FLAG_ACTIVITY_NEW_TASK);
            startActivity(mainIntent);
            finish();
        });
    }
}