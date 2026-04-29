<?php

declare(strict_types=1);

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class RoleCollection extends ResourceCollection
{
    public $collects = RoleResource::class;

    public function toArray(Request $request): array
    {
        return [
            'data' => $this->collection,
        ];
    }

    public function with(Request $request): array
    {
        $resource = $this->resource;

        return [
            'meta' => [
                'total' => method_exists($resource, 'total') ? $resource->total() : $resource->count(),
                'per_page' => method_exists($resource, 'perPage') ? $resource->perPage() : $resource->count(),
                'current_page' => method_exists($resource, 'currentPage') ? $resource->currentPage() : 1,
                'last_page' => method_exists($resource, 'lastPage') ? $resource->lastPage() : 1,
            ],
        ];
    }
}

