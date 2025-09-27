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
import androidx.recyclerview.widget.LinearLayoutManager;
import androidx.recyclerview.widget.RecyclerView;
import java.util.ArrayList;
import java.util.List;
public class DashboardFragment extends Fragment {
    private TextView tvTotalClients, tvActiveReservations, tvMonthlyRevenue;
    private RecyclerView rvPopularDestinations;
    private CardView cardStats1, cardStats2, cardStats3;
    @Nullable
    @Override
    public View onCreateView(@NonNull LayoutInflater inflater, @Nullable ViewGroup container, @Nullable Bundle savedInstanceState) {
        View view = inflater.inflate(R.layout.fragment_dashboard, container, false);
        initViews(view);
        setupData();
        return view;
    }
    private void initViews(View view) {
        tvTotalClients = view.findViewById(R.id.tv_total_clients);
        tvActiveReservations = view.findViewById(R.id.tv_active_reservations);
        tvMonthlyRevenue = view.findViewById(R.id.tv_monthly_revenue);
        rvPopularDestinations = view.findViewById(R.id.rv_popular_destinations);
        cardStats1 = view.findViewById(R.id.card_stats_1);
        cardStats2 = view.findViewById(R.id.card_stats_2);
        cardStats3 = view.findViewById(R.id.card_stats_3);

        rvPopularDestinations.setLayoutManager(new LinearLayoutManager(getContext()));
    }
    private void setupData() {

        tvTotalClients.setText("1,247");
        tvActiveReservations.setText("89");
        tvMonthlyRevenue.setText("€45,230");

        List<Destination> popularDestinations = getPopularDestinations();
        DestinationAdapter adapter = new DestinationAdapter(popularDestinations);
        rvPopularDestinations.setAdapter(adapter);
    }
    private List<Destination> getPopularDestinations() {
        List<Destination> destinations = new ArrayList<>();
        destinations.add(new Destination("Paris, France", "€1,200", "4.8★"));
        destinations.add(new Destination("Bali, Indonésie", "€2,500", "4.9★"));
        destinations.add(new Destination("Tokyo, Japon", "€3,200", "4.7★"));
        destinations.add(new Destination("New York, USA", "€2,800", "4.6★"));
        destinations.add(new Destination("Rome, Italie", "€1,500", "4.8★"));
        return destinations;
    }

    public static class Destination {
        private String name;
        private String price;
        private String rating;
        public Destination(String name, String price, String rating) {
            this.name = name;
            this.price = price;
            this.rating = rating;
        }
        public String getName() { return name; }
        public String getPrice() { return price; }
        public String getRating() { return rating; }
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
                    .inflate(R.layout.item_destination, parent, false);
            return new ViewHolder(view);
        }
        @Override
        public void onBindViewHolder(@NonNull ViewHolder holder, int position) {
            Destination destination = destinations.get(position);
            holder.tvName.setText(destination.getName());
            holder.tvPrice.setText(destination.getPrice());
            holder.tvRating.setText(destination.getRating());
        }
        @Override
        public int getItemCount() {
            return destinations.size();
        }
        class ViewHolder extends RecyclerView.ViewHolder {
            TextView tvName, tvPrice, tvRating;
            ViewHolder(View itemView) {
                super(itemView);
                tvName = itemView.findViewById(R.id.tv_destination_name);
                tvPrice = itemView.findViewById(R.id.tv_destination_price);
                tvRating = itemView.findViewById(R.id.tv_destination_rating);
            }
        }
    }
}