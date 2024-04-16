<?php

namespace App\QueryFilters\Address;

use App\QueryFilters\Filter;
use Illuminate\Database\Eloquent\Builder;

class CityFilter extends Filter
{
    private const FILTER_NAME = 'city';

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
        $province = strtolower(request($filterName));

        return $builder->where('city_id', $province);
    }
}
