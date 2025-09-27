package com.example.vogie.model;
public class QuickAction {
    private String title;
    private String description;
    private int iconRes;
    private String color;
    public QuickAction(String title, String description, int iconRes, String color) {
        this.title = title;
        this.description = description;
        this.iconRes = iconRes;
        this.color = color;
    }
    public String getTitle() {
        return title;
    }
    public void setTitle(String title) {
        this.title = title;
    }
    public String getDescription() {
        return description;
    }
    public void setDescription(String description) {
        this.description = description;
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