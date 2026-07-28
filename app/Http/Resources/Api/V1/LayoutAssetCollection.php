<?php

declare(strict_types=1);

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class LayoutAssetCollection extends ResourceCollection
{
    public function toArray(Request $request): array
    {
        return [
            'data' => $this->collection,
        ];
    }

    public function with(Request $request): array
    {
        return [
            'meta' => [
                'total' => $this->resource->total() ?? $this->resource->count(),
                'per_page' => $this->resource->perPage() ?? 15,
                'current_page' => $this->resource->currentPage() ?? 1,
                'last_page' => $this->resource->lastPage() ?? 1,
            ],
        ];
    }
}
