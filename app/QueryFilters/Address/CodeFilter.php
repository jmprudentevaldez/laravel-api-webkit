<?php

namespace App\QueryFilters\Address;

use App\QueryFilters\Filter;
use Illuminate\Database\Eloquent\Builder;

class CodeFilter extends Filter
{
    private const FILTER_NAME = 'code';

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
        $code = strtolower(request($filterName));

        return $builder->where('code_correspondence', $code);
    }
}
