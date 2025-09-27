package com.example.vogie2;
import android.app.AlertDialog;
import android.os.Bundle;
import android.text.InputType;
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
import com.example.vogie2.api.TrajetsApi;
import com.example.vogie2.api.VillesApi;
import com.example.vogie2.models.ApiResponse;
import com.example.vogie2.models.Trajet;
import com.example.vogie2.models.Ville;
import com.google.android.material.floatingactionbutton.FloatingActionButton;
import com.google.android.material.snackbar.Snackbar;
import java.util.ArrayList;
import java.util.List;
import java.util.Locale;
import java.util.Map;
import retrofit2.Call;
import retrofit2.Callback;
import retrofit2.Response;
public class TrajetsFragment extends Fragment {
    private RecyclerView recyclerView;
    private TrajetsAdapter adapter;
    private ProgressBar progressBar;
    private TextView errorText;
    private FloatingActionButton fabAdd;
    private List<Ville> cities = new ArrayList<>();
    @Nullable
    @Override
    public View onCreateView(@NonNull LayoutInflater inflater, @Nullable ViewGroup container, @Nullable Bundle savedInstanceState) {
        View v = inflater.inflate(R.layout.fragment_trajets, container, false);
        recyclerView = v.findViewById(R.id.recyclerView);
        progressBar = v.findViewById(R.id.progressBar);
        errorText = v.findViewById(R.id.errorText);
        fabAdd = v.findViewById(R.id.fabAdd);
        adapter = new TrajetsAdapter();
        recyclerView.setLayoutManager(new LinearLayoutManager(requireContext()));
        recyclerView.setAdapter(adapter);
        adapter.registerAdapterDataObserver(new RecyclerView.AdapterDataObserver() {});
        fabAdd.setOnClickListener(v1 -> showCreateDialog());
        recyclerView.addOnItemTouchListener(new RecyclerItemClickListener(requireContext(), recyclerView, new RecyclerItemClickListener.OnItemClickListener() {
            @Override public void onItemClick(View view, int position) { showEditDialog(position); }
            @Override public void onLongItemClick(View view, int position) { confirmDelete(position); }
        }));
        loadTrajets();
        loadCities();
        return v;
    }
    private void setLoading(boolean loading) {
        progressBar.setVisibility(loading ? View.VISIBLE : View.GONE);
        setGlobalLoading(loading);
    }
    private void loadTrajets() {
        setLoading(true);
        errorText.setVisibility(View.GONE);
        TrajetsApi api = ApiClient.get().create(TrajetsApi.class);
        api.list(null).enqueue(new Callback<ApiResponse<List<Trajet>>>() {
            @Override
            public void onResponse(Call<ApiResponse<List<Trajet>>> call, Response<ApiResponse<List<Trajet>>> response) {
                setLoading(false);
                if (response.isSuccessful() && response.body()!=null && response.body().ok) {
                    List<Trajet> items = response.body().data != null ? response.body().data : new ArrayList<>();
                    adapter.submit(items);
                } else {
                    errorText.setVisibility(View.VISIBLE);
                    errorText.setText("Erreur " + response.code());
                }
            }
            @Override
            public void onFailure(Call<ApiResponse<List<Trajet>>> call, Throwable t) {
                setLoading(false);
                errorText.setVisibility(View.VISIBLE);
                errorText.setText("Erreur: " + t.getMessage());
            }
        });
    }
    private void setGlobalLoading(boolean show) {
        if (getActivity() instanceof HomeActivity) {
            ((HomeActivity) getActivity()).showGlobalLoading(show);
        }
    }
    private void loadCities() {
        VillesApi villesApi = ApiClient.get().create(VillesApi.class);
        villesApi.list().enqueue(new Callback<ApiResponse<List<Ville>>>() {
            @Override
            public void onResponse(Call<ApiResponse<List<Ville>>> call, Response<ApiResponse<List<Ville>>> response) {
                if (response.isSuccessful() && response.body() != null && response.body().ok) {
                    cities.clear();
                    if (response.body().data != null) {
                        cities.addAll(response.body().data);
                    }
                    android.util.Log.d("TrajetsFragment", "Loaded " + cities.size() + " cities");
                } else {
                    android.util.Log.w("TrajetsFragment", "Failed to load cities");
                }
            }
            @Override
            public void onFailure(Call<ApiResponse<List<Ville>>> call, Throwable t) {
                android.util.Log.e("TrajetsFragment", "Error loading cities", t);
            }
        });
    }
    private void showCreateDialog() {
        View dialog = LayoutInflater.from(requireContext()).inflate(R.layout.dialog_trajet, null, false);
        EditText etDepart = dialog.findViewById(R.id.etDepart);
        EditText etArrivee = dialog.findViewById(R.id.etArrivee);
        EditText etHd = dialog.findViewById(R.id.etHeureDepart);
        EditText etHa = dialog.findViewById(R.id.etHeureArrivee);
        EditText etPrix = dialog.findViewById(R.id.etPrix);
        new AlertDialog.Builder(requireContext())
                .setTitle("Ajouter un trajet")
                .setView(dialog)
                .setPositiveButton("Ajouter", (d, w) -> {
                    String depart = etDepart.getText().toString().trim();
                    String arrivee = etArrivee.getText().toString().trim();
                    String hd = etHd.getText().toString().trim();
                    String ha = etHa.getText().toString().trim();
                    double prix = 0;
                    try { prix = Double.parseDouble(etPrix.getText().toString().trim()); } catch (Exception ignored) {}
                    if (depart.isEmpty() || arrivee.isEmpty() || hd.isEmpty() || ha.isEmpty() || prix <= 0) {
                        Snackbar.make(recyclerView, "Champs invalides", Snackbar.LENGTH_SHORT).show();
                        return;
                    }

                    Ville departVille = cities.stream().filter(c -> c.nom.equalsIgnoreCase(depart)).findFirst().orElse(null);
                    Ville arriveeVille = cities.stream().filter(c -> c.nom.equalsIgnoreCase(arrivee)).findFirst().orElse(null);
                    if (departVille == null || arriveeVille == null) {
                        Snackbar.make(recyclerView, "Villes non trouvées. Vérifiez les noms des villes.", Snackbar.LENGTH_SHORT).show();
                        return;
                    }

                    createTrajet(departVille.id, arriveeVille.id, hd, ha, prix);
                })
                .setNegativeButton("Annuler", null)
                .show();
    }
    private void showEditDialog(int position) {
        Trajet t = getItem(position);
        if (t == null) return;
        View dialog = LayoutInflater.from(requireContext()).inflate(R.layout.dialog_trajet, null, false);
        EditText etDepart = dialog.findViewById(R.id.etDepart);
        EditText etArrivee = dialog.findViewById(R.id.etArrivee);
        EditText etHd = dialog.findViewById(R.id.etHeureDepart);
        EditText etHa = dialog.findViewById(R.id.etHeureArrivee);
        EditText etPrix = dialog.findViewById(R.id.etPrix);
        etDepart.setText(t.depart);
        etArrivee.setText(t.arrivee);
        etHd.setText(t.heure_depart);
        etHa.setText(t.heure_arrivee);
        etPrix.setText(String.format(Locale.FRANCE, "%.2f", t.prix));
        new AlertDialog.Builder(requireContext())
                .setTitle("Modifier le trajet")
                .setView(dialog)
                .setPositiveButton("Enregistrer", (d, w) -> {

                    Snackbar.make(recyclerView, "Edition UI prête (nécessite mapping villes)", Snackbar.LENGTH_SHORT).show();
                })
                .setNegativeButton("Annuler", null)
                .show();
    }
    private void confirmDelete(int position) {
        Trajet t = getItem(position);
        if (t == null) return;
        new AlertDialog.Builder(requireContext())
                .setTitle("Supprimer")
                .setMessage("Supprimer ce trajet " + t.depart + " → " + t.arrivee + " ?")
                .setPositiveButton("Supprimer", (d, w) -> doDelete(t.id))
                .setNegativeButton("Annuler", null)
                .show();
    }
    private void doDelete(int id) {
        setLoading(true);
        TrajetsApi api = ApiClient.get().create(TrajetsApi.class);
        api.delete(id).enqueue(new Callback<ApiResponse<java.util.Map<String,Object>>>() {
            @Override public void onResponse(Call<ApiResponse<java.util.Map<String, Object>>> call, Response<ApiResponse<java.util.Map<String, Object>>> response) {
                setLoading(false);
                if (response.isSuccessful() && response.body()!=null && response.body().ok) {
                    Snackbar.make(recyclerView, "Supprimé", Snackbar.LENGTH_SHORT).show();
                    loadTrajets();
                } else {
                    Snackbar.make(recyclerView, "Erreur suppression", Snackbar.LENGTH_SHORT).show();
                }
            }
            @Override public void onFailure(Call<ApiResponse<java.util.Map<String, Object>>> call, Throwable t) {
                setLoading(false);
                Snackbar.make(recyclerView, "Erreur: "+t.getMessage(), Snackbar.LENGTH_SHORT).show();
            }
        });
    }
    @Nullable
    private Trajet getItem(int position) {
        try { return adapter != null ? adapterPosition(adapter, position) : null; }
        catch (Exception ignored) { return null; }
    }
    private Trajet adapterPosition(TrajetsAdapter a, int position) {

        try {
            java.lang.reflect.Field f = TrajetsAdapter.class.getDeclaredField("data");
            f.setAccessible(true);
            java.util.List list = (java.util.List) f.get(a);
            return (Trajet) list.get(position);
        } catch (Exception e) {
            return null;
        }
    }
    private void createTrajet(int villeDepart, int villeArrivee, String heureDepart, String heureArrivee, double prix) {
        setLoading(true);
        TrajetsApi trajetsApi = ApiClient.get().create(TrajetsApi.class);

        Trajet newTrajet = new Trajet();
        newTrajet.ville_depart_id = villeDepart;
        newTrajet.ville_arrivee_id = villeArrivee;
        newTrajet.heure_depart = heureDepart;
        newTrajet.heure_arrivee = heureArrivee;
        newTrajet.prix = prix;
        trajetsApi.create(newTrajet).enqueue(new Callback<ApiResponse<Map<String, Object>>>() {
            @Override
            public void onResponse(Call<ApiResponse<Map<String, Object>>> call, Response<ApiResponse<Map<String, Object>>> response) {
                setLoading(false);
                if (response.isSuccessful() && response.body() != null && response.body().ok) {
                    Snackbar.make(recyclerView, "Trajet créé avec succès", Snackbar.LENGTH_SHORT).show();
                    loadTrajets();
                } else {
                    String errorMsg = "Erreur lors de la création";
                    if (response.body() != null && response.body().error != null) {
                        errorMsg = response.body().error;
                    }
                    Snackbar.make(recyclerView, errorMsg, Snackbar.LENGTH_SHORT).show();
                }
            }
            @Override
            public void onFailure(Call<ApiResponse<Map<String, Object>>> call, Throwable t) {
                setLoading(false);
                Snackbar.make(recyclerView, "Erreur réseau: " + t.getMessage(), Snackbar.LENGTH_SHORT).show();
                android.util.Log.e("TrajetsFragment", "Error creating trajet", t);
            }
        });
    }
}