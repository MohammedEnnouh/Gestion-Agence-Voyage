package com.example.vogie.adapter;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.TextView;
import androidx.annotation.NonNull;
import androidx.recyclerview.widget.RecyclerView;
import com.example.vogie.R;
import com.example.vogie.model.Trip;
import java.util.List;
import java.util.Locale;
public class TripAdapter extends RecyclerView.Adapter<TripAdapter.TripViewHolder> {
    public interface OnTripClickListener {
        void onTripClick(Trip trip);
    }
    private List<Trip> trips;
    private OnTripClickListener listener;
    public TripAdapter(List<Trip> trips, OnTripClickListener listener) {
        this.trips = trips;
        this.listener = listener;
    }
    @NonNull
    @Override
    public TripViewHolder onCreateViewHolder(@NonNull ViewGroup parent, int viewType) {
        View view = LayoutInflater.from(parent.getContext()).inflate(R.layout.item_trip, parent, false);
        return new TripViewHolder(view);
    }
    @Override
    public void onBindViewHolder(@NonNull TripViewHolder holder, int position) {
        Trip trip = trips.get(position);
        holder.bind(trip, listener);
    }
    @Override
    public int getItemCount() {
        return trips.size();
    }
    static class TripViewHolder extends RecyclerView.ViewHolder {
        private TextView timeText;
        private TextView priceText;
        private TextView totalText;
        public TripViewHolder(@NonNull View itemView) {
            super(itemView);
            timeText = itemView.findViewById(R.id.tripTime);
            priceText = itemView.findViewById(R.id.tripPrice);
            totalText = itemView.findViewById(R.id.tripTotal);
        }
        public void bind(Trip trip, OnTripClickListener listener) {
            timeText.setText(trip.getTime());
            priceText.setText(String.format(Locale.getDefault(), "%.2f € / personne", trip.getPricePerPerson()));
            totalText.setText(String.format(Locale.getDefault(), "Total: %.2f €", trip.getTotal()));
            itemView.setOnClickListener(v -> {
                if (listener != null) {
                    listener.onTripClick(trip);
                }
            });
        }
    }
}