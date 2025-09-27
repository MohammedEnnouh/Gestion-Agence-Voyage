package com.example.vogie;
import android.os.Bundle;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.TextView;
import androidx.annotation.NonNull;
import androidx.annotation.Nullable;
import androidx.fragment.app.Fragment;
import androidx.recyclerview.widget.LinearLayoutManager;
import androidx.recyclerview.widget.RecyclerView;
import java.util.ArrayList;
import java.util.List;
public class ReservationsFragment extends Fragment {
    private RecyclerView rvReservations;
    private TextView tvNoReservations;
    @Nullable
    @Override
    public View onCreateView(@NonNull LayoutInflater inflater, @Nullable ViewGroup container, @Nullable Bundle savedInstanceState) {
        View view = inflater.inflate(R.layout.fragment_reservations, container, false);
        initViews(view);
        setupData();
        return view;
    }
    private void initViews(View view) {
        rvReservations = view.findViewById(R.id.rv_reservations);
        tvNoReservations = view.findViewById(R.id.tv_no_reservations);
        rvReservations.setLayoutManager(new LinearLayoutManager(getContext()));
    }
    private void setupData() {
        List<Reservation> reservations = getSampleReservations();
        if (reservations.isEmpty()) {
            tvNoReservations.setVisibility(View.VISIBLE);
            rvReservations.setVisibility(View.GONE);
        } else {
            tvNoReservations.setVisibility(View.GONE);
            rvReservations.setVisibility(View.VISIBLE);
            ReservationAdapter adapter = new ReservationAdapter(reservations);
            rvReservations.setAdapter(adapter);
        }
    }
    private List<Reservation> getSampleReservations() {
        List<Reservation> reservations = new ArrayList<>();
        reservations.add(new Reservation("Marie Dubois", "Paris, France", "15/12/2024", "€1,200", "Confirmée"));
        reservations.add(new Reservation("Jean Martin", "Bali, Indonésie", "20/12/2024", "€2,500", "En attente"));
        reservations.add(new Reservation("Sophie Bernard", "Tokyo, Japon", "25/12/2024", "€3,200", "Confirmée"));
        reservations.add(new Reservation("Pierre Moreau", "New York, USA", "30/12/2024", "€2,800", "Annulée"));
        reservations.add(new Reservation("Claire Petit", "Rome, Italie", "05/01/2025", "€1,500", "Confirmée"));
        return reservations;
    }

    public static class Reservation {
        private String clientName;
        private String destination;
        private String date;
        private String price;
        private String status;
        public Reservation(String clientName, String destination, String date, String price, String status) {
            this.clientName = clientName;
            this.destination = destination;
            this.date = date;
            this.price = price;
            this.status = status;
        }
        public String getClientName() { return clientName; }
        public String getDestination() { return destination; }
        public String getDate() { return date; }
        public String getPrice() { return price; }
        public String getStatus() { return status; }
    }

    private class ReservationAdapter extends RecyclerView.Adapter<ReservationAdapter.ViewHolder> {
        private List<Reservation> reservations;
        public ReservationAdapter(List<Reservation> reservations) {
            this.reservations = reservations;
        }
        @NonNull
        @Override
        public ViewHolder onCreateViewHolder(@NonNull ViewGroup parent, int viewType) {
            View view = LayoutInflater.from(parent.getContext())
                    .inflate(R.layout.item_reservation, parent, false);
            return new ViewHolder(view);
        }
        @Override
        public void onBindViewHolder(@NonNull ViewHolder holder, int position) {
            Reservation reservation = reservations.get(position);
            holder.tvClientName.setText(reservation.getClientName());
            holder.tvDestination.setText(reservation.getDestination());
            holder.tvDate.setText(reservation.getDate());
            holder.tvPrice.setText(reservation.getPrice());
            holder.tvStatus.setText(reservation.getStatus());

            switch (reservation.getStatus()) {
                case "Confirmée":
                    holder.tvStatus.setTextColor(getResources().getColor(R.color.success_green));
                    break;
                case "En attente":
                    holder.tvStatus.setTextColor(getResources().getColor(R.color.warning_orange));
                    break;
                case "Annulée":
                    holder.tvStatus.setTextColor(getResources().getColor(R.color.error_red));
                    break;
                default:
                    holder.tvStatus.setTextColor(getResources().getColor(R.color.text_secondary));
                    break;
            }
        }
        @Override
        public int getItemCount() {
            return reservations.size();
        }
        class ViewHolder extends RecyclerView.ViewHolder {
            TextView tvClientName, tvDestination, tvDate, tvPrice, tvStatus;
            ViewHolder(View itemView) {
                super(itemView);
                tvClientName = itemView.findViewById(R.id.tv_reservation_client);
                tvDestination = itemView.findViewById(R.id.tv_reservation_destination);
                tvDate = itemView.findViewById(R.id.tv_reservation_date);
                tvPrice = itemView.findViewById(R.id.tv_reservation_price);
                tvStatus = itemView.findViewById(R.id.tv_reservation_status);
            }
        }
    }
}