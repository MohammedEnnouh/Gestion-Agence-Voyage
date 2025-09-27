package com.example.vogie2;

public final class NetworkConfig {
    private NetworkConfig() {}

    public static void setRealDeviceIP(String ipv4) {
        if (ipv4 == null || ipv4.isEmpty()) return;
        String url = "http://" + ipv4 + "/Vogie3/public/api/";
        ApiClient.setBaseUrl(url);
    }
}