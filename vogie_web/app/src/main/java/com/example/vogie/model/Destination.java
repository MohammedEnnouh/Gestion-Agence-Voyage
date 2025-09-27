package com.example.vogie.model;
import android.os.Parcel;
import android.os.Parcelable;
public class Destination implements Parcelable {
    private String name;
    private String price;
    private int imageResId;
    public Destination(String name, String price, int imageResId) {
        this.name = name;
        this.price = price;
        this.imageResId = imageResId;
    }
    protected Destination(Parcel in) {
        name = in.readString();
        price = in.readString();
        imageResId = in.readInt();
    }
    public static final Creator<Destination> CREATOR = new Creator<Destination>() {
        @Override
        public Destination createFromParcel(Parcel in) {
            return new Destination(in);
        }
        @Override
        public Destination[] newArray(int size) {
            return new Destination[size];
        }
    };
    public String getName() {
        return name;
    }
    public void setName(String name) {
        this.name = name;
    }
    public String getPrice() {
        return price;
    }
    public void setPrice(String price) {
        this.price = price;
    }
    public int getImageResId() {
        return imageResId;
    }
    public void setImageResId(int imageResId) {
        this.imageResId = imageResId;
    }
    @Override
    public int describeContents() {
        return 0;
    }
    @Override
    public void writeToParcel(Parcel dest, int flags) {
        dest.writeString(name);
        dest.writeString(price);
        dest.writeInt(imageResId);
    }
}