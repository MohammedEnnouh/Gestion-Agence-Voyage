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
import com.example.vogie.model.QuickAction;
import java.util.List;
public class QuickActionAdapter extends RecyclerView.Adapter<QuickActionAdapter.QuickActionViewHolder> {
    private List<QuickAction> actionsList;
    private OnQuickActionClickListener listener;
    public interface OnQuickActionClickListener {
        void onQuickActionClick(QuickAction action);
    }
    public QuickActionAdapter(List<QuickAction> actionsList, OnQuickActionClickListener listener) {
        this.actionsList = actionsList;
        this.listener = listener;
    }
    @NonNull
    @Override
    public QuickActionViewHolder onCreateViewHolder(@NonNull ViewGroup parent, int viewType) {
        View view = LayoutInflater.from(parent.getContext()).inflate(R.layout.item_quick_action, parent, false);
        return new QuickActionViewHolder(view);
    }
    @Override
    public void onBindViewHolder(@NonNull QuickActionViewHolder holder, int position) {
        QuickAction action = actionsList.get(position);
        holder.tvTitle.setText(action.getTitle());
        holder.tvDescription.setText(action.getDescription());
        holder.ivIcon.setImageResource(action.getIconRes());

        try {
            int color = Color.parseColor(action.getColor());
            holder.ivIcon.setColorFilter(color);
        } catch (IllegalArgumentException e) {

            holder.ivIcon.setColorFilter(Color.parseColor("#2196F3"));
        }
        holder.itemView.setOnClickListener(v -> {
            if (listener != null) {
                listener.onQuickActionClick(action);
            }
        });
    }
    @Override
    public int getItemCount() {
        return actionsList.size();
    }
    static class QuickActionViewHolder extends RecyclerView.ViewHolder {
        TextView tvTitle, tvDescription;
        ImageView ivIcon;
        public QuickActionViewHolder(@NonNull View itemView) {
            super(itemView);
            tvTitle = itemView.findViewById(R.id.tv_action_title);
            tvDescription = itemView.findViewById(R.id.tv_action_description);
            ivIcon = itemView.findViewById(R.id.iv_action_icon);
        }
    }
}