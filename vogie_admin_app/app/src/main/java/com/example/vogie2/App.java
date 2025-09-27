package com.example.vogie2;
import android.app.Application;
import android.os.Looper;
import android.util.Log;
import android.widget.Toast;
import java.io.File;
import java.io.FileOutputStream;
import java.io.PrintWriter;
import java.io.StringWriter;
public class App extends Application {
    @Override
    public void onCreate() {
        super.onCreate();
        Thread.setDefaultUncaughtExceptionHandler((t, e) -> {
            try {

                StringWriter sw = new StringWriter();
                e.printStackTrace(new PrintWriter(sw));
                String stack = sw.toString();
                File out = new File(getFilesDir(), "crash.txt");
                try (FileOutputStream fos = new FileOutputStream(out, false)) {
                    String meta = "Thread: " + t.getName() + "\n" +
                            "Version: " + BuildConfig.VERSION_NAME + " (" + BuildConfig.VERSION_CODE + ")\n" +
                            "BaseURL: " + ApiClient.getBaseUrl() + "\n\n";
                    fos.write((meta + stack).getBytes());
                }
                Log.e("AppCrash", "Unhandled exception, saved to: " + out.getAbsolutePath());

                if (Looper.myLooper() != Looper.getMainLooper()) {
                    new android.os.Handler(Looper.getMainLooper()).post(() ->
                            Toast.makeText(getApplicationContext(), "An error occurred. Log saved to crash.txt", Toast.LENGTH_LONG).show());
                } else {
                    Toast.makeText(getApplicationContext(), "An error occurred. Log saved to crash.txt", Toast.LENGTH_LONG).show();
                }
            } catch (Throwable ignore) {
            } finally {

                android.os.Process.killProcess(android.os.Process.myPid());
                System.exit(10);
            }
        });
    }
}