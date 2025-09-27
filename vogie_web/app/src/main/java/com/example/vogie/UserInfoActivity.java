package com.example.vogie;
import android.content.Intent;
import android.os.Bundle;
import android.widget.Button;
import android.widget.Toast;
import androidx.appcompat.app.AppCompatActivity;
import com.google.android.material.textfield.TextInputEditText;
public class UserInfoActivity extends AppCompatActivity {
    private TextInputEditText etFullName, etEmail, etPhone;
    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_user_info);

        etFullName = findViewById(R.id.etFullName);
        etEmail = findViewById(R.id.etEmail);
        etPhone = findViewById(R.id.etPhone);
        Button btnContinue = findViewById(R.id.btnContinueToPayment);

        String cityId = getIntent().getStringExtra("city_id");
        String cityName = getIntent().getStringExtra("city_name");
        btnContinue.setOnClickListener(v -> {
            if (validateInputs()) {

                Intent intent = new Intent(UserInfoActivity.this, PaymentMethodActivity.class);
                intent.putExtra("city_id", cityId);
                intent.putExtra("city_name", cityName);
                intent.putExtra("full_name", etFullName.getText().toString().trim());
                intent.putExtra("email", etEmail.getText().toString().trim());
                intent.putExtra("phone", etPhone.getText().toString().trim());
                startActivity(intent);
            }
        });
    }
    private boolean validateInputs() {
        String fullName = etFullName.getText().toString().trim();
        String email = etEmail.getText().toString().trim();
        String phone = etPhone.getText().toString().trim();
        if (fullName.isEmpty()) {
            etFullName.setError("Full name is required");
            etFullName.requestFocus();
            return false;
        }
        if (email.isEmpty() || !android.util.Patterns.EMAIL_ADDRESS.matcher(email).matches()) {
            etEmail.setError("Please enter a valid email address");
            etEmail.requestFocus();
            return false;
        }
        if (phone.isEmpty() || phone.length() < 10) {
            etPhone.setError("Please enter a valid phone number");
            etPhone.requestFocus();
            return false;
        }
        return true;
    }
}