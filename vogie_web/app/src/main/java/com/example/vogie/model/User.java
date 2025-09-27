package com.example.vogie.model;
import android.os.Parcel;
import android.os.Parcelable;
public class User implements Parcelable {
    private int id;
    private String fullName;
    private String email;
    private String phone;
    private String createdAt;
    public User(int id, String fullName, String email, String phone, String createdAt) {
        this.id = id;
        this.fullName = fullName;
        this.email = email;
        this.phone = phone;
        this.createdAt = createdAt;
    }
    protected User(Parcel in) {
        id = in.readInt();
        fullName = in.readString();
        email = in.readString();
        phone = in.readString();
        createdAt = in.readString();
    }
    public static final Creator<User> CREATOR = new Creator<User>() {
        @Override
        public User createFromParcel(Parcel in) {
            return new User(in);
        }
        @Override
        public User[] newArray(int size) {
            return new User[size];
        }
    };
    public int getId() {
        return id;
    }
    public String getFullName() {
        return fullName;
    }
    public String getEmail() {
        return email;
    }
    public String getPhone() {
        return phone;
    }
    public String getCreatedAt() {
        return createdAt;
    }
    @Override
    public int describeContents() {
        return 0;
    }
    @Override
    public void writeToParcel(Parcel dest, int flags) {
        dest.writeInt(id);
        dest.writeString(fullName);
        dest.writeString(email);
        dest.writeString(phone);
        dest.writeString(createdAt);
    }
}