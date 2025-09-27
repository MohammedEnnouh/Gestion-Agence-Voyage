package com.example.vogie2.db;
import androidx.room.Dao;
import androidx.room.Insert;
import androidx.room.OnConflictStrategy;
import androidx.room.Query;
import java.util.List;
@Dao
public interface TrajetDao {
    @Query("SELECT * FROM trajets ORDER BY id DESC")
    List<TrajetEntity> getAll();
    @Insert(onConflict = OnConflictStrategy.REPLACE)
    void insertAll(List<TrajetEntity> trajets);
    @Insert(onConflict = OnConflictStrategy.REPLACE)
    long insert(TrajetEntity trajet);
    @Query("DELETE FROM trajets")
    void clear();
}