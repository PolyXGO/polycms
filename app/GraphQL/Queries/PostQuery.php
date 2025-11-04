<?php

declare(strict_types=1);

namespace App\GraphQL\Queries;

use Illuminate\Database\Eloquent\Builder;

class PostQuery
{
    /**
     * Search posts by title or excerpt
     */
    public function search(Builder $builder, array $args): Builder
    {
        if (isset($args['search']) && !empty($args['search'])) {
            $search = $args['search'];
            $builder->where(function ($query) use ($search) {
                $query->where('title', 'like', "%{$search}%")
                    ->orWhere('excerpt', 'like', "%{$search}%");
            });
        }

        return $builder;
    }
}
