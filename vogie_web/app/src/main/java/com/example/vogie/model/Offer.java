package com.example.vogie.model;
public class Offer {
    private String departure;
    private String destination;
    private double originalPrice;
    private int discount;
    private String description;
    private String code;
    public Offer(String departure, String destination, double originalPrice, int discount, String description) {
        this.departure = departure;
        this.destination = destination;
        this.originalPrice = originalPrice;
        this.discount = discount;
        this.description = description;
        this.code = generatePromoCode();
    }
    public String getDeparture() {
        return departure;
    }
    public String getDestination() {
        return destination;
    }
    public double getOriginalPrice() {
        return originalPrice;
    }
    public double getDiscountedPrice() {
        return originalPrice * (1 - discount / 100.0);
    }
    public int getDiscount() {
        return discount;
    }
    public String getDescription() {
        return description;
    }
    public String getCode() {
        return code;
    }
    private String generatePromoCode() {
        return "VOGIE" + (int)(Math.random() * 10000);
    }
}