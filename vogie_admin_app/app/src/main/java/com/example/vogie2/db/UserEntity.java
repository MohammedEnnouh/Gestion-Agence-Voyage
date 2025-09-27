package com.example.vogie2.db;
import androidx.room.Entity;
import androidx.room.PrimaryKey;
@Entity(tableName = "users")
public class UserEntity {
    @PrimaryKey(autoGenerate = true)
    public int id;
    public String full_name;
    public String email;
    public String role;
    public String password;
}