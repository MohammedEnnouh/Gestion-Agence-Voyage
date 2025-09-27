package com.example.vogie2.db;
import androidx.room.Dao;
import androidx.room.Insert;
import androidx.room.OnConflictStrategy;
import androidx.room.Query;
import java.util.List;
@Dao
public interface VilleDao {
    @Query("SELECT * FROM villes ORDER BY nom")
    List<VilleEntity> getAll();
    @Insert(onConflict = OnConflictStrategy.REPLACE)
    void insertAll(List<VilleEntity> villes);
    @Insert(onConflict = OnConflictStrategy.REPLACE)
    long insert(VilleEntity ville);
    @Query("DELETE FROM villes")
    void clear();
}