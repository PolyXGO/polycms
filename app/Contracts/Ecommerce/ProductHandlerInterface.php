<?php

namespace App\Contracts\Ecommerce;

use App\Models\Product;

interface ProductHandlerInterface
{
    /**
     * Render the configuration form for this product type in Admin.
     *
     * @param Product $product
     * @return string HTML content
     */
    public function renderConfigForm(Product $product);

    /**
     * Save the configuration data from the request.
     *
     * @param Product $product
     * @param array $data
     * @return void
     */
    public function saveConfig(Product $product, array $data);

    /**
     * Hook triggered when an order item of this product is completed/paid.
     * Use this to grant access, generate stuff, etc.
     *
     * @param mixed $orderItem (OrderItem model)
     * @return void
     */
    public function onOrderCompleted($orderItem);
    
    /**
     * Hook triggered when an order item is cancelled/refunded.
     *
     * @param mixed $orderItem
     * @return void
     */
    public function onOrderCancelled($orderItem);
}
