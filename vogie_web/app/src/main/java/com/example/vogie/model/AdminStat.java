package com.example.vogie.model;
public class AdminStat {
    private String title;
    private String value;
    private int iconRes;
    private String color;
    public AdminStat(String title, String value, int iconRes, String color) {
        this.title = title;
        this.value = value;
        this.iconRes = iconRes;
        this.color = color;
    }
    public String getTitle() {
        return title;
    }
    public void setTitle(String title) {
        this.title = title;
    }
    public String getValue() {
        return value;
    }
    public void setValue(String value) {
        this.value = value;
    }
    public int getIconRes() {
        return iconRes;
    }
    public void setIconRes(int iconRes) {
        this.iconRes = iconRes;
    }
    public String getColor() {
        return color;
    }
    public void setColor(String color) {
        this.color = color;
    }
}