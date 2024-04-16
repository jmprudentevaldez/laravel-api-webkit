<?php

namespace App\QueryFilters\Address;

use App\QueryFilters\Filter;
use Illuminate\Database\Eloquent\Builder;

class ProvinceFilter extends Filter
{
    private const FILTER_NAME = 'province';

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

        return $builder->where('province_id', $province);
    }
}
