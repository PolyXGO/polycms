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

    public function filterByCategory(Builder $builder, array $args): Builder
    {
        if (!empty($args['category_id'])) {
            $builder->whereHas('categories', function (Builder $query) use ($args) {
                $query->where('categories.id', $args['category_id']);
            });
        }

        return $builder;
    }
}
