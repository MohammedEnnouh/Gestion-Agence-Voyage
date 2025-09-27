package com.example.vogie.model;
import android.os.Parcel;
import android.os.Parcelable;
public class Route implements Parcelable {
    private int id;
    private int fromCityId;
    private int toCityId;
    private double pricePerPerson;
    private String fromCityName;
    private String toCityName;
    public Route(int id, int fromCityId, int toCityId, double pricePerPerson, String fromCityName, String toCityName) {
        this.id = id;
        this.fromCityId = fromCityId;
        this.toCityId = toCityId;
        this.pricePerPerson = pricePerPerson;
        this.fromCityName = fromCityName;
        this.toCityName = toCityName;
    }
    protected Route(Parcel in) {
        id = in.readInt();
        fromCityId = in.readInt();
        toCityId = in.readInt();
        pricePerPerson = in.readDouble();
        fromCityName = in.readString();
        toCityName = in.readString();
    }
    public static final Creator<Route> CREATOR = new Creator<Route>() {
        @Override
        public Route createFromParcel(Parcel in) {
            return new Route(in);
        }
        @Override
        public Route[] newArray(int size) {
            return new Route[size];
        }
    };
    public int getId() {
        return id;
    }
    public int getFromCityId() {
        return fromCityId;
    }
    public int getToCityId() {
        return toCityId;
    }
    public double getPricePerPerson() {
        return pricePerPerson;
    }
    public String getFromCityName() {
        return fromCityName;
    }
    public String getToCityName() {
        return toCityName;
    }
    @Override
    public int describeContents() {
        return 0;
    }
    @Override
    public void writeToParcel(Parcel dest, int flags) {
        dest.writeInt(id);
        dest.writeInt(fromCityId);
        dest.writeInt(toCityId);
        dest.writeDouble(pricePerPerson);
        dest.writeString(fromCityName);
        dest.writeString(toCityName);
    }
}