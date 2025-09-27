package com.example.vogie.model;
import android.os.Parcel;
import android.os.Parcelable;
public class Booking implements Parcelable {
    private int id;
    private int userId;
    private String destination;
    private int fromCityId;
    private int toCityId;
    private String departureDate;
    private String departureTime;
    private int passengerCount;
    private String paymentMethod;
    private String paymentStatus;
    private String stripePaymentId;
    private String qrCodePath;
    private String bookingReference;
    private String bookingDate;
    private double totalAmount;
    private String fromCityName;
    private String toCityName;
    public Booking(int id, int userId, String destination, int fromCityId, int toCityId,
                   String departureDate, String departureTime, int passengerCount,
                   String paymentMethod, String paymentStatus, String stripePaymentId,
                   String qrCodePath, String bookingReference, String bookingDate,
                   double totalAmount, String fromCityName, String toCityName) {
        this.id = id;
        this.userId = userId;
        this.destination = destination;
        this.fromCityId = fromCityId;
        this.toCityId = toCityId;
        this.departureDate = departureDate;
        this.departureTime = departureTime;
        this.passengerCount = passengerCount;
        this.paymentMethod = paymentMethod;
        this.paymentStatus = paymentStatus;
        this.stripePaymentId = stripePaymentId;
        this.qrCodePath = qrCodePath;
        this.bookingReference = bookingReference;
        this.bookingDate = bookingDate;
        this.totalAmount = totalAmount;
        this.fromCityName = fromCityName;
        this.toCityName = toCityName;
    }
    protected Booking(Parcel in) {
        id = in.readInt();
        userId = in.readInt();
        destination = in.readString();
        fromCityId = in.readInt();
        toCityId = in.readInt();
        departureDate = in.readString();
        departureTime = in.readString();
        passengerCount = in.readInt();
        paymentMethod = in.readString();
        paymentStatus = in.readString();
        stripePaymentId = in.readString();
        qrCodePath = in.readString();
        bookingReference = in.readString();
        bookingDate = in.readString();
        totalAmount = in.readDouble();
        fromCityName = in.readString();
        toCityName = in.readString();
    }
    public static final Creator<Booking> CREATOR = new Creator<Booking>() {
        @Override
        public Booking createFromParcel(Parcel in) {
            return new Booking(in);
        }
        @Override
        public Booking[] newArray(int size) {
            return new Booking[size];
        }
    };
    public int getId() {
        return id;
    }
    public int getUserId() {
        return userId;
    }
    public String getDestination() {
        return destination;
    }
    public int getFromCityId() {
        return fromCityId;
    }
    public int getToCityId() {
        return toCityId;
    }
    public String getDepartureDate() {
        return departureDate;
    }
    public String getDepartureTime() {
        return departureTime;
    }
    public int getPassengerCount() {
        return passengerCount;
    }
    public String getPaymentMethod() {
        return paymentMethod;
    }
    public String getPaymentStatus() {
        return paymentStatus;
    }
    public String getStripePaymentId() {
        return stripePaymentId;
    }
    public String getQrCodePath() {
        return qrCodePath;
    }
    public String getBookingReference() {
        return bookingReference;
    }
    public String getBookingDate() {
        return bookingDate;
    }
    public double getTotalAmount() {
        return totalAmount;
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
        dest.writeInt(userId);
        dest.writeString(destination);
        dest.writeInt(fromCityId);
        dest.writeInt(toCityId);
        dest.writeString(departureDate);
        dest.writeString(departureTime);
        dest.writeInt(passengerCount);
        dest.writeString(paymentMethod);
        dest.writeString(paymentStatus);
        dest.writeString(stripePaymentId);
        dest.writeString(qrCodePath);
        dest.writeString(bookingReference);
        dest.writeString(bookingDate);
        dest.writeDouble(totalAmount);
        dest.writeString(fromCityName);
        dest.writeString(toCityName);
    }
}