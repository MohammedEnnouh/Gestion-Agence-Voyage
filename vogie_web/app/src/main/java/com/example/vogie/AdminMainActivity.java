package com.example.vogie;

import android.content.Intent;
import android.os.Bundle;
import android.view.MenuItem;
import android.view.View;
import android.widget.TextView;
import androidx.annotation.NonNull;
import androidx.appcompat.app.AppCompatActivity;
import androidx.cardview.widget.CardView;
import androidx.recyclerview.widget.LinearLayoutManager;
import androidx.recyclerview.widget.RecyclerView;
import com.example.vogie.adapter.AdminStatsAdapter;
import com.example.vogie.adapter.QuickActionAdapter;
import com.example.vogie.model.AdminStat;
import com.example.vogie.model.QuickAction;
import com.google.android.material.bottomnavigation.BottomNavigationView;
import java.util.ArrayList;
import java.util.List;
public class AdminMainActivity extends AppCompatActivity {
    private TextView tvWelcomeAdmin;
    private RecyclerView rvStats, rvQuickActions;
    private CardView cardCities, cardRoutes, cardPayments, cardUsers;
    private AdminStatsAdapter statsAdapter;
    private QuickActionAdapter quickActionAdapter;
    private List<AdminStat> statsList = new ArrayList<>();
    private List<QuickAction> quickActionsList = new ArrayList<>();
    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_admin_main);
        initViews();
        setupBottomNavigation();
        setupData();
        setupRecyclerViews();
    }
    private void initViews() {
        tvWelcomeAdmin = findViewById(R.id.tv_welcome_admin);
        rvStats = findViewById(R.id.rv_stats);
        rvQuickActions = findViewById(R.id.rv_quick_actions);
        cardCities = findViewById(R.id.card_cities);
        cardRoutes = findViewById(R.id.card_routes);
        cardPayments = findViewById(R.id.card_payments);
        cardUsers = findViewById(R.id.card_users);
        setupCardClickListeners();
    }
    private void setupCardClickListeners() {
        cardCities.setOnClickListener(v -> {
            Intent intent = new Intent(this, AdminCitiesActivity.class);
            startActivity(intent);
        });
        cardRoutes.setOnClickListener(v -> {
            Intent intent = new Intent(this, AdminRoutesActivity.class);
            startActivity(intent);
        });
        cardPayments.setOnClickListener(v -> {
            Intent intent = new Intent(this, AdminPaymentsActivity.class);
            startActivity(intent);
        });
        cardUsers.setOnClickListener(v -> {
            Intent intent = new Intent(this, AdminUsersActivity.class);
            startActivity(intent);
        });
    }
    private void setupBottomNavigation() {
        BottomNavigationView bottomNav = findViewById(R.id.bottom_navigation);
        bottomNav.setOnItemSelectedListener(this::onNavigationItemSelected);
    }
    private boolean onNavigationItemSelected(MenuItem item) {
        int itemId = item.getItemId();
        if (itemId == R.id.nav_dashboard) {

            return true;
        } else if (itemId == R.id.nav_cities) {
            startActivity(new Intent(this, AdminCitiesActivity.class));
            return true;
        } else if (itemId == R.id.nav_routes) {
            startActivity(new Intent(this, AdminRoutesActivity.class));
            return true;
        } else if (itemId == R.id.nav_payments) {
            startActivity(new Intent(this, AdminPaymentsActivity.class));
            return true;
        } else if (itemId == R.id.nav_users) {
            startActivity(new Intent(this, AdminUsersActivity.class));
            return true;
        }
        return false;
    }
    private void setupData() {

        statsList.clear();
        statsList.add(new AdminStat("Réservations", "156", R.drawable.ic_bookings, "#4CAF50"));
        statsList.add(new AdminStat("Paiements Espèces", "23", R.drawable.ic_cash, "#FF9800"));
        statsList.add(new AdminStat("Paiements en Ligne", "133", R.drawable.ic_online_payment, "#2196F3"));
        statsList.add(new AdminStat("Villes", "12", R.drawable.ic_cities, "#9C27B0"));
        statsList.add(new AdminStat("Trajets", "24", R.drawable.ic_routes, "#607D8B"));
        statsList.add(new AdminStat("Utilisateurs", "89", R.drawable.ic_users, "#795548"));

        quickActionsList.clear();
        quickActionsList.add(new QuickAction("Gérer les Villes", "Ajouter, modifier, supprimer des villes", R.drawable.ic_cities, "#9C27B0"));
        quickActionsList.add(new QuickAction("Gérer les Trajets", "Configurer les routes et prix", R.drawable.ic_routes, "#607D8B"));
        quickActionsList.add(new QuickAction("Paiements Espèces", "Confirmer les paiements en espèces", R.drawable.ic_cash, "#FF9800"));
        quickActionsList.add(new QuickAction("Paiements en Ligne", "Voir les paiements Stripe", R.drawable.ic_online_payment, "#2196F3"));
        quickActionsList.add(new QuickAction("Gérer les Utilisateurs", "Administrer les comptes utilisateurs", R.drawable.ic_users, "#795548"));
        quickActionsList.add(new QuickAction("Rapports", "Statistiques et analyses", R.drawable.ic_reports, "#E91E63"));
    }
    private void setupRecyclerViews() {

        statsAdapter = new AdminStatsAdapter(statsList);
        rvStats.setLayoutManager(new LinearLayoutManager(this, LinearLayoutManager.HORIZONTAL, false));
        rvStats.setAdapter(statsAdapter);

        quickActionAdapter = new QuickActionAdapter(quickActionsList, this::onQuickActionClick);
        rvQuickActions.setLayoutManager(new LinearLayoutManager(this));
        rvQuickActions.setAdapter(quickActionAdapter);
    }
    private void onQuickActionClick(QuickAction action) {
        String title = action.getTitle();
        if (title.contains("Villes")) {
            startActivity(new Intent(this, AdminCitiesActivity.class));
        } else if (title.contains("Trajets")) {
            startActivity(new Intent(this, AdminRoutesActivity.class));
        } else if (title.contains("Espèces")) {
            startActivity(new Intent(this, AdminPaymentsActivity.class));
        } else if (title.contains("en Ligne")) {
            startActivity(new Intent(this, AdminPaymentsActivity.class));
        } else if (title.contains("Utilisateurs")) {
            startActivity(new Intent(this, AdminUsersActivity.class));
        } else if (title.contains("Rapports")) {
            startActivity(new Intent(this, AdminReportsActivity.class));
        }
    }
}