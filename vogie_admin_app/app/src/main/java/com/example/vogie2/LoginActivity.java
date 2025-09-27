package com.example.vogie2;
import android.content.Intent;
import android.os.Bundle;
import android.text.TextUtils;
import android.view.View;
import android.widget.Button;
import android.widget.EditText;
import android.widget.ProgressBar;
import android.widget.TextView;
import androidx.annotation.Nullable;
import androidx.appcompat.app.AppCompatActivity;
import com.example.vogie2.api.AuthApi;
import com.example.vogie2.models.ApiResponse;
import com.example.vogie2.models.User;
import com.example.vogie2.db.AppDatabase;
import com.example.vogie2.db.UserDao;
import com.example.vogie2.db.UserEntity;
import java.util.concurrent.ExecutorService;
import java.util.concurrent.Executors;
public class LoginActivity extends AppCompatActivity {
    private EditText emailInput;
    private EditText passwordInput;
    private Button loginButton;
    private ProgressBar progressBar;
    private TextView errorText;
    private final ExecutorService executor = Executors.newSingleThreadExecutor();
    @Override
    protected void onCreate(@Nullable Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);

        getWindow().setBackgroundDrawableResource(android.R.color.white);
        setContentView(R.layout.activity_login);

        NetworkConfig.setRealDeviceIP("192.168.0.101");

        try {
            java.io.File crash = new java.io.File(getFilesDir(), "crash.txt");
            if (crash.exists()) {
                StringBuilder sb = new StringBuilder();
                try (java.io.BufferedReader br = new java.io.BufferedReader(new java.io.InputStreamReader(new java.io.FileInputStream(crash)))) {
                    String line;
                    int count = 0;
                    while ((line = br.readLine()) != null && count < 400) {
                        sb.append(line).append('\n');
                        count++;
                    }
                }
                new androidx.appcompat.app.AlertDialog.Builder(this)
                        .setTitle("Crash log")
                        .setMessage(sb.toString())
                        .setPositiveButton("Copy", (d,w)->{
                            android.content.ClipboardManager cm = (android.content.ClipboardManager) getSystemService(android.content.Context.CLIPBOARD_SERVICE);
                            if (cm != null) cm.setPrimaryClip(android.content.ClipData.newPlainText("crash", sb.toString()));
                            android.widget.Toast.makeText(this, "Copied to clipboard", android.widget.Toast.LENGTH_SHORT).show();
                        })
                        .setNegativeButton("Dismiss", null)
                        .show();

                boolean ignoredDel = crash.delete();
            }
        } catch (Throwable ignored) {}

        try {
            String base = ApiClient.getBaseUrl();
            if (isEmulator() && base != null && base.contains("192.168.")) {
                ApiClient.setBaseUrl("http://10.0.2.2/Vogie3/public/api/");
            }
        } catch (Exception ignored) {}
        emailInput = findViewById(R.id.inputEmail);
        passwordInput = findViewById(R.id.inputPassword);
        loginButton = findViewById(R.id.btnLogin);
        progressBar = findViewById(R.id.progressBar);
        errorText = findViewById(R.id.errorText);

        android.widget.ImageView logo = findViewById(R.id.logoImage);
        if (logo != null) {
            int res = getResources().getIdentifier("vogie_logo", "drawable", getPackageName());
            if (res == 0) res = getResources().getIdentifier("v", "drawable", getPackageName());
            if (res == 0) res = getApplicationInfo().icon;
            logo.setImageResource(res);
            logo.setVisibility(View.VISIBLE);
        }

        emailInput.setText("admin@vogie.com");
        loginButton.setOnClickListener(v -> doLogin());
    }
    private void setLoading(boolean loading) {
        progressBar.setVisibility(loading ? View.VISIBLE : View.GONE);
        loginButton.setEnabled(!loading);
    }
    private void doLogin() {
        try {
            errorText.setVisibility(View.GONE);
            String email = emailInput.getText().toString().trim();
            String pass = passwordInput.getText().toString();
            if (TextUtils.isEmpty(email)) {
                errorText.setText("Email requis");
                errorText.setVisibility(View.VISIBLE);
                return;
            }
            setLoading(true);

            AuthApi api = ApiClient.get().create(AuthApi.class);
            java.util.Map<String, String> body = new java.util.HashMap<>();
            body.put("email", email);
            body.put("password", pass);
            api.login(body).enqueue(new retrofit2.Callback<ApiResponse<User>>() {
                @Override
                public void onResponse(retrofit2.Call<ApiResponse<User>> call, retrofit2.Response<ApiResponse<User>> response) {
                    if (response.isSuccessful() && response.body()!=null && response.body().ok) {
                        setLoading(false);
                        Intent i = new Intent(LoginActivity.this, HomeActivity.class);
                        i.addFlags(Intent.FLAG_ACTIVITY_CLEAR_TOP | Intent.FLAG_ACTIVITY_NEW_TASK);
                        startActivity(i);
                        finish();
                    } else {

                        tryLocalAuth(email, pass);
                    }
                }
                @Override
                public void onFailure(retrofit2.Call<ApiResponse<User>> call, Throwable t) {

                    android.widget.Toast.makeText(LoginActivity.this, "Serveur injoignable, tentative connexion locale...", android.widget.Toast.LENGTH_SHORT).show();
                    tryLocalAuth(email, pass);
                }
            });
        } catch (Throwable e) {
            setLoading(false);
            errorText.setText("Erreur: " + e.getClass().getSimpleName());
            errorText.setVisibility(View.VISIBLE);
        }
    }
    private boolean isEmulator() {
        String fp = android.os.Build.FINGERPRINT;
        String model = android.os.Build.MODEL;
        return (fp != null && (fp.contains("generic") || fp.contains("emulator")))
                || (model != null && model.contains("Emulator"));
    }
    private void tryLocalAuth(String email, String pass) {
        executor.execute(() -> {
            UserDao dao = AppDatabase.get(getApplicationContext()).userDao();
            UserEntity user = dao.findByEmail(email);
            boolean ok = false;
            if (user != null) {
                ok = (user.password == null || user.password.isEmpty() || (pass != null && pass.equals(user.password)));
            }
            boolean finalOk = ok;
            runOnUiThread(() -> {
                setLoading(false);
                if (finalOk) {
                    Intent i = new Intent(LoginActivity.this, HomeActivity.class);
                    i.addFlags(Intent.FLAG_ACTIVITY_CLEAR_TOP | Intent.FLAG_ACTIVITY_NEW_TASK);
                    startActivity(i);
                    finish();
                } else {
                    errorText.setText("Identifiants invalides");
                    errorText.setVisibility(View.VISIBLE);
                }
            });
        });
    }
}