package com.example.vogie2;
import android.graphics.Color;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.TextView;
import androidx.annotation.NonNull;
import androidx.recyclerview.widget.RecyclerView;
import com.example.vogie2.models.Booking;
import java.text.NumberFormat;
import java.util.ArrayList;
import java.util.List;
import java.util.Locale;
public class PaymentsAdapter extends RecyclerView.Adapter<PaymentsAdapter.VH> {
    public interface Listener { void onConfirmPayment(Booking b); }
    private final List<Booking> data = new ArrayList<>();
    private final Listener listener;
    public PaymentsAdapter(Listener l) { this.listener = l; }
    public void submit(List<Booking> items) {
        data.clear();
        if (items != null) data.addAll(items);
        notifyDataSetChanged();
    }
    @NonNull
    @Override
    public VH onCreateViewHolder(@NonNull ViewGroup parent, int viewType) {
        View v = LayoutInflater.from(parent.getContext()).inflate(R.layout.item_payment, parent, false);
        return new VH(v);
    }
    @Override
    public void onBindViewHolder(@NonNull VH h, int position) {
        Booking b = data.get(position);
        h.ref.setText("#" + (b.booking_reference != null ? b.booking_reference : b.id));
        h.route.setText((b.depart != null ? b.depart : "") + " → " + (b.arrivee != null ? b.arrivee : ""));
        double amt = b.total_amount != null ? b.total_amount : 0.0;
        h.amount.setText(String.format(Locale.FRANCE, "%.2f MAD", amt));
        String status = b.payment_status != null ? b.payment_status : "pending";
        boolean isCompleted = "completed".equalsIgnoreCase(status) || "paid".equalsIgnoreCase(status);
        boolean isPending = "pending".equalsIgnoreCase(status);

        if (isCompleted) {

            h.status.setText("✅ COMPLETED");
            h.status.setTextColor(Color.parseColor("#FFFFFF"));
            h.status.setBackgroundColor(Color.parseColor("#4CAF50"));
            h.status.setPadding(16, 8, 16, 8);
            h.confirm.setVisibility(View.GONE);

            ((com.google.android.material.card.MaterialCardView) h.itemView)
                .setCardBackgroundColor(Color.parseColor("#E8F5E9"));
        } else if (isPending) {

            h.status.setText("⏳ PENDING");
            h.status.setTextColor(Color.parseColor("#FFFFFF"));
            h.status.setBackgroundColor(Color.parseColor("#FF9800"));
            h.status.setPadding(16, 8, 16, 8);
            h.confirm.setVisibility(View.VISIBLE);
            h.confirm.setOnClickListener(v -> { if (listener != null) listener.onConfirmPayment(b); });

            ((com.google.android.material.card.MaterialCardView) h.itemView)
                .setCardBackgroundColor(Color.parseColor("#FFF8E1"));
        } else {

            h.status.setText(status.toUpperCase());
            h.status.setTextColor(Color.parseColor("#FFFFFF"));
            h.status.setBackgroundColor(Color.parseColor("#757575"));
            h.status.setPadding(16, 8, 16, 8);
            h.confirm.setVisibility(View.VISIBLE);
            h.confirm.setOnClickListener(v -> { if (listener != null) listener.onConfirmPayment(b); });

            ((com.google.android.material.card.MaterialCardView) h.itemView)
                .setCardBackgroundColor(Color.parseColor("#FFFFFF"));
        }

        h.status.setBackground(createRoundedBackground(isCompleted ? "#4CAF50" : isPending ? "#FF9800" : "#757575"));
    }
    private android.graphics.drawable.GradientDrawable createRoundedBackground(String color) {
        android.graphics.drawable.GradientDrawable drawable = new android.graphics.drawable.GradientDrawable();
        drawable.setShape(android.graphics.drawable.GradientDrawable.RECTANGLE);
        drawable.setCornerRadius(20f);
        drawable.setColor(Color.parseColor(color));
        return drawable;
    }
    @Override
    public int getItemCount() { return data.size(); }
    static class VH extends RecyclerView.ViewHolder {
        TextView ref, route, amount, status, confirm;
        VH(@NonNull View itemView) {
            super(itemView);
            ref = itemView.findViewById(R.id.tvRef);
            route = itemView.findViewById(R.id.tvRoute);
            amount = itemView.findViewById(R.id.tvAmount);
            status = itemView.findViewById(R.id.tvStatus);
            confirm = itemView.findViewById(R.id.btnConfirm);
        }
    }
}