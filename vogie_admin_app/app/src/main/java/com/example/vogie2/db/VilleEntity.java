package com.example.vogie2.db;
import androidx.room.Entity;
import androidx.room.PrimaryKey;
@Entity(tableName = "villes")
public class VilleEntity {
    @PrimaryKey(autoGenerate = true)
    public int id;
    public String nom;
}