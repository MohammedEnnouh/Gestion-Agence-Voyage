package com.example.vogie;
import android.content.Intent;
import android.net.Uri;
import android.os.Bundle;
import android.view.MenuItem;
import android.view.View;
import android.widget.Button;
import android.widget.ImageView;
import android.widget.TextView;
import android.widget.Toast;
import androidx.annotation.NonNull;
import androidx.appcompat.app.AppCompatActivity;
import androidx.appcompat.widget.Toolbar;
import com.example.vogie.model.City;
import com.google.android.material.floatingactionbutton.FloatingActionButton;
public class CityDetailActivity extends AppCompatActivity {
    private City city;
    private boolean isFavorite = false;
    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_city_detail);

        if (getIntent() != null && getIntent().hasExtra("city_name")) {
            int cityId = getIntent().getIntExtra("city_id", -1);
            String cityName = getIntent().getStringExtra("city_name");
            int imageResId = getIntent().getIntExtra("city_image_res_id", -1);
            String description = getIntent().getStringExtra("city_description");
            if (cityId == -1 || cityName == null || imageResId == -1 || description == null) {
                Toast.makeText(this, "Error loading city details", Toast.LENGTH_SHORT).show();
                finish();
                return;
            }

            city = new City(cityId, cityName, imageResId, description);
        } else {
            Toast.makeText(this, "No city data provided", Toast.LENGTH_SHORT).show();
            finish();
            return;
        }
        setupToolbar();
        setupViews();
    }
    private void setupToolbar() {
        Toolbar toolbar = findViewById(R.id.toolbar);
        setSupportActionBar(toolbar);
        if (getSupportActionBar() != null) {
            getSupportActionBar().setDisplayHomeAsUpEnabled(true);
            getSupportActionBar().setTitle("");
        }
    }
    private void setupViews() {

        ImageView cityImage = findViewById(R.id.cityImage);
        TextView cityName = findViewById(R.id.cityName);
        TextView cityDescription = findViewById(R.id.cityDescription);
        Button btnViewOnMap = findViewById(R.id.btnViewOnMap);
        FloatingActionButton fabFavorite = findViewById(R.id.fabFavorite);

        cityImage.setImageResource(city.getImageResId());
        cityName.setText(city.getName());
        cityDescription.setText(city.getDescription());

        fabFavorite.setBackgroundTintList(getColorStateList(R.color.favorite_red));
        updateFavoriteButton(fabFavorite);
        fabFavorite.setOnClickListener(v -> toggleFavorite(fabFavorite));

        btnViewOnMap.setOnClickListener(v -> viewOnMap());
    }
    private void toggleFavorite(FloatingActionButton fab) {
        isFavorite = !isFavorite;
        updateFavoriteButton(fab);
        String message = isFavorite ?
            getString(R.string.added_to_favorites, city.getName()) :
            getString(R.string.removed_from_favorites, city.getName());
        Toast.makeText(this, message, Toast.LENGTH_SHORT).show();
    }
    private void updateFavoriteButton(FloatingActionButton fab) {
        int iconRes = isFavorite ?
            R.drawable.ic_favorite :
            R.drawable.ic_favorite_border;
        fab.setImageResource(iconRes);
    }
    private void viewOnMap() {
        if (city == null) {
            Toast.makeText(this, "City data not available", Toast.LENGTH_SHORT).show();
            return;
        }
        try {

            String location = "geo:0,0?q=" + Uri.encode(city.getName() + ", Morocco");
            Uri gmmIntentUri = Uri.parse(location);

            Intent mapIntent = new Intent(Intent.ACTION_VIEW, gmmIntentUri);
            mapIntent.setPackage("com.google.android.apps.maps");

            if (mapIntent.resolveActivity(getPackageManager()) != null) {
                startActivity(mapIntent);
            } else {

                String webUrl = "https://www.google.com/maps/search/?api=1&query=" +
                    Uri.encode(city.getName() + ", Morocco");
                Intent webIntent = new Intent(Intent.ACTION_VIEW, Uri.parse(webUrl));
                startActivity(webIntent);
            }
        } catch (Exception e) {
            Toast.makeText(this, "Error opening map: " + e.getMessage(), Toast.LENGTH_SHORT).show();
        }
    }
    @Override
    public boolean onOptionsItemSelected(@NonNull MenuItem item) {
        if (item.getItemId() == android.R.id.home) {
            onBackPressed();
            return true;
        }
        return super.onOptionsItemSelected(item);
    }
}