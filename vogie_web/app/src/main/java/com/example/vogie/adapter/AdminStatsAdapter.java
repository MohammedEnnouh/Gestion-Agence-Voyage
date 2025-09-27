package com.example.vogie.adapter;
import android.graphics.Color;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.ImageView;
import android.widget.TextView;
import androidx.annotation.NonNull;
import androidx.recyclerview.widget.RecyclerView;
import com.example.vogie.R;
import com.example.vogie.model.AdminStat;
import java.util.List;
public class AdminStatsAdapter extends RecyclerView.Adapter<AdminStatsAdapter.StatViewHolder> {
    private List<AdminStat> statsList;
    public AdminStatsAdapter(List<AdminStat> statsList) {
        this.statsList = statsList;
    }
    @NonNull
    @Override
    public StatViewHolder onCreateViewHolder(@NonNull ViewGroup parent, int viewType) {
        View view = LayoutInflater.from(parent.getContext()).inflate(R.layout.item_admin_stat, parent, false);
        return new StatViewHolder(view);
    }
    @Override
    public void onBindViewHolder(@NonNull StatViewHolder holder, int position) {
        AdminStat stat = statsList.get(position);
        holder.tvTitle.setText(stat.getTitle());
        holder.tvValue.setText(stat.getValue());
        holder.ivIcon.setImageResource(stat.getIconRes());

        try {
            int color = Color.parseColor(stat.getColor());
            holder.ivIcon.setColorFilter(color);
        } catch (IllegalArgumentException e) {

            holder.ivIcon.setColorFilter(Color.parseColor("#2196F3"));
        }
    }
    @Override
    public int getItemCount() {
        return statsList.size();
    }
    static class StatViewHolder extends RecyclerView.ViewHolder {
        TextView tvTitle, tvValue;
        ImageView ivIcon;
        public StatViewHolder(@NonNull View itemView) {
            super(itemView);
            tvTitle = itemView.findViewById(R.id.tv_stat_title);
            tvValue = itemView.findViewById(R.id.tv_stat_value);
            ivIcon = itemView.findViewById(R.id.iv_stat_icon);
        }
    }
}