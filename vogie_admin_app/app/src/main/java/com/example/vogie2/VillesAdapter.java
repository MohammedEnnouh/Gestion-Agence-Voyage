package com.example.vogie2;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.TextView;
import androidx.annotation.NonNull;
import androidx.recyclerview.widget.RecyclerView;
import com.example.vogie2.models.Ville;
import java.util.ArrayList;
import java.util.List;
public class VillesAdapter extends RecyclerView.Adapter<VillesAdapter.VH> {
    private final List<Ville> data = new ArrayList<>();
    public void submit(List<Ville> items) {
        data.clear();
        if (items != null) data.addAll(items);
        notifyDataSetChanged();
    }
    @NonNull
    @Override
    public VH onCreateViewHolder(@NonNull ViewGroup parent, int viewType) {
        View view = LayoutInflater.from(parent.getContext()).inflate(R.layout.item_ville, parent, false);
        return new VH(view);
    }
    @Override
    public void onBindViewHolder(@NonNull VH holder, int position) {
        Ville v = data.get(position);
        holder.name.setText(v.nom);
        holder.id.setText("#" + v.id);
    }
    @Override
    public int getItemCount() {
        return data.size();
    }
    static class VH extends RecyclerView.ViewHolder {
        TextView name;
        TextView id;
        VH(@NonNull View itemView) {
            super(itemView);
            name = itemView.findViewById(R.id.villeName);
            id = itemView.findViewById(R.id.villeId);
        }
    }
}