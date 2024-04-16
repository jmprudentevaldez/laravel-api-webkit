<?php

namespace App\QueryFilters\Address;

use App\QueryFilters\Filter;
use Illuminate\Database\Eloquent\Builder;

class ClassificationFilter extends Filter
{
    private const FILTER_NAME = 'classification';

    /**
     * {@inheritDoc}
     */
    protected function getFilterName(): string
    {
        return static::FILTER_NAME;
    }

    /**
     * {@inheritDoc}
     */
    protected function applyFilter(Builder $builder): Builder
    {
        $filterName = $this->getFilterName();
        $type = strtolower(request($filterName));

        return $builder->where('classification', $type);
    }
}
