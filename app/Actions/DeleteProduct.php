<?php

declare(strict_types=1);

namespace App\Actions;

use App\Facades\Hook;
use App\Models\Product;

class DeleteProduct
{
    /**
     * Delete a product
     */
    public function execute(Product $product): bool
    {
        // Fire action hook before deletion
        Hook::doAction('product.deleting', $product);

        $deleted = $product->delete();

        if ($deleted) {
            // Fire action hook after deletion
            Hook::doAction('product.deleted', $product);
        }

        return $deleted;
    }
}
