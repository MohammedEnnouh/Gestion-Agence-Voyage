package com.example.vogie.payment

import android.app.Activity
import android.content.Context
import android.util.Log
import androidx.activity.ComponentActivity
import androidx.activity.result.contract.ActivityResultContracts
import androidx.appcompat.app.AppCompatActivity
import androidx.fragment.app.Fragment
import com.stripe.android.*
import com.stripe.android.model.*
import com.stripe.android.payments.paymentlauncher.*
import com.stripe.android.view.CardInputWidget
import kotlinx.coroutines.CoroutineScope
import kotlinx.coroutines.Dispatchers
import kotlinx.coroutines.launch

// Import the required Stripe classes
import com.stripe.android.model.ConfirmPaymentIntentParams
import com.stripe.android.model.PaymentIntent
import com.stripe.android.model.PaymentMethodCreateParams
import com.stripe.android.payments.paymentlauncher.PaymentLauncher
import com.stripe.android.payments.paymentlauncher.PaymentResult
import com.stripe.android.payments.paymentlauncher.rememberPaymentLauncher

class StripePaymentHelper(
    private val context: Context,
    private val publishableKey: String,
    private val backendUrl: String,
    private val onPaymentSuccess: () -> Unit,
    private val onPaymentError: (String) -> Unit
) {
    private lateinit var paymentLauncher: PaymentLauncher
    private val httpClient = HttpClient()
    private val tag = "StripePaymentHelper"

    init {
        initializePaymentLauncher()
    }

    private fun initializePaymentLauncher() {
        paymentLauncher = when (val context = this.context) {
            is ComponentActivity -> {
                PaymentLauncher.create(
                    activity = context,
                    publishableKey = publishableKey,
                    stripeAccountId = null,
                    callback = ::onPaymentResult
                )
            }
            is AppCompatActivity -> {
                PaymentLauncher.create(
                    activity = context,
                    publishableKey = publishableKey,
                    stripeAccountId = null,
                    callback = ::onPaymentResult
                )
            }
            else -> throw IllegalStateException("Context must be a ComponentActivity or AppCompatActivity")
        }
    }

    fun processPayment(amount: Long, currency: String, description: String) {
        // In a production app, you should call your backend to create a PaymentIntent
        // and then confirm it on the client side
        
        // For demo purposes, we'll create a test PaymentIntent
        createPaymentIntent(amount, currency, description)
    }

    private fun createPaymentIntent(amount: Long, currency: String, description: String) {
        // In a real app, you would call your backend to create a PaymentIntent
        // and then confirm it on the client side
        
        // For demo purposes, we'll create a test PaymentIntent
        // In a real app, you would call your backend to create a PaymentIntent
        // For demo purposes, we'll use a test client secret
        val cardParams = PaymentMethodCreateParams.Card.Builder()
            .setNumber("4242424242424242")
            .setExpiryMonth(12)
            .setExpiryYear(2030)
            .setCvc("123")
            .build()

        val paymentMethodParams = PaymentMethodCreateParams.create(cardParams)
        
        val confirmParams = ConfirmPaymentIntentParams.createWithPaymentMethodCreateParams(
            paymentMethodCreateParams = paymentMethodParams,
            clientSecret = "pi_3P1JXg2eZvKYlo2C0l4Xr2X0_secret_XXXXXXXXXXXXXXXXXXXXXXXX"
        )
        
        // In a real app, you would call your backend to create the PaymentIntent
        // For demo, we'll just proceed with a test client secret
        confirmPayment("pi_3P1JXg2eZvKYlo2C0l4Xr2X0_secret_XXXXXXXXXXXXXXXXXXXXXXXX")
    }

    private fun confirmPayment(clientSecret: String) {
        try {
            // Create a payment method with card details
            // In a real app, you would collect these from the user
            val cardParams = PaymentMethodCreateParams.Card.Builder()
                .setNumber("4242424242424242")
                .setExpiryMonth(12)
                .setExpiryYear(2030)
                .setCvc("123")
                .build()

            val paymentMethodParams = PaymentMethodCreateParams.create(cardParams)
            
            val confirmParams = ConfirmPaymentIntentParams.createWithPaymentMethodCreateParams(
                paymentMethodCreateParams = paymentMethodParams,
                clientSecret = clientSecret,
                setupFutureUsage = ConfirmPaymentIntentParams.SetupFutureUsage.OffSession
            )
            
            paymentLauncher.confirm(confirmParams)
        } catch (e: Exception) {
            Log.e(tag, "Error confirming payment", e)
            onPaymentError("Failed to process payment: ${e.message}")
        }
    }

    private fun onPaymentResult(paymentResult: PaymentResult) {
        when (paymentResult) {
            is PaymentResult.Completed -> {
                // Payment succeeded
                onPaymentSuccess()
            }
            is PaymentResult.Canceled -> {
                onPaymentError("Payment was canceled")
            }
            is PaymentResult.Failed -> {
                onPaymentError("Payment failed: ${paymentResult.throwable.message}")
            }
        }
    }

    private fun handlePaymentSuccess(paymentIntent: PaymentIntent) {
        // Handle successful payment
        Log.d(tag, "Payment successful: ${paymentIntent.id}")
        onPaymentSuccess()
    }

    companion object {
        // Test Publishable Key - Replace with your actual publishable key from Stripe Dashboard
        const val TEST_PUBLISHABLE_KEY = "pk_test_51Pe5DrDW9NzzBWTtvTKSAGaCxmzOb1us4eve170c9zYIcknAh3I4HcQhnUhfyLk8kd1ML8u4qmOzX8Pzi0WHOfvn00ujylHqyb"
        
        // Backend URL - This should be your server endpoint that creates PaymentIntents
        const val BACKEND_URL = "https://your-backend-url.com"
    }
}

// Simple HTTP client for making network requests
class HttpClient {
    // In a real app, you would use Retrofit or another HTTP client
    // This is a simplified version for demonstration
    fun post(url: String, body: Map<String, Any>, callback: (String) -> Unit) {
        // Implement your HTTP POST request logic here
        // For demo purposes, we'll just call the callback with a mock response
        callback("""{"clientSecret": "pi_3P1JXg2eZvKYlo2C0l4Xr2X0_secret_XXXXXXXXXXXXXXXXXXXXXXXX"}""")
    }
}