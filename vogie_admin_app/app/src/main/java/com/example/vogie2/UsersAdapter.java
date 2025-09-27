package com.example.vogie2;
import android.graphics.Color;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.TextView;
import androidx.annotation.NonNull;
import androidx.recyclerview.widget.RecyclerView;
import com.example.vogie2.models.Client;
import java.util.ArrayList;
import java.util.List;
public class UsersAdapter extends RecyclerView.Adapter<UsersAdapter.UserViewHolder> {
    public interface UserActionListener {
        void onEditUser(Client client);
        void onDeleteUser(Client client);
    }
    private final List<Client> users = new ArrayList<>();
    private final UserActionListener listener;
    public UsersAdapter(UserActionListener listener) {
        this.listener = listener;
    }
    public void submit(List<Client> newUsers) {
        users.clear();
        if (newUsers != null) {
            users.addAll(newUsers);
        }
        notifyDataSetChanged();
    }
    @NonNull
    @Override
    public UserViewHolder onCreateViewHolder(@NonNull ViewGroup parent, int viewType) {
        View view = LayoutInflater.from(parent.getContext()).inflate(R.layout.item_ville, parent, false);
        return new UserViewHolder(view);
    }
    @Override
    public void onBindViewHolder(@NonNull UserViewHolder holder, int position) {
        Client client = users.get(position);

        holder.userName.setText(client.full_name != null ? client.full_name : "Unknown User");

        String emailPhone = "";
        if (client.email != null && !client.email.isEmpty()) {
            emailPhone = client.email;
        }
        if (client.phone != null && !client.phone.isEmpty()) {
            if (!emailPhone.isEmpty()) emailPhone += " • ";
            emailPhone += "📱 " + client.phone;
        }
        if (emailPhone.isEmpty()) emailPhone = "No contact info";
        holder.userEmail.setText(emailPhone);

        holder.userId.setText("ID: " + client.id);

        holder.userRole.setText("USER");

        boolean isActive = client.is_active != null && client.is_active == 1;
        holder.userStatus.setText(isActive ? "ACTIVE" : "INACTIVE");
        holder.userStatus.setTextColor(Color.WHITE);
        holder.userStatus.setBackground(createRoundedBackground(isActive ? "#4CAF50" : "#F44336"));

        holder.btnEdit.setOnClickListener(v -> {
            if (listener != null) listener.onEditUser(client);
        });
        holder.btnDelete.setOnClickListener(v -> {
            if (listener != null) listener.onDeleteUser(client);
        });
    }
    @Override
    public int getItemCount() {
        return users.size();
    }
    private android.graphics.drawable.GradientDrawable createRoundedBackground(String color) {
        android.graphics.drawable.GradientDrawable drawable = new android.graphics.drawable.GradientDrawable();
        drawable.setShape(android.graphics.drawable.GradientDrawable.RECTANGLE);
        drawable.setCornerRadius(20f);
        drawable.setColor(Color.parseColor(color));
        return drawable;
    }
    static class UserViewHolder extends RecyclerView.ViewHolder {
        TextView userName, userEmail, userId, userRole, userStatus, btnEdit, btnDelete;
        UserViewHolder(@NonNull View itemView) {
            super(itemView);
            userName = itemView.findViewById(R.id.villeName);
            userEmail = itemView.findViewById(R.id.userEmail);
            userId = itemView.findViewById(R.id.villeId);
            userRole = itemView.findViewById(R.id.userRole);
            userStatus = itemView.findViewById(R.id.userStatus);
            btnEdit = itemView.findViewById(R.id.btnEdit);
            btnDelete = itemView.findViewById(R.id.btnDelete);
        }
    }
}