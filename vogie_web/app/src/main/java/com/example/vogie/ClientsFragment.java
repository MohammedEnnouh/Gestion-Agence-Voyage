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
public class ClientsFragment extends Fragment {
    private RecyclerView rvClients;
    private TextView tvNoClients;
    @Nullable
    @Override
    public View onCreateView(@NonNull LayoutInflater inflater, @Nullable ViewGroup container, @Nullable Bundle savedInstanceState) {
        View view = inflater.inflate(R.layout.fragment_clients, container, false);
        initViews(view);
        setupData();
        return view;
    }
    private void initViews(View view) {
        rvClients = view.findViewById(R.id.rv_clients);
        tvNoClients = view.findViewById(R.id.tv_no_clients);
        rvClients.setLayoutManager(new LinearLayoutManager(getContext()));
    }
    private void setupData() {
        List<Client> clients = getSampleClients();
        if (clients.isEmpty()) {
            tvNoClients.setVisibility(View.VISIBLE);
            rvClients.setVisibility(View.GONE);
        } else {
            tvNoClients.setVisibility(View.GONE);
            rvClients.setVisibility(View.VISIBLE);
            ClientAdapter adapter = new ClientAdapter(clients);
            rvClients.setAdapter(adapter);
        }
    }
    private List<Client> getSampleClients() {
        List<Client> clients = new ArrayList<>();
        clients.add(new Client("Marie Dubois", "marie.dubois@email.com", "+33 6 12 34 56 78", "Active"));
        clients.add(new Client("Jean Martin", "jean.martin@email.com", "+33 6 98 76 54 32", "Active"));
        clients.add(new Client("Sophie Bernard", "sophie.bernard@email.com", "+33 6 11 22 33 44", "Inactive"));
        clients.add(new Client("Pierre Moreau", "pierre.moreau@email.com", "+33 6 55 66 77 88", "Active"));
        clients.add(new Client("Claire Petit", "claire.petit@email.com", "+33 6 99 88 77 66", "Active"));
        return clients;
    }

    public static class Client {
        private String name;
        private String email;
        private String phone;
        private String status;
        public Client(String name, String email, String phone, String status) {
            this.name = name;
            this.email = email;
            this.phone = phone;
            this.status = status;
        }
        public String getName() { return name; }
        public String getEmail() { return email; }
        public String getPhone() { return phone; }
        public String getStatus() { return status; }
    }

    private class ClientAdapter extends RecyclerView.Adapter<ClientAdapter.ViewHolder> {
        private List<Client> clients;
        public ClientAdapter(List<Client> clients) {
            this.clients = clients;
        }
        @NonNull
        @Override
        public ViewHolder onCreateViewHolder(@NonNull ViewGroup parent, int viewType) {
            View view = LayoutInflater.from(parent.getContext())
                    .inflate(R.layout.item_client, parent, false);
            return new ViewHolder(view);
        }
        @Override
        public void onBindViewHolder(@NonNull ViewHolder holder, int position) {
            Client client = clients.get(position);
            holder.tvName.setText(client.getName());
            holder.tvEmail.setText(client.getEmail());
            holder.tvPhone.setText(client.getPhone());
            holder.tvStatus.setText(client.getStatus());

            if ("Active".equals(client.getStatus())) {
                holder.tvStatus.setTextColor(getResources().getColor(R.color.success_green));
            } else {
                holder.tvStatus.setTextColor(getResources().getColor(R.color.text_secondary));
            }
        }
        @Override
        public int getItemCount() {
            return clients.size();
        }
        class ViewHolder extends RecyclerView.ViewHolder {
            TextView tvName, tvEmail, tvPhone, tvStatus;
            ViewHolder(View itemView) {
                super(itemView);
                tvName = itemView.findViewById(R.id.tv_client_name);
                tvEmail = itemView.findViewById(R.id.tv_client_email);
                tvPhone = itemView.findViewById(R.id.tv_client_phone);
                tvStatus = itemView.findViewById(R.id.tv_client_status);
            }
        }
    }
}