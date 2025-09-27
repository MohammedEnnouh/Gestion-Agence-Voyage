package com.example.vogie2;
import android.app.AlertDialog;
import android.os.Bundle;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.EditText;
import android.widget.ProgressBar;
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
public class ClientsFragment extends Fragment {
    private RecyclerView recyclerView;
    private ClientsAdapter adapter;
    private ProgressBar progressBar;
    private TextView errorText;
    private FloatingActionButton fabAdd;
    @Nullable
    @Override
    public View onCreateView(@NonNull LayoutInflater inflater, @Nullable ViewGroup container, @Nullable Bundle savedInstanceState) {
        View v = inflater.inflate(R.layout.fragment_clients, container, false);
        recyclerView = v.findViewById(R.id.recyclerView);
        progressBar = v.findViewById(R.id.progressBar);
        errorText = v.findViewById(R.id.errorText);
        fabAdd = v.findViewById(R.id.fabAdd);
        adapter = new ClientsAdapter();
        recyclerView.setLayoutManager(new LinearLayoutManager(requireContext()));
        recyclerView.setAdapter(adapter);
        recyclerView.addOnItemTouchListener(new RecyclerItemClickListener(requireContext(), recyclerView, new RecyclerItemClickListener.OnItemClickListener() {
            @Override public void onItemClick(View view, int position) { showEditDialog(position); }
            @Override public void onLongItemClick(View view, int position) { confirmDelete(position); }
        }));
        fabAdd.setOnClickListener(v1 -> showCreateDialog());
        loadClients();
        return v;
    }
    private void setLoading(boolean b) { progressBar.setVisibility(b?View.VISIBLE:View.GONE); }
    private void loadClients() {
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
                    errorText.setText("Erreur "+response.code());
                }
            }
            @Override public void onFailure(Call<ApiResponse<List<Client>>> call, Throwable t) {
                setLoading(false);
                errorText.setVisibility(View.VISIBLE);
                errorText.setText("Erreur: "+t.getMessage());
            }
        });
    }
    private void showCreateDialog() {
        View d = LayoutInflater.from(requireContext()).inflate(R.layout.dialog_client, null, false);
        EditText etName = d.findViewById(R.id.etName);
        EditText etEmail = d.findViewById(R.id.etEmail);
        new AlertDialog.Builder(requireContext())
                .setTitle("Ajouter client")
                .setView(d)
                .setPositiveButton("Ajouter", (dlg, w) -> {
                    String name = etName.getText().toString().trim();
                    String email = etEmail.getText().toString().trim();
                    if (name.isEmpty()) { Snackbar.make(recyclerView, "Nom requis", Snackbar.LENGTH_SHORT).show(); return; }
                    Client c = new Client();
                    c.full_name = name;
                    c.email = email;
                    ClientsApi api = ApiClient.get().create(ClientsApi.class);
                    api.create(c).enqueue(simpleReloadCallback("Ajouté"));
                })
                .setNegativeButton("Annuler", null)
                .show();
    }
    private void showEditDialog(int pos) {
        Client c = getItem(pos); if (c==null) return;
        View d = LayoutInflater.from(requireContext()).inflate(R.layout.dialog_client, null, false);
        EditText etName = d.findViewById(R.id.etName);
        EditText etEmail = d.findViewById(R.id.etEmail);
        etName.setText(c.full_name);
        etEmail.setText(c.email);
        new AlertDialog.Builder(requireContext())
                .setTitle("Modifier client")
                .setView(d)
                .setPositiveButton("Enregistrer", (dlg,w)->{
                    Map<String,Object> payload = new HashMap<>();
                    if (!etName.getText().toString().trim().isEmpty()) payload.put("full_name", etName.getText().toString().trim());
                    if (!etEmail.getText().toString().trim().isEmpty()) payload.put("email", etEmail.getText().toString().trim());
                    ClientsApi api = ApiClient.get().create(ClientsApi.class);
                    api.update(c.id, new Client(){ { full_name = (String)payload.get("full_name"); email = (String)payload.get("email"); } }).enqueue(simpleReloadCallback("Modifié"));
                })
                .setNegativeButton("Annuler", null)
                .show();
    }
    private void confirmDelete(int pos) {
        Client c = getItem(pos); if (c==null) return;
        new AlertDialog.Builder(requireContext())
                .setTitle("Supprimer")
                .setMessage("Supprimer "+c.full_name+" ?")
                .setPositiveButton("Supprimer", (d,w)->{
                    ClientsApi api = ApiClient.get().create(ClientsApi.class);
                    api.delete(c.id).enqueue(simpleReloadCallback("Supprimé"));
                })
                .setNegativeButton("Annuler", null)
                .show();
    }
    private Callback<ApiResponse<Map<String,Object>>> simpleReloadCallback(String okMsg) {
        return new Callback<ApiResponse<Map<String, Object>>>() {
            @Override public void onResponse(Call<ApiResponse<Map<String, Object>>> call, Response<ApiResponse<Map<String, Object>>> response) {
                if (response.isSuccessful() && response.body()!=null && response.body().ok) {
                    Snackbar.make(recyclerView, okMsg, Snackbar.LENGTH_SHORT).show();
                    loadClients();
                } else {
                    Snackbar.make(recyclerView, "Erreur", Snackbar.LENGTH_SHORT).show();
                }
            }
            @Override public void onFailure(Call<ApiResponse<Map<String, Object>>> call, Throwable t) {
                Snackbar.make(recyclerView, "Erreur: "+t.getMessage(), Snackbar.LENGTH_SHORT).show();
            }
        };
    }
    @Nullable
    private Client getItem(int position) {
        try {
            java.lang.reflect.Field f = ClientsAdapter.class.getDeclaredField("data");
            f.setAccessible(true);
            java.util.List list = (java.util.List) f.get(adapter);
            return (Client) list.get(position);
        } catch (Exception e) { return null; }
    }
}