package com.example.vogie2;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.TextView;
import androidx.annotation.NonNull;
import androidx.recyclerview.widget.RecyclerView;
import com.example.vogie2.models.Client;
import java.util.ArrayList;
import java.util.List;
public class ClientsAdapter extends RecyclerView.Adapter<ClientsAdapter.VH> {
    private final List<Client> data = new ArrayList<>();
    public void submit(List<Client> items) {
        data.clear();
        if (items != null) data.addAll(items);
        notifyDataSetChanged();
    }
    @NonNull
    @Override
    public VH onCreateViewHolder(@NonNull ViewGroup parent, int viewType) {
        View v = LayoutInflater.from(parent.getContext()).inflate(R.layout.item_client, parent, false);
        return new VH(v);
    }
    @Override
    public void onBindViewHolder(@NonNull VH h, int position) {
        Client c = data.get(position);
        h.name.setText(c.full_name != null ? c.full_name : (c.nom != null ? c.nom : ""));
        h.email.setText(c.email != null ? c.email : "");
        h.id.setText("#" + c.id);
    }
    @Override
    public int getItemCount() { return data.size(); }
    static class VH extends RecyclerView.ViewHolder {
        TextView name, email, id;
        VH(@NonNull View itemView) {
            super(itemView);
            name = itemView.findViewById(R.id.tvName);
            email = itemView.findViewById(R.id.tvEmail);
            id = itemView.findViewById(R.id.tvId);
        }
    }
}