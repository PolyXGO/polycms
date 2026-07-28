<?php

declare(strict_types=1);

namespace App\GraphQL\Queries;

use Illuminate\Database\Eloquent\Builder;

class CategoryQuery
{
    /**
     * Filter only root categories (no parent)
     */
    public function rootOnly(Builder $builder, array $args): Builder
    {
        if (isset($args['root_only']) && $args['root_only'] === true) {
            $builder->whereNull('parent_id');
        }

        return $builder;
    }
}
