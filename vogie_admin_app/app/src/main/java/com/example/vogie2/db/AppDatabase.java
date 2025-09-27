package com.example.vogie2.db;
import android.content.Context;
import androidx.annotation.NonNull;
import androidx.room.Database;
import androidx.room.Room;
import androidx.room.RoomDatabase;
import androidx.sqlite.db.SupportSQLiteDatabase;
import java.util.ArrayList;
import java.util.List;
import java.util.concurrent.ExecutorService;
import java.util.concurrent.Executors;
@Database(entities = {UserEntity.class, VilleEntity.class, TrajetEntity.class}, version = 1, exportSchema = false)
public abstract class AppDatabase extends RoomDatabase {
    public abstract UserDao userDao();
    public abstract VilleDao villeDao();
    public abstract TrajetDao trajetDao();
    private static volatile AppDatabase INSTANCE;
    private static final ExecutorService SEED_EXECUTOR = Executors.newSingleThreadExecutor();
    public static AppDatabase get(Context context) {
        if (INSTANCE == null) {
            synchronized (AppDatabase.class) {
                if (INSTANCE == null) {
                    INSTANCE = Room.databaseBuilder(context.getApplicationContext(), AppDatabase.class, "vogie_local.db")
                            .addCallback(new Callback() {
                                @Override
                                public void onCreate(@NonNull SupportSQLiteDatabase db) {
                                    super.onCreate(db);
                                    seed(context.getApplicationContext());
                                }
                            })
                            .build();
                }
            }
        }
        return INSTANCE;
    }
    private static void seed(Context ctx) {
        SEED_EXECUTOR.execute(() -> {
            AppDatabase db = get(ctx);

            if (db.userDao().findByEmail("admin@local.app") == null) {
                UserEntity admin = new UserEntity();
                admin.full_name = "Administrateur";
                admin.email = "admin@local.app";
                admin.role = "admin";
                admin.password = "admin123";
                db.userDao().insert(admin);
            }

            List<VilleEntity> villes = new ArrayList<>();
            String[] noms = new String[]{"Casablanca","Rabat","Marrakech","Fès","Tanger","Agadir"};
            for (String n : noms) {
                VilleEntity v = new VilleEntity();
                v.nom = n;
                villes.add(v);
            }
            db.villeDao().insertAll(villes);

            List<TrajetEntity> trajets = new ArrayList<>();
            String[][] routes = new String[][]{
                    {"Casablanca","Rabat","08:00","10:00","70"},
                    {"Rabat","Fès","09:00","12:30","120"},
                    {"Marrakech","Agadir","07:30","10:00","90"},
                    {"Tanger","Rabat","13:00","16:00","110"}
            };
            for (String[] r : routes) {
                TrajetEntity t = new TrajetEntity();
                t.depart = r[0];
                t.arrivee = r[1];
                t.heure_depart = r[2];
                t.heure_arrivee = r[3];
                t.prix = Double.parseDouble(r[4]);
                db.trajetDao().insert(t);
            }
        });
    }
}