<?php

declare(strict_types=1);

namespace App\Exceptions\Ecommerce;

use RuntimeException;

class InsufficientStockException extends RuntimeException
{
    protected int $availableQuantity;

    public function __construct(string $message = 'Insufficient stock', int $availableQuantity = 0, int $code = 0, ?\Throwable $previous = null)
    {
        $this->availableQuantity = $availableQuantity;
        parent::__construct($message, $code, $previous);
    }

    public function getAvailableQuantity(): int
    {
        return $this->availableQuantity;
    }
}
