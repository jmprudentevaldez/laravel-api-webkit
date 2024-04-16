<?php

namespace App\QueryFilters\Address;

use App\QueryFilters\Filter;
use Illuminate\Database\Eloquent\Builder;

class RegionFilter extends Filter
{
    private const FILTER_NAME = 'region';

    protected function getFilterName(): string
    {
        return static::FILTER_NAME;
    }

    protected function applyFilter(Builder $builder): Builder
    {
        $filterName = $this->getFilterName();
        $region = strtolower(request($filterName));

        return $builder->where('region_id', $region);
    }
}
