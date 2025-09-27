package com.example.vogie2;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.TextView;
import androidx.annotation.NonNull;
import androidx.recyclerview.widget.RecyclerView;
import com.example.vogie2.models.Trajet;
import java.util.ArrayList;
import java.util.List;
public class TrajetsAdapter extends RecyclerView.Adapter<TrajetsAdapter.VH> {
    private final List<Trajet> data = new ArrayList<>();
    public void submit(List<Trajet> items) {
        data.clear();
        if (items != null) data.addAll(items);
        notifyDataSetChanged();
    }
    @NonNull
    @Override
    public VH onCreateViewHolder(@NonNull ViewGroup parent, int viewType) {
        View view = LayoutInflater.from(parent.getContext()).inflate(R.layout.item_trajet, parent, false);
        return new VH(view);
    }
    @Override
    public void onBindViewHolder(@NonNull VH holder, int position) {
        Trajet t = data.get(position);
        String route = (t.depart != null ? t.depart : "?") + " \u2192 " + (t.arrivee != null ? t.arrivee : "?");
        holder.tvRoute.setText(route);
        holder.tvHours.setText((t.heure_depart != null ? t.heure_depart : "--:--") + " - " + (t.heure_arrivee != null ? t.heure_arrivee : "--:--"));
        holder.tvPrice.setText("MAD " + String.format(java.util.Locale.FRANCE, "%.2f", t.prix));
    }
    @Override
    public int getItemCount() {
        return data.size();
    }
    static class VH extends RecyclerView.ViewHolder {
        TextView tvRoute, tvHours, tvPrice;
        VH(@NonNull View itemView) {
            super(itemView);
            tvRoute = itemView.findViewById(R.id.tvRoute);
            tvHours = itemView.findViewById(R.id.tvHours);
            tvPrice = itemView.findViewById(R.id.tvPrice);
        }
    }
}