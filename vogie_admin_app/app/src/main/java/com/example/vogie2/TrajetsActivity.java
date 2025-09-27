package com.example.vogie2;
import android.os.Bundle;
import android.view.View;
import android.widget.ProgressBar;
import android.widget.TextView;
import androidx.annotation.Nullable;
import androidx.appcompat.app.AppCompatActivity;
import androidx.recyclerview.widget.LinearLayoutManager;
import androidx.recyclerview.widget.RecyclerView;
import com.example.vogie2.db.AppDatabase;
import com.example.vogie2.db.TrajetDao;
import com.example.vogie2.db.TrajetEntity;
import com.example.vogie2.models.Trajet;
import java.util.ArrayList;
import java.util.List;
import java.util.concurrent.ExecutorService;
import java.util.concurrent.Executors;
public class TrajetsActivity extends AppCompatActivity {
    private RecyclerView recyclerView;
    private TrajetsAdapter adapter;
    private ProgressBar progressBar;
    private TextView errorText;
    private final ExecutorService executor = Executors.newSingleThreadExecutor();
    @Override
    protected void onCreate(@Nullable Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_trajets);
        recyclerView = findViewById(R.id.recyclerView);
        progressBar = findViewById(R.id.progressBar);
        errorText = findViewById(R.id.errorText);
        adapter = new TrajetsAdapter();
        recyclerView.setLayoutManager(new LinearLayoutManager(this));
        recyclerView.setAdapter(adapter);
        loadTrajets();
    }
    private void loadTrajets() {
        progressBar.setVisibility(View.VISIBLE);
        errorText.setVisibility(View.GONE);
        executor.execute(() -> {
            TrajetDao dao = AppDatabase.get(getApplicationContext()).trajetDao();
            List<TrajetEntity> rows = dao.getAll();
            List<Trajet> mapped = new ArrayList<>();
            for (TrajetEntity te : rows) {
                Trajet t = new Trajet();
                t.id = te.id;
                t.depart = te.depart;
                t.arrivee = te.arrivee;
                t.heure_depart = te.heure_depart;
                t.heure_arrivee = te.heure_arrivee;
                t.prix = te.prix;
                mapped.add(t);
            }
            runOnUiThread(() -> {
                progressBar.setVisibility(View.GONE);
                adapter.submit(mapped);
            });
        });
    }
}