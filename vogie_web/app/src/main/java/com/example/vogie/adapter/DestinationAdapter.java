package com.example.vogie.adapter;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.ImageView;
import android.widget.TextView;
import androidx.annotation.NonNull;
import androidx.recyclerview.widget.RecyclerView;
import com.bumptech.glide.Glide;
import com.example.vogie.R;
import com.example.vogie.model.City;
import java.util.List;
public class DestinationAdapter extends RecyclerView.Adapter<DestinationAdapter.DestinationViewHolder> {
    private List<City> destinations;
    private final OnDestinationClickListener listener;
    public interface OnDestinationClickListener {
        void onDestinationClick(City city);
    }
    public DestinationAdapter(List<City> destinations, OnDestinationClickListener listener) {
        this.destinations = destinations;
        this.listener = listener;
    }
    @NonNull
    @Override
    public DestinationViewHolder onCreateViewHolder(@NonNull ViewGroup parent, int viewType) {
        View view = LayoutInflater.from(parent.getContext())
                .inflate(R.layout.item_destination, parent, false);
        return new DestinationViewHolder(view);
    }
    @Override
    public void onBindViewHolder(@NonNull DestinationViewHolder holder, int position) {
        City destination = destinations.get(position);
        holder.bind(destination);
    }
    @Override
    public int getItemCount() {
        return destinations.size();
    }
    public void updateList(List<City> newDestinations) {
        this.destinations = newDestinations;
        notifyDataSetChanged();
    }
    class DestinationViewHolder extends RecyclerView.ViewHolder {
        private final ImageView destinationImage;
        private final TextView destinationName;
        private final TextView destinationDescription;
        public DestinationViewHolder(@NonNull View itemView) {
            super(itemView);
            destinationImage = itemView.findViewById(R.id.destination_image);
            destinationName = itemView.findViewById(R.id.destination_name);
            destinationDescription = itemView.findViewById(R.id.destination_description);
            itemView.setOnClickListener(v -> {
                int position = getAdapterPosition();
                if (position != RecyclerView.NO_POSITION && listener != null) {
                    listener.onDestinationClick(destinations.get(position));
                }
            });
        }
        public void bind(City destination) {
            destinationName.setText(destination.getName());
            destinationDescription.setText(destination.getDescription());

            Glide.with(itemView.getContext())
                    .load(destination.getImageResId())
                    .placeholder(R.drawable.ic_launcher_foreground)
                    .error(R.drawable.ic_launcher_foreground)
                    .centerCrop()
                    .into(destinationImage);
        }
    }
}