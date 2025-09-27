package com.example.vogie2.db;
import androidx.room.Entity;
import androidx.room.PrimaryKey;
@Entity(tableName = "trajets")
public class TrajetEntity {
    @PrimaryKey(autoGenerate = true)
    public int id;
    public String depart;
    public String arrivee;
    public String heure_depart;
    public String heure_arrivee;
    public double prix;
}