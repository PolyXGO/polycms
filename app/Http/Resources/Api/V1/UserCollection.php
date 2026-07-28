<?php

declare(strict_types=1);

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Spatie\Permission\Models\Role;

class UserCollection extends ResourceCollection
{
    public $collects = UserResource::class;

    /**
     * @return array<int|string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'data' => $this->collection,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function with(Request $request): array
    {
        $resource = $this->resource;

        return [
            'meta' => [
                'total' => method_exists($resource, 'total') ? $resource->total() : $resource->count(),
                'per_page' => method_exists($resource, 'perPage') ? $resource->perPage() : $resource->count(),
                'current_page' => method_exists($resource, 'currentPage') ? $resource->currentPage() : 1,
                'last_page' => method_exists($resource, 'lastPage') ? $resource->lastPage() : 1,
                'from' => method_exists($resource, 'firstItem') ? $resource->firstItem() : null,
                'to' => method_exists($resource, 'lastItem') ? $resource->lastItem() : null,
                'available_roles' => Role::query()->orderBy('name')->pluck('name')->values()->all(),
            ],
        ];
    }
}

