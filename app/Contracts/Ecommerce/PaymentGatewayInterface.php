<?php

namespace App\Contracts\Ecommerce;

interface PaymentGatewayInterface
{
    /**
     * Get the unique code of the gateway (e.g., 'momo', 'stripe').
     *
     * @return string
     */
    public function getCode();

    /**
     * Render the payment button or form for the checkout page.
     *
     * @param mixed $order (Order model)
     * @return string HTML
     */
    public function getFormHtml($order);

    /**
     * Process the payment request (redirect or direct charge).
     *
     * @param mixed $request
     * @return mixed Response
     */
    public function processPayment($request);

    /**
     * Verify the webhook/IPN callback to confirm payment.
     *
     * @param mixed $request
     * @return bool|array Returns transaction data if success, false if failed
     */
    public function verifyWebhook($request);

    /**
     * Process a refund for a transaction.
     *
     * @param string $transactionRef
     * @param float $amount
     * @param string $reason
     * @return bool
     */
    public function refund($transactionRef, $amount, $reason = '');
}
