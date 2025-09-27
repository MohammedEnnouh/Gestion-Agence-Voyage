package com.example.vogie;
import android.os.Bundle;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.TextView;
import androidx.annotation.NonNull;
import androidx.annotation.Nullable;
import androidx.cardview.widget.CardView;
import androidx.fragment.app.Fragment;
public class ReportsFragment extends Fragment {
    private CardView cardSalesReport, cardClientReport, cardDestinationReport;
    private TextView tvTotalRevenue, tvTotalBookings, tvAverageRating;
    @Nullable
    @Override
    public View onCreateView(@NonNull LayoutInflater inflater, @Nullable ViewGroup container, @Nullable Bundle savedInstanceState) {
        View view = inflater.inflate(R.layout.fragment_reports, container, false);
        initViews(view);
        setupData();
        return view;
    }
    private void initViews(View view) {
        cardSalesReport = view.findViewById(R.id.card_sales_report);
        cardClientReport = view.findViewById(R.id.card_client_report);
        cardDestinationReport = view.findViewById(R.id.card_destination_report);
        tvTotalRevenue = view.findViewById(R.id.tv_total_revenue);
        tvTotalBookings = view.findViewById(R.id.tv_total_bookings);
        tvAverageRating = view.findViewById(R.id.tv_average_rating);

        cardSalesReport.setOnClickListener(v -> showSalesReport());
        cardClientReport.setOnClickListener(v -> showClientReport());
        cardDestinationReport.setOnClickListener(v -> showDestinationReport());
    }
    private void setupData() {
        tvTotalRevenue.setText("€156,420");
        tvTotalBookings.setText("1,247");
        tvAverageRating.setText("4.7★");
    }
    private void showSalesReport() {

    }
    private void showClientReport() {

    }
    private void showDestinationReport() {

    }
}