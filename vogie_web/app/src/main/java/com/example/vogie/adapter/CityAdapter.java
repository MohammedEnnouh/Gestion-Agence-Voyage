package com.example.vogie.adapter;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.ImageView;
import android.widget.TextView;
import androidx.annotation.NonNull;
import androidx.recyclerview.widget.RecyclerView;
import com.example.vogie.R;
import com.example.vogie.model.City;
import com.google.android.material.chip.Chip;
import java.util.List;
public class CityAdapter extends RecyclerView.Adapter<CityAdapter.CityViewHolder> {
    private final List<City> cities;
    private final OnCityClickListener listener;
    public interface OnCityClickListener {
        void onCityClick(City city);
    }
    public CityAdapter(List<City> cities, OnCityClickListener listener) {
        this.cities = cities;
        this.listener = listener;
    }
    @NonNull
    @Override
    public CityViewHolder onCreateViewHolder(@NonNull ViewGroup parent, int viewType) {
        View view = LayoutInflater.from(parent.getContext())
                .inflate(R.layout.item_popular_destination, parent, false);
        return new CityViewHolder(view);
    }
    @Override
    public void onBindViewHolder(@NonNull CityViewHolder holder, int position) {
        City city = cities.get(position);
        holder.tvDestinationName.setText(city.getName());
        holder.ivDestination.setImageResource(city.getImageResId());
        if (city.getDescription() != null && !city.getDescription().isEmpty()) {
            holder.tvDescription.setVisibility(View.VISIBLE);
            holder.tvDescription.setText(city.getDescription());
        } else {
            holder.tvDescription.setVisibility(View.GONE);
        }

        if (city.getPrice() != null && !city.getPrice().isEmpty()) {
            holder.chipPrice.setVisibility(View.VISIBLE);
            holder.chipPrice.setText(city.getPrice());
        } else {
            holder.chipPrice.setVisibility(View.GONE);
        }

        holder.itemView.setOnClickListener(v -> {
            if (listener != null) {
                listener.onCityClick(city);
            }
        });
    }
    @Override
    public int getItemCount() {
        return cities != null ? cities.size() : 0;
    }
    public void updateList(List<City> newList) {
        this.cities = newList;
        notifyDataSetChanged();
    }
    static class CityViewHolder extends RecyclerView.ViewHolder {
        final ImageView ivDestination;
        final TextView tvDestinationName;
        final TextView tvDescription;
        final Chip chipPrice;
        CityViewHolder(View itemView) {
            super(itemView);
            ivDestination = itemView.findViewById(R.id.ivDestination);
            tvDestinationName = itemView.findViewById(R.id.tvDestinationName);
            tvDescription = itemView.findViewById(R.id.tvDescription);
            chipPrice = itemView.findViewById(R.id.chipPrice);
        }
    }
}