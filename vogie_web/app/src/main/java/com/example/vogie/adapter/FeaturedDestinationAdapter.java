package com.example.vogie.adapter;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.ImageView;
import android.widget.TextView;
import androidx.annotation.NonNull;
import androidx.recyclerview.widget.RecyclerView;
import com.example.vogie.R;
import com.example.vogie.model.Destination;
import java.util.List;
public class FeaturedDestinationAdapter extends RecyclerView.Adapter<FeaturedDestinationAdapter.ViewHolder> {
    private final List<Destination> destinations;
    private final OnDestinationClickListener listener;
    public interface OnDestinationClickListener {
        void onDestinationClick(Destination destination);
    }
    public FeaturedDestinationAdapter(List<Destination> destinations, OnDestinationClickListener listener) {
        this.destinations = destinations;
        this.listener = listener;
    }
    @NonNull
    @Override
    public ViewHolder onCreateViewHolder(@NonNull ViewGroup parent, int viewType) {
        View view = LayoutInflater.from(parent.getContext())
                .inflate(R.layout.item_featured_destination, parent, false);
        return new ViewHolder(view);
    }
    @Override
    public void onBindViewHolder(@NonNull ViewHolder holder, int position) {
        Destination destination = destinations.get(position);
        holder.tvDestinationName.setText(destination.getName());
        holder.tvPrice.setText(destination.getPrice());
        holder.ivDestination.setImageResource(destination.getImageResId());
        holder.itemView.setOnClickListener(v -> {
            if (listener != null) {
                listener.onDestinationClick(destination);
            }
        });
    }
    @Override
    public int getItemCount() {
        return destinations.size();
    }
    static class ViewHolder extends RecyclerView.ViewHolder {
        final ImageView ivDestination;
        final TextView tvDestinationName;
        final TextView tvPrice;
        ViewHolder(View itemView) {
            super(itemView);
            ivDestination = itemView.findViewById(R.id.ivDestination);
            tvDestinationName = itemView.findViewById(R.id.tvDestinationName);
            tvPrice = itemView.findViewById(R.id.tvDestinationPrice);
        }
    }
}