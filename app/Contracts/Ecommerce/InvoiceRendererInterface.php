<?php

namespace App\Contracts\Ecommerce;

use App\Models\Ecommerce\Order;

interface InvoiceRendererInterface
{
    /**
     * Render the invoice as HTML or PDF content.
     *
     * @param Order $order
     * @param string $template
     * @return string
     */
    public function render(Order $order, $template = 'default');

    /**
     * Get list of available templates this renderer supports.
     *
     * @return array ['template_code' => 'Template Label']
     */
    public function getAvailableTemplates();
}
