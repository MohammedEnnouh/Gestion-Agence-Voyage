package com.example.vogie.adapter;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.TextView;
import androidx.annotation.NonNull;
import androidx.recyclerview.widget.RecyclerView;
import com.example.vogie.R;
import com.example.vogie.model.Booking;
import java.util.List;
public class BookingAdapter extends RecyclerView.Adapter<BookingAdapter.BookingViewHolder> {
    public interface OnBookingClickListener {
        void onBookingClick(Booking booking);
    }
    private List<Booking> bookings;
    private OnBookingClickListener listener;
    public BookingAdapter(List<Booking> bookings, OnBookingClickListener listener) {
        this.bookings = bookings;
        this.listener = listener;
    }
    @NonNull
    @Override
    public BookingViewHolder onCreateViewHolder(@NonNull ViewGroup parent, int viewType) {
        View view = LayoutInflater.from(parent.getContext()).inflate(R.layout.item_booking, parent, false);
        return new BookingViewHolder(view);
    }
    @Override
    public void onBindViewHolder(@NonNull BookingViewHolder holder, int position) {
        Booking booking = bookings.get(position);
        holder.bind(booking, listener);
    }
    @Override
    public int getItemCount() {
        return bookings.size();
    }
    static class BookingViewHolder extends RecyclerView.ViewHolder {
        private TextView destinationText;
        private TextView dateText;
        private TextView statusText;
        public BookingViewHolder(@NonNull View itemView) {
            super(itemView);
            destinationText = itemView.findViewById(R.id.bookingDestination);
            dateText = itemView.findViewById(R.id.bookingDate);
            statusText = itemView.findViewById(R.id.bookingStatus);
        }
        public void bind(Booking booking, OnBookingClickListener listener) {
            destinationText.setText(booking.getDestinationName());
            dateText.setText(booking.getBookingDate());
            statusText.setText(booking.getStatus());
            itemView.setOnClickListener(v -> {
                if (listener != null) {
                    listener.onBookingClick(booking);
                }
            });
        }
    }
}