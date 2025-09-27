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
public class DestinationsFragment extends Fragment {
    private RecyclerView rvDestinations;
    private TextView tvNoDestinations;
    @Nullable
    @Override
    public View onCreateView(@NonNull LayoutInflater inflater, @Nullable ViewGroup container, @Nullable Bundle savedInstanceState) {
        View view = inflater.inflate(R.layout.fragment_destinations, container, false);
        initViews(view);
        setupData();
        return view;
    }
    private void initViews(View view) {
        rvDestinations = view.findViewById(R.id.rv_destinations);
        tvNoDestinations = view.findViewById(R.id.tv_no_destinations);
        rvDestinations.setLayoutManager(new LinearLayoutManager(getContext()));
    }
    private void setupData() {
        List<Destination> destinations = getSampleDestinations();
        if (destinations.isEmpty()) {
            tvNoDestinations.setVisibility(View.VISIBLE);
            rvDestinations.setVisibility(View.GONE);
        } else {
            tvNoDestinations.setVisibility(View.GONE);
            rvDestinations.setVisibility(View.VISIBLE);
            DestinationAdapter adapter = new DestinationAdapter(destinations);
            rvDestinations.setAdapter(adapter);
        }
    }
    private List<Destination> getSampleDestinations() {
        List<Destination> destinations = new ArrayList<>();
        destinations.add(new Destination("Paris, France", "€1,200", "4.8★", "Découvrez la ville de l'amour"));
        destinations.add(new Destination("Bali, Indonésie", "€2,500", "4.9★", "Paradis tropical"));
        destinations.add(new Destination("Tokyo, Japon", "€3,200", "4.7★", "Métropole moderne"));
        destinations.add(new Destination("New York, USA", "€2,800", "4.6★", "La ville qui ne dort jamais"));
        destinations.add(new Destination("Rome, Italie", "€1,500", "4.8★", "Histoire et culture"));
        return destinations;
    }

    public static class Destination {
        private String name;
        private String price;
        private String rating;
        private String description;
        public Destination(String name, String price, String rating, String description) {
            this.name = name;
            this.price = price;
            this.rating = rating;
            this.description = description;
        }
        public String getName() { return name; }
        public String getPrice() { return price; }
        public String getRating() { return rating; }
        public String getDescription() { return description; }
    }

    private class DestinationAdapter extends RecyclerView.Adapter<DestinationAdapter.ViewHolder> {
        private List<Destination> destinations;
        public DestinationAdapter(List<Destination> destinations) {
            this.destinations = destinations;
        }
        @NonNull
        @Override
        public ViewHolder onCreateViewHolder(@NonNull ViewGroup parent, int viewType) {
            View view = LayoutInflater.from(parent.getContext())
                    .inflate(R.layout.item_destination_full, parent, false);
            return new ViewHolder(view);
        }
        @Override
        public void onBindViewHolder(@NonNull ViewHolder holder, int position) {
            Destination destination = destinations.get(position);
            holder.tvName.setText(destination.getName());
            holder.tvPrice.setText(destination.getPrice());
            holder.tvRating.setText(destination.getRating());
            holder.tvDescription.setText(destination.getDescription());
        }
        @Override
        public int getItemCount() {
            return destinations.size();
        }
        class ViewHolder extends RecyclerView.ViewHolder {
            TextView tvName, tvPrice, tvRating, tvDescription;
            ViewHolder(View itemView) {
                super(itemView);
                tvName = itemView.findViewById(R.id.tv_destination_name);
                tvPrice = itemView.findViewById(R.id.tv_destination_price);
                tvRating = itemView.findViewById(R.id.tv_destination_rating);
                tvDescription = itemView.findViewById(R.id.tv_destination_description);
            }
        }
    }
}