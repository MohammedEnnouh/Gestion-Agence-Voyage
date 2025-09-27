package com.example.vogie2;
import android.graphics.Color;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.TextView;
import androidx.annotation.NonNull;
import androidx.recyclerview.widget.RecyclerView;
import com.example.vogie2.models.Booking;
import java.util.ArrayList;
import java.util.List;
public class RecentBookingsAdapter extends RecyclerView.Adapter<RecentBookingsAdapter.VH> {
    private final List<Booking> items = new ArrayList<>();
    public void setItems(List<Booking> list) {
        android.util.Log.d("RecentBookingsAdapter", "setItems called with " + (list != null ? list.size() : "null") + " items");
        items.clear();
        if (list != null) {
            items.addAll(list);
            android.util.Log.d("RecentBookingsAdapter", "Added " + items.size() + " items to adapter");
        }
        notifyDataSetChanged();
        android.util.Log.d("RecentBookingsAdapter", "notifyDataSetChanged() called, final size: " + items.size());
    }
    @NonNull
    @Override
    public VH onCreateViewHolder(@NonNull ViewGroup parent, int viewType) {
        android.util.Log.d("RecentBookingsAdapter", "onCreateViewHolder called");
        View v = LayoutInflater.from(parent.getContext()).inflate(R.layout.item_recent_booking, parent, false);
        return new VH(v);
    }
    @Override
    public void onBindViewHolder(@NonNull VH h, int position) {
        android.util.Log.d("RecentBookingsAdapter", "onBindViewHolder called for position: " + position);
        Booking b = items.get(position);
        h.tvRef.setText(b.booking_reference != null ? b.booking_reference : ("#" + b.id));
        String route = (b.depart != null ? b.depart : "?") + " ➜ " + (b.arrivee != null ? b.arrivee : "?");
        h.tvRoute.setText(route);
        if (b.departure_date != null && b.departure_time != null) {
            h.tvDate.setText(b.departure_date + " " + b.departure_time);
        } else if (b.created_at != null) {
            h.tvDate.setText(b.created_at);
        } else {
            h.tvDate.setText("");
        }

        String status = b.payment_status != null ? b.payment_status : "pending";
        boolean isCompleted = "completed".equalsIgnoreCase(status) || "paid".equalsIgnoreCase(status);
        boolean isPending = "pending".equalsIgnoreCase(status);
        if (isCompleted) {

            h.itemView.setBackgroundColor(Color.parseColor("#E8F5E9"));
            h.tvStatus.setText("✅ COMPLETED");
            h.tvStatus.setTextColor(Color.parseColor("#1B5E20"));
            h.tvStatus.setBackground(null);
            h.tvStatus.setPadding(0, 0, 0, 0);
        } else if (isPending) {

            h.itemView.setBackgroundColor(Color.parseColor("#FFF8E1"));
            h.tvStatus.setText("⏳ PENDING");
            h.tvStatus.setTextColor(Color.parseColor("#E65100"));
            h.tvStatus.setBackground(null);
            h.tvStatus.setPadding(0, 0, 0, 0);
        } else {

            h.itemView.setBackgroundColor(Color.parseColor("#FFFFFF"));
            h.tvStatus.setText(status.toUpperCase());
            h.tvStatus.setTextColor(Color.parseColor("#757575"));
            h.tvStatus.setBackground(null);
            h.tvStatus.setPadding(0, 0, 0, 0);
        }
        double amount = b.total_amount != null ? b.total_amount : 0.0;
        h.tvAmount.setText(String.format(java.util.Locale.FRANCE, "%.2f MAD", amount));
        android.util.Log.d("RecentBookingsAdapter", "Bound booking: " + b.booking_reference + " - " + route);
    }
    @Override
    public int getItemCount() {
        android.util.Log.d("RecentBookingsAdapter", "getItemCount() called, returning: " + items.size());
        return items.size();
    }
    static class VH extends RecyclerView.ViewHolder {
        TextView tvRef, tvAmount, tvRoute, tvDate, tvStatus;
        VH(@NonNull View itemView) {
            super(itemView);
            tvRef = itemView.findViewById(R.id.tvRef);
            tvAmount = itemView.findViewById(R.id.tvAmount);
            tvRoute = itemView.findViewById(R.id.tvRoute);
            tvDate = itemView.findViewById(R.id.tvDate);
            tvStatus = itemView.findViewById(R.id.tvStatus);
        }
    }
}