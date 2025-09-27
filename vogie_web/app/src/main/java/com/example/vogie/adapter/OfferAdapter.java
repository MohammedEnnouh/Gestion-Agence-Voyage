package com.example.vogie.adapter;
import android.graphics.Paint;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.ImageView;
import android.widget.TextView;
import androidx.annotation.NonNull;
import androidx.recyclerview.widget.RecyclerView;
import com.bumptech.glide.Glide;
import com.example.vogie.R;
import com.example.vogie.model.Offer;
import java.util.List;
import java.util.Locale;
public class OfferAdapter extends RecyclerView.Adapter<OfferAdapter.OfferViewHolder> {
    private final List<Offer> offers;
    private final OnOfferClickListener listener;
    public interface OnOfferClickListener {
        void onOfferClick(Offer offer);
    }
    public OfferAdapter(List<Offer> offers, OnOfferClickListener listener) {
        this.offers = offers;
        this.listener = listener;
    }
    @NonNull
    @Override
    public OfferViewHolder onCreateViewHolder(@NonNull ViewGroup parent, int viewType) {
        View view = LayoutInflater.from(parent.getContext())
                .inflate(R.layout.item_offer, parent, false);
        return new OfferViewHolder(view);
    }
    @Override
    public void onBindViewHolder(@NonNull OfferViewHolder holder, int position) {
        Offer offer = offers.get(position);

        holder.offerRoute.setText(String.format("%s → %s",
                offer.getDeparture(), offer.getDestination()));
        holder.offerDiscount.setText(String.format(Locale.getDefault(), "-%d%%", offer.getDiscount()));

        String originalPrice = String.format(Locale.getDefault(), "%.0f DH", offer.getOriginalPrice());
        String discountedPrice = String.format(Locale.getDefault(), "%.0f DH", offer.getDiscountedPrice());
        holder.originalPrice.setText(originalPrice);
        holder.originalPrice.setPaintFlags(holder.originalPrice.getPaintFlags() | Paint.STRIKE_THRU_TEXT_FLAG);
        holder.offerPrice.setText(discountedPrice);

        String description = String.format("Économisez %d%% sur ce trajet!", offer.getDiscount());
        holder.offerDescription.setText(description);

        Glide.with(holder.itemView.getContext())
                .load(R.drawable.ic_offer_placeholder)
                .placeholder(android.R.drawable.ic_menu_gallery)
                .into(holder.offerImage);

        holder.itemView.setOnClickListener(v -> listener.onOfferClick(offer));

        holder.btnBookNow.setOnClickListener(v -> listener.onOfferClick(offer));
    }
    @Override
    public int getItemCount() {
        return offers.size();
    }
    static class OfferViewHolder extends RecyclerView.ViewHolder {
        ImageView offerImage;
        TextView offerRoute;
        TextView offerDiscount;
        TextView offerPrice;
        TextView originalPrice;
        TextView offerDescription;
        TextView btnBookNow;
        public OfferViewHolder(@NonNull View itemView) {
            super(itemView);
            offerImage = itemView.findViewById(R.id.offerImage);
            offerRoute = itemView.findViewById(R.id.offerRoute);
            offerDiscount = itemView.findViewById(R.id.offerDiscount);
            offerPrice = itemView.findViewById(R.id.offerPrice);
            originalPrice = itemView.findViewById(R.id.originalPrice);
            offerDescription = itemView.findViewById(R.id.offerDescription);
            btnBookNow = itemView.findViewById(R.id.btnBookNow);
        }
    }
}