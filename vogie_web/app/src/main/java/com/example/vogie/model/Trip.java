package com.example.vogie.model;
import android.os.Parcel;
import android.os.Parcelable;
import com.google.gson.annotations.SerializedName;
public class Trip implements Parcelable {
    @SerializedName("time")
    private String time;
    @SerializedName("price_per_person")
    private double pricePerPerson;
    @SerializedName("total")
    private double total;
    private int routeId;
    public Trip() {}
    public Trip(String time, double pricePerPerson, double total, int routeId) {
        this.time = time;
        this.pricePerPerson = pricePerPerson;
        this.total = total;
        this.routeId = routeId;
    }
    protected Trip(Parcel in) {
        time = in.readString();
        pricePerPerson = in.readDouble();
        total = in.readDouble();
        routeId = in.readInt();
    }
    public static final Creator<Trip> CREATOR = new Creator<Trip>() {
        @Override
        public Trip createFromParcel(Parcel in) {
            return new Trip(in);
        }
        @Override
        public Trip[] newArray(int size) {
            return new Trip[size];
        }
    };
    public String getTime() {
        return time;
    }
    public void setTime(String time) {
        this.time = time;
    }
    public double getPricePerPerson() {
        return pricePerPerson;
    }
    public void setPricePerPerson(double pricePerPerson) {
        this.pricePerPerson = pricePerPerson;
    }
    public double getTotal() {
        return total;
    }
    public void setTotal(double total) {
        this.total = total;
    }
    public int getRouteId() {
        return routeId;
    }
    public void setRouteId(int routeId) {
        this.routeId = routeId;
    }
    @Override
    public int describeContents() {
        return 0;
    }
    @Override
    public void writeToParcel(Parcel dest, int flags) {
        dest.writeString(time);
        dest.writeDouble(pricePerPerson);
        dest.writeDouble(total);
        dest.writeInt(routeId);
    }
}