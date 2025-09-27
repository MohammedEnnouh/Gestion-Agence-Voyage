package com.example.vogie2;
import android.util.Log;
import java.net.CookieManager;
import java.net.CookiePolicy;
import java.util.ArrayList;
import java.util.HashMap;
import java.util.List;
import java.util.Map;
import okhttp3.Cookie;
import okhttp3.CookieJar;
import okhttp3.HttpUrl;
import okhttp3.OkHttpClient;
import okhttp3.logging.HttpLoggingInterceptor;
import retrofit2.Retrofit;
import retrofit2.converter.gson.GsonConverterFactory;
public class ApiClient {
    private static volatile String baseUrl = BuildConfig.API_BASE_URL;
    private static Retrofit retrofit;
    private static final CookieJar COOKIE_JAR = new CookieJar() {
        private final Map<String, List<Cookie>> store = new HashMap<>();
        @Override
        public void saveFromResponse(HttpUrl url, List<Cookie> cookies) {
            if (cookies == null || cookies.isEmpty()) return;
            store.put(url.host(), new ArrayList<>(cookies));
        }
        @Override
        public List<Cookie> loadForRequest(HttpUrl url) {
            List<Cookie> cookies = store.get(url.host());
            return cookies != null ? new ArrayList<>(cookies) : new ArrayList<>();
        }
    };
    public static synchronized void setBaseUrl(String newBaseUrl) {
        if (newBaseUrl == null || newBaseUrl.isEmpty()) return;
        if (!newBaseUrl.endsWith("/")) {
            newBaseUrl = newBaseUrl + "/";
        }
        if (!newBaseUrl.equals(baseUrl)) {
            baseUrl = newBaseUrl;
            retrofit = null;
        }
    }
    public static String getBaseUrl() {
        return baseUrl;
    }
    public static Retrofit get() {
        if (retrofit == null) {
            HttpLoggingInterceptor logging = new HttpLoggingInterceptor();
            logging.setLevel(HttpLoggingInterceptor.Level.BODY);
            OkHttpClient client = new OkHttpClient.Builder()
                    .cookieJar(COOKIE_JAR)
                    .addInterceptor(logging)
                    .connectTimeout(java.time.Duration.ofSeconds(5))
                    .readTimeout(java.time.Duration.ofSeconds(5))
                    .writeTimeout(java.time.Duration.ofSeconds(5))
                    .callTimeout(java.time.Duration.ofSeconds(8))
                    .build();
            retrofit = new Retrofit.Builder()
                    .baseUrl(baseUrl)
                    .addConverterFactory(GsonConverterFactory.create())
                    .client(client)
                    .build();
        }
        return retrofit;
    }
}