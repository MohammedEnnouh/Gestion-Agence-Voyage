package com.example.vogie2;
import android.content.Intent;
import android.os.Bundle;
import android.view.Menu;
import android.view.MenuItem;
import android.view.View;
import android.widget.ProgressBar;
import android.widget.TextView;
import androidx.annotation.Nullable;
import androidx.appcompat.app.AppCompatActivity;
import androidx.recyclerview.widget.LinearLayoutManager;
import androidx.recyclerview.widget.RecyclerView;
import com.example.vogie2.db.AppDatabase;
import com.example.vogie2.db.VilleDao;
import com.example.vogie2.db.VilleEntity;
import java.util.ArrayList;
import java.util.List;
import java.util.concurrent.ExecutorService;
import java.util.concurrent.Executors;
public class MainActivity extends AppCompatActivity {
    private RecyclerView recyclerView;
    private VillesAdapter adapter;
    private ProgressBar progressBar;
    private TextView errorText;
    private final ExecutorService executor = Executors.newSingleThreadExecutor();
    @Override
    protected void onCreate(@Nullable Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_main);
        recyclerView = findViewById(R.id.recyclerView);
        progressBar = findViewById(R.id.progressBar);
        errorText = findViewById(R.id.errorText);
        adapter = new VillesAdapter();
        recyclerView.setLayoutManager(new LinearLayoutManager(this));
        recyclerView.setAdapter(adapter);
        loadVilles();
    }
    @Override
    public boolean onCreateOptionsMenu(Menu menu) {
        getMenuInflater().inflate(R.menu.main_menu, menu);
        return true;
    }
    @Override
    public boolean onOptionsItemSelected(MenuItem item) {
        if (item.getItemId() == R.id.menu_trajets) {
            startActivity(new Intent(this, TrajetsActivity.class));
            return true;
        }
        return super.onOptionsItemSelected(item);
    }
    private void loadVilles() {
        progressBar.setVisibility(View.VISIBLE);
        errorText.setVisibility(View.GONE);
        executor.execute(() -> {
            VilleDao dao = AppDatabase.get(getApplicationContext()).villeDao();
            List<VilleEntity> list = dao.getAll();
            List<com.example.vogie2.models.Ville> mapped = new ArrayList<>();
            for (VilleEntity ve : list) {
                com.example.vogie2.models.Ville v = new com.example.vogie2.models.Ville();
                v.id = ve.id;
                v.nom = ve.nom;
                mapped.add(v);
            }
            runOnUiThread(() -> {
                progressBar.setVisibility(View.GONE);
                adapter.submit(mapped);
            });
        });
    }
}