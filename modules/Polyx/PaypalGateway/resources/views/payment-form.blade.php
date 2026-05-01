<div id="paypal-button-container-{{ $order->id }}" class="paypal-button-container"></div>

<script src="https://www.paypal.com/sdk/js?client-id={{ $clientId }}&currency={{ $currency }}&intent=capture"></script>

<script>
(function() {
    const orderId = {{ $order->id }};
    const orderCode = '{{ $order->code }}';
    
    paypal.Buttons({
        style: {
            color: '{{ $buttonColor }}',
            shape: '{{ $buttonShape }}',
            label: '{{ $buttonLabel }}',
            height: {{ $buttonHeight }}
        },
        
        // Create PayPal order
        createOrder: async function() {
            try {
                const response = await fetch('/api/v1/payment/paypal/create-order', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                    },
                    body: JSON.stringify({
                        order_id: orderId
                    })
                });
                
                const data = await response.json();
                
                if (!data.success) {
                    throw new Error(data.error || 'Failed to create PayPal order');
                }
                
                return data.paypal_order_id;
            } catch (error) {
                console.error('PayPal createOrder error:', error);
                alert('Failed to initialize payment. Please try again.');
                throw error;
            }
        },
        
        // Capture approved payment
        onApprove: async function(data) {
            try {
                const response = await fetch('/api/v1/payment/paypal/capture', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                    },
                    body: JSON.stringify({
                        paypal_order_id: data.orderID,
                        order_id: orderId
                    })
                });
                
                const result = await response.json();
                
                if (result.success && result.redirect_url) {
                    window.location.href = result.redirect_url;
                } else {
                    throw new Error(result.error || 'Payment capture failed');
                }
            } catch (error) {
                console.error('PayPal onApprove error:', error);
                alert('Payment failed. Please try again or contact support.');
            }
        },
        
        // Handle cancellation
        onCancel: function(data) {
            console.log('Payment cancelled by user');
        },
        
        // Handle errors
        onError: function(err) {
            console.error('PayPal error:', err);
            alert('A payment error occurred. Please try again.');
        }
    }).render('#paypal-button-container-{{ $order->id }}');
})();
</script>

<style>
.paypal-button-container {
    min-height: 55px;
    margin: 1rem 0;
}
</style>
