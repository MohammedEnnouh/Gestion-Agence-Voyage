package com.example.vogie2;
import android.os.Bundle;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
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
import java.util.ArrayList;
import java.util.Collections;
import java.util.Comparator;
import java.util.List;
import retrofit2.Call;
import retrofit2.Callback;
import retrofit2.Response;
public class DashboardFragment extends Fragment {
    private TextView tvVilles, tvBookings, tvClients, tvMoney, tvNoBookings;
    private RecyclerView rvRecentBookings;
    private RecentBookingsAdapter recentAdapter;
    @Nullable
    @Override
    public View onCreateView(@NonNull LayoutInflater inflater, @Nullable ViewGroup container, @Nullable Bundle savedInstanceState) {
        View v = inflater.inflate(R.layout.fragment_dashboard, container, false);

        tvVilles = v.findViewById(R.id.tvVilles);
        tvBookings = v.findViewById(R.id.tvBookings);
        tvClients = v.findViewById(R.id.tvClients);
        tvMoney = v.findViewById(R.id.tvMoney);
        rvRecentBookings = v.findViewById(R.id.rvRecentBookings);
        tvNoBookings = v.findViewById(R.id.tvNoBookings);

        if (rvRecentBookings != null) {
            android.util.Log.d("DashboardFragment", "Setting up RecyclerView...");
            rvRecentBookings.setLayoutManager(new LinearLayoutManager(getContext()));
            recentAdapter = new RecentBookingsAdapter();
            rvRecentBookings.setAdapter(recentAdapter);

            rvRecentBookings.setVisibility(android.view.View.VISIBLE);
            rvRecentBookings.setMinimumHeight(200);
            android.util.Log.d("DashboardFragment", "RecyclerView setup completed");
        } else {
            android.util.Log.e("DashboardFragment", "rvRecentBookings is null!");
        }

        loadStats();
        return v;
    }
    private void selectBottomTab(int id){
        if (getActivity() instanceof HomeActivity){
            ((HomeActivity) getActivity()).selectTab(id);
        }
    }
    private void loadStats() {
        setGlobalLoading(true);

        android.util.Log.d("DashboardFragment", "API Base URL: " + ApiClient.getBaseUrl());
        VillesApi villesApi = ApiClient.get().create(VillesApi.class);
        TrajetsApi trajetsApi = ApiClient.get().create(TrajetsApi.class);
        ClientsApi clientsApi = ApiClient.get().create(ClientsApi.class);
        BookingsApi bookingsApi = ApiClient.get().create(BookingsApi.class);

        android.util.Log.d("DashboardFragment", "Starting Villes API call...");
        villesApi.list().enqueue(new Callback<ApiResponse<List<Ville>>>() {
            @Override public void onResponse(Call<ApiResponse<List<Ville>>> call, Response<ApiResponse<List<Ville>>> response) {
                android.util.Log.d("DashboardFragment", "Villes API response - Success: " + response.isSuccessful() + ", Code: " + response.code());
                int count = 0;
                if (response.isSuccessful() && response.body()!=null) {
                    android.util.Log.d("DashboardFragment", "Villes response body exists, ok: " + response.body().ok);
                    if (response.body().data != null) {
                        count = response.body().data.size();
                        android.util.Log.d("DashboardFragment", "Villes count: " + count);
                    } else {
                        android.util.Log.w("DashboardFragment", "Villes data is null");
                    }
                } else {
                    android.util.Log.w("DashboardFragment", "Villes API unsuccessful or null body");
                }
                if (tvVilles != null) tvVilles.setText(String.valueOf(count));
            }
            @Override public void onFailure(Call<ApiResponse<List<Ville>>> call, Throwable t) {
                android.util.Log.e("DashboardFragment", "Villes API call failed", t);

                if (tvVilles != null) tvVilles.setText("0");
            }
        });

        android.util.Log.d("DashboardFragment", "Starting Clients API call...");
        clientsApi.list(null).enqueue(new Callback<ApiResponse<List<Client>>>() {
            @Override public void onResponse(Call<ApiResponse<List<Client>>> call, Response<ApiResponse<List<Client>>> response) {
                android.util.Log.d("DashboardFragment", "Clients API response - Success: " + response.isSuccessful() + ", Code: " + response.code());
                int count = 0;
                if (response.isSuccessful() && response.body()!=null) {
                    android.util.Log.d("DashboardFragment", "Clients response body exists, ok: " + response.body().ok);
                    if (response.body().data != null) {
                        count = response.body().data.size();
                        android.util.Log.d("DashboardFragment", "Clients count: " + count);
                    } else {
                        android.util.Log.w("DashboardFragment", "Clients data is null");
                    }
                } else {
                    android.util.Log.w("DashboardFragment", "Clients API unsuccessful or null body");
                }
                if (tvClients != null) tvClients.setText(String.valueOf(count));
            }
            @Override public void onFailure(Call<ApiResponse<List<Client>>> call, Throwable t) {
                android.util.Log.e("DashboardFragment", "Clients API call failed", t);

                if (tvClients != null) tvClients.setText("0");
            }
        });

        bookingsApi.list(null).enqueue(new Callback<ApiResponse<List<Booking>>>() {
            @Override public void onResponse(Call<ApiResponse<List<Booking>>> call, Response<ApiResponse<List<Booking>>> response) {
                android.util.Log.d("DashboardFragment", "Bookings API response received. Success: " + response.isSuccessful());
                int bookingCount = 0;
                double totalMoney = 0.0;
                List<Booking> bookingList = null;
                if (response.isSuccessful() && response.body()!=null && response.body().data!=null) {
                    bookingList = response.body().data;
                    bookingCount = bookingList.size();
                    android.util.Log.d("DashboardFragment", "Received " + bookingCount + " bookings from API");

                    for (Booking b : bookingList) {
                        if (b != null && b.total_amount != null &&
                            ("completed".equals(b.payment_status) || "paid".equals(b.payment_status))) {
                            totalMoney += b.total_amount;
                            android.util.Log.d("DashboardFragment", "Added paid booking: " + b.booking_reference + " - " + b.total_amount);
                        }
                    }
                    android.util.Log.d("DashboardFragment", "Total revenue from paid bookings only: " + totalMoney);
                } else {
                    android.util.Log.w("DashboardFragment", "API response unsuccessful or empty data");
                }

                if (tvBookings != null) tvBookings.setText(String.valueOf(bookingCount));
                if (tvMoney != null) tvMoney.setText(String.format(java.util.Locale.FRANCE, "%.2f", totalMoney));

                updateRecentBookings(bookingList);
                setGlobalLoading(false);
            }
            @Override public void onFailure(Call<ApiResponse<List<Booking>>> call, Throwable t) {
                android.util.Log.e("DashboardFragment", "Bookings API call failed", t);

                if (tvBookings != null) tvBookings.setText("0");
                if (tvMoney != null) tvMoney.setText("0.00");

                android.util.Log.d("DashboardFragment", "API failed, no Recent Bookings to show");
                updateRecentBookings(null);
                setGlobalLoading(false);
            }
        });
    }
    private void updateRecentBookings(List<Booking> bookingList) {
        android.util.Log.d("DashboardFragment", "updateRecentBookings called with list size: " + (bookingList != null ? bookingList.size() : "null"));
        if (recentAdapter != null) {
            if (bookingList != null && !bookingList.isEmpty()) {

                List<Booking> recentList = new ArrayList<>(bookingList);
                try {
                    Collections.sort(recentList, new Comparator<Booking>() {
                        @Override public int compare(Booking o1, Booking o2) {
                            String a = o1!=null && o1.created_at!=null ? o1.created_at : "";
                            String b = o2!=null && o2.created_at!=null ? o2.created_at : "";
                            return b.compareTo(a);
                        }
                    });
                } catch (Exception e) {
                    android.util.Log.e("DashboardFragment", "Error sorting bookings", e);
                }

                android.util.Log.d("DashboardFragment", "Setting " + recentList.size() + " REAL bookings from database to adapter");
                recentAdapter.setItems(recentList);

                if (tvNoBookings != null) tvNoBookings.setVisibility(android.view.View.GONE);
                if (rvRecentBookings != null) rvRecentBookings.setVisibility(android.view.View.VISIBLE);
            } else {
                android.util.Log.d("DashboardFragment", "No bookings available - showing empty message");

                recentAdapter.setItems(new ArrayList<>());

                if (tvNoBookings != null) tvNoBookings.setVisibility(android.view.View.VISIBLE);
                if (rvRecentBookings != null) rvRecentBookings.setVisibility(android.view.View.GONE);
            }
        } else {
            android.util.Log.e("DashboardFragment", "recentAdapter is null!");
        }
    }
    private void setGlobalLoading(boolean show) {
        if (getActivity() instanceof HomeActivity) {
            ((HomeActivity) getActivity()).showGlobalLoading(show);
        }
    }
}