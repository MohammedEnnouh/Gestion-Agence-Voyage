package com.example.vogie2;
import android.app.AlertDialog;
import android.os.Bundle;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.EditText;
import android.widget.ProgressBar;
import android.widget.Switch;
import android.widget.TextView;
import androidx.annotation.NonNull;
import androidx.annotation.Nullable;
import androidx.fragment.app.Fragment;
import androidx.recyclerview.widget.LinearLayoutManager;
import androidx.recyclerview.widget.RecyclerView;
import com.example.vogie2.api.ClientsApi;
import com.example.vogie2.models.ApiResponse;
import com.example.vogie2.models.Client;
import com.google.android.material.floatingactionbutton.FloatingActionButton;
import com.google.android.material.snackbar.Snackbar;
import java.util.ArrayList;
import java.util.HashMap;
import java.util.List;
import java.util.Map;
import retrofit2.Call;
import retrofit2.Callback;
import retrofit2.Response;
public class VillesFragment extends Fragment implements UsersAdapter.UserActionListener {
    private RecyclerView recyclerView;
    private UsersAdapter adapter;
    private ProgressBar progressBar;
    private TextView errorText;
    private FloatingActionButton fabAdd;
    @Nullable
    @Override
    public View onCreateView(@NonNull LayoutInflater inflater, @Nullable ViewGroup container, @Nullable Bundle savedInstanceState) {
        View v = inflater.inflate(R.layout.fragment_villes, container, false);
        recyclerView = v.findViewById(R.id.recyclerView);
        progressBar = v.findViewById(R.id.progressBar);
        errorText = v.findViewById(R.id.errorText);
        fabAdd = v.findViewById(R.id.fabAdd);
        adapter = new UsersAdapter(this);
        recyclerView.setLayoutManager(new LinearLayoutManager(requireContext()));
        recyclerView.setAdapter(adapter);
        fabAdd.setOnClickListener(v1 -> showCreateDialog());
        loadUsers();
        return v;
    }
    private void setLoading(boolean b){ progressBar.setVisibility(b?View.VISIBLE:View.GONE); }
    private void loadUsers() {
        setLoading(true);
        errorText.setVisibility(View.GONE);
        ClientsApi api = ApiClient.get().create(ClientsApi.class);
        api.list(null).enqueue(new Callback<ApiResponse<List<Client>>>() {
            @Override public void onResponse(Call<ApiResponse<List<Client>>> call, Response<ApiResponse<List<Client>>> response) {
                setLoading(false);
                if (response.isSuccessful() && response.body()!=null && response.body().ok) {
                    adapter.submit(response.body().data!=null?response.body().data:new ArrayList<>());
                } else {
                    errorText.setVisibility(View.VISIBLE);
                    errorText.setText("Error "+response.code());
                }
            }
            @Override public void onFailure(Call<ApiResponse<List<Client>>> call, Throwable t) {
                setLoading(false);
                errorText.setVisibility(View.VISIBLE);
                errorText.setText("Error: "+t.getMessage());
            }
        });
    }
    private void showCreateDialog() {
        View d = LayoutInflater.from(requireContext()).inflate(R.layout.dialog_user, null, false);
        EditText etName = d.findViewById(R.id.editUserName);
        EditText etEmail = d.findViewById(R.id.editUserEmail);
        EditText etPhone = d.findViewById(R.id.editUserPhone);
        EditText etPassword = d.findViewById(R.id.editUserPassword);
        Switch swActive = d.findViewById(R.id.switchActive);
        new AlertDialog.Builder(requireContext())
                .setTitle("Add User")
                .setView(d)
                .setPositiveButton("Add", (dlg,w)->{
                    String name = etName.getText().toString().trim();
                    String email = etEmail.getText().toString().trim();
                    String phone = etPhone.getText().toString().trim();
                    String password = etPassword.getText().toString().trim();
                    if (name.isEmpty()) {
                        Snackbar.make(recyclerView, "Name required", Snackbar.LENGTH_SHORT).show();
                        return;
                    }
                    if (email.isEmpty()) {
                        Snackbar.make(recyclerView, "Email required", Snackbar.LENGTH_SHORT).show();
                        return;
                    }
                    ClientsApi api = ApiClient.get().create(ClientsApi.class);
                    Client client = new Client();
                    client.full_name = name;
                    client.email = email;
                    client.phone = phone.isEmpty() ? null : phone;
                    client.is_active = swActive.isChecked() ? 1 : 0;
                    api.create(client).enqueue(simpleReloadCallback("User added"));
                })
                .setNegativeButton("Cancel", null)
                .show();
    }
    @Override
    public void onEditUser(Client client) {
        View d = LayoutInflater.from(requireContext()).inflate(R.layout.dialog_user, null, false);
        EditText etName = d.findViewById(R.id.editUserName);
        EditText etEmail = d.findViewById(R.id.editUserEmail);
        EditText etPhone = d.findViewById(R.id.editUserPhone);
        EditText etPassword = d.findViewById(R.id.editUserPassword);
        Switch swActive = d.findViewById(R.id.switchActive);

        etName.setText(client.full_name != null ? client.full_name : "");
        etEmail.setText(client.email != null ? client.email : "");
        etPhone.setText(client.phone != null ? client.phone : "");

        swActive.setChecked(client.is_active != null && client.is_active == 1);
        new AlertDialog.Builder(requireContext())
                .setTitle("Edit User: " + client.full_name)
                .setView(d)
                .setPositiveButton("Update", (dlg,w)->{
                    String name = etName.getText().toString().trim();
                    String email = etEmail.getText().toString().trim();
                    String phone = etPhone.getText().toString().trim();
                    String password = etPassword.getText().toString().trim();
                    if (name.isEmpty()) {
                        Snackbar.make(recyclerView, "Name required", Snackbar.LENGTH_SHORT).show();
                        return;
                    }
                    if (email.isEmpty()) {
                        Snackbar.make(recyclerView, "Email required", Snackbar.LENGTH_SHORT).show();
                        return;
                    }
                    ClientsApi api = ApiClient.get().create(ClientsApi.class);
                    Client updatedClient = new Client();
                    updatedClient.id = client.id;
                    updatedClient.full_name = name;
                    updatedClient.email = email;
                    updatedClient.phone = phone.isEmpty() ? null : phone;
                    updatedClient.is_active = swActive.isChecked() ? 1 : 0;
                    api.update(client.id, updatedClient).enqueue(simpleReloadCallback("User updated"));
                })
                .setNegativeButton("Cancel", null)
                .show();
    }
    @Override
    public void onDeleteUser(Client client) {
        new AlertDialog.Builder(requireContext())
                .setTitle("Delete User")
                .setMessage("Delete user " + client.full_name + "?")
                .setPositiveButton("Delete", (d,w)->{
                    ClientsApi api = ApiClient.get().create(ClientsApi.class);
                    api.delete(client.id).enqueue(simpleReloadCallback("User deleted"));
                })
                .setNegativeButton("Cancel", null)
                .show();
    }
    private Callback<ApiResponse<java.util.Map<String,Object>>> simpleReloadCallback(String okMsg) {
        return new Callback<ApiResponse<java.util.Map<String, Object>>>() {
            @Override public void onResponse(Call<ApiResponse<java.util.Map<String, Object>>> call, Response<ApiResponse<java.util.Map<String, Object>>> response) {
                if (response.isSuccessful() && response.body()!=null && response.body().ok) {
                    Snackbar.make(recyclerView, okMsg, Snackbar.LENGTH_SHORT).show();
                    loadUsers();
                } else {
                    Snackbar.make(recyclerView, "Error", Snackbar.LENGTH_SHORT).show();
                }
            }
            @Override public void onFailure(Call<ApiResponse<java.util.Map<String, Object>>> call, Throwable t) {
                Snackbar.make(recyclerView, "Error: "+t.getMessage(), Snackbar.LENGTH_SHORT).show();
            }
        };
    }
}