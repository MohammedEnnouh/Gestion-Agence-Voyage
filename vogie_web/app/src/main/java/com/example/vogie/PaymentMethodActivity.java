package com.example.vogie;
import android.app.ProgressDialog;
import android.content.Intent;
import android.os.Bundle;
import android.view.View;
import android.widget.TextView;
import android.widget.Toast;
import androidx.appcompat.app.AlertDialog;
import androidx.appcompat.app.AppCompatActivity;
import com.example.vogie.payment.StripePaymentHelper;
import com.google.android.material.card.MaterialCardView;
import com.stripe.android.model.PaymentMethodCreateParams;
import kotlin.Unit;
import java.util.Locale;
public class PaymentMethodActivity extends AppCompatActivity {
    private static final String TAG = "PaymentMethodActivity";
    private String cityId;
    private String cityName;
    private String fullName;
    private String email;
    private String phone;
    private ProgressDialog progressDialog;
    private StripePaymentHelper stripePaymentHelper;
    private String routeLabel;
    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_payment_method);

        progressDialog = new ProgressDialog(this);
        progressDialog.setMessage("Processing payment...");
        progressDialog.setCancelable(false);

        stripePaymentHelper = new StripePaymentHelper(
                this,
                StripePaymentHelper.TEST_PUBLISHABLE_KEY,
                StripePaymentHelper.BACKEND_URL,
                () -> {
                    onPaymentSuccess();
                    return kotlin.Unit.INSTANCE;
                },
                error -> {
                    onPaymentError(error);
                    return kotlin.Unit.INSTANCE;
                }
        );

        Intent intent = getIntent();

        cityId = intent.getStringExtra("city_id");
        cityName = intent.getStringExtra("city_name");

        String fromName = intent.getStringExtra("from_name");
        String toName = intent.getStringExtra("to_name");
        double totalAmount = intent.getDoubleExtra("total_amount", 0.0);
        fullName = intent.getStringExtra("full_name");
        email = intent.getStringExtra("email");
        phone = intent.getStringExtra("phone");

        TextView tvDestination = findViewById(R.id.tvDestination);
        routeLabel = (fromName != null && toName != null) ? (fromName + " → " + toName) : (cityName != null ? cityName : "");
        tvDestination.setText(routeLabel);
        TextView tvAmount = findViewById(R.id.tvAmount);
        if (tvAmount != null) {
            tvAmount.setText(String.format(Locale.getDefault(), "%.2f MAD", totalAmount));
        }

        MaterialCardView cardOnlinePayment = findViewById(R.id.cardOnlinePayment);
        MaterialCardView cardCashOnDelivery = findViewById(R.id.cardCashOnDelivery);
        cardOnlinePayment.setOnClickListener(v -> {

            showErrorDialog("Paiement en ligne", "Le paiement par carte sera activé prochainement. Veuillez choisir le paiement en espèces pour finaliser votre réservation.");
        });
        cardCashOnDelivery.setOnClickListener(v -> {

            showQRCode();
        });
    }
    private void showPaymentConfirmationDialog() {
        new AlertDialog.Builder(this)
                .setTitle("Confirmation du paiement")
                .setMessage(String.format(Locale.getDefault(), "Vous serez débité de %s pour votre trajet. Continuer?", ((TextView)findViewById(R.id.tvAmount))!=null?((TextView)findViewById(R.id.tvAmount)).getText():""))
                .setPositiveButton("Payer maintenant", (dialog, which) -> processOnlinePayment())
                .setNegativeButton("Annuler", null)
                .show();
    }
    private void processOnlinePayment() {
        progressDialog.show();

        long amount = 0;
        String currency = "MAD";
        String description = String.format(Locale.getDefault(), "Trajet %s - %s", routeLabel, fullName);

        stripePaymentHelper.processPayment(amount, currency, description);
    }
    private void onPaymentSuccess() {

        runOnUiThread(() -> {
            progressDialog.dismiss();
            showPaymentSuccess();
        });
    }
    private void onPaymentError(String errorMessage) {
        runOnUiThread(() -> {
            progressDialog.dismiss();
            showErrorDialog("Payment Error", errorMessage);
        });
    }
    private void showErrorDialog(String title, String message) {
        new AlertDialog.Builder(this)
                .setTitle(title)
                .setMessage(message)
                .setPositiveButton("OK", null)
                .show();
    }
    private void showQRCode() {

        String bookingId = generateBookingId();
        String qrData = String.format(Locale.US,
                "VOGIE_BOOKING\n" +
                "Booking ID: %s\n" +
                "Name: %s\n" +
                "Email: %s\n" +
                "Phone: %s\n" +
                "Trajet: %s\n" +
                "Amount: %s\n" +
                "Payment: Cash on Delivery",
                bookingId, fullName, email, phone, routeLabel, ((TextView)findViewById(R.id.tvAmount))!=null?((TextView)findViewById(R.id.tvAmount)).getText():"0 MAD");

        Intent intent = new Intent(this, QRCodeActivity.class);
        intent.putExtra("qr_data", qrData);
        intent.putExtra("booking_id", bookingId);
        startActivity(intent);
        finish();
    }
    private void showPaymentSuccess() {

        Intent intent = new Intent(this, BookingConfirmationActivity.class);
        intent.putExtra("booking_id", generateBookingId());
        intent.putExtra("city_name", routeLabel);
        intent.putExtra("payment_method", "Espèces");
        TextView tvAmount2 = findViewById(R.id.tvAmount);
        intent.putExtra("amount", tvAmount2 != null ? tvAmount2.getText().toString() : "0 MAD");
        startActivity(intent);
        finish();
    }
    private String generateBookingId() {

        return "VOG" + System.currentTimeMillis();
    }
}