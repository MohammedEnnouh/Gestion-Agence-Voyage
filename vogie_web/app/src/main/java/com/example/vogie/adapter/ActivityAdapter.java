package com.example.vogie.adapter;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.ImageView;
import android.widget.TextView;
import androidx.annotation.NonNull;
import androidx.recyclerview.widget.RecyclerView;
import com.example.vogie.R;
import com.example.vogie.api.ApiService;
import java.util.List;
public class ActivityAdapter extends RecyclerView.Adapter<ActivityAdapter.ActivityViewHolder> {
    private List<ApiService.DashboardData.Activity> activities;
    public ActivityAdapter(List<ApiService.DashboardData.Activity> activities) {
        this.activities = activities;
    }
    @NonNull
    @Override
    public ActivityViewHolder onCreateViewHolder(@NonNull ViewGroup parent, int viewType) {
        View view = LayoutInflater.from(parent.getContext()).inflate(R.layout.item_activity, parent, false);
        return new ActivityViewHolder(view);
    }
    @Override
    public void onBindViewHolder(@NonNull ActivityViewHolder holder, int position) {
        ApiService.DashboardData.Activity activity = activities.get(position);
        holder.bind(activity);
    }
    @Override
    public int getItemCount() {
        return activities.size();
    }
    static class ActivityViewHolder extends RecyclerView.ViewHolder {
        private ImageView iconView;
        private TextView titleText;
        private TextView messageText;
        private TextView timeText;
        public ActivityViewHolder(@NonNull View itemView) {
            super(itemView);
            iconView = itemView.findViewById(R.id.activityIcon);
            titleText = itemView.findViewById(R.id.activityTitle);
            messageText = itemView.findViewById(R.id.activityMessage);
            timeText = itemView.findViewById(R.id.activityTime);
        }
        public void bind(ApiService.DashboardData.Activity activity) {
            titleText.setText(activity.title);
            messageText.setText(activity.message);
            timeText.setText(activity.time_ago);

            if ("booking".equals(activity.type)) {
                iconView.setImageResource(R.drawable.ic_booking);
            } else if ("review".equals(activity.type)) {
                iconView.setImageResource(R.drawable.ic_star);
            } else {
                iconView.setImageResource(R.drawable.ic_info);
            }
        }
    }
}