package com.example.vogie2.db;
import androidx.room.Dao;
import androidx.room.Insert;
import androidx.room.OnConflictStrategy;
import androidx.room.Query;
@Dao
public interface UserDao {
    @Query("SELECT * FROM users WHERE email = :email LIMIT 1")
    UserEntity findByEmail(String email);
    @Insert(onConflict = OnConflictStrategy.REPLACE)
    long insert(UserEntity user);
}