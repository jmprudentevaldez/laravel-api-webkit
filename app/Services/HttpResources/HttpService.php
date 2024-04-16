<?php

namespace App\Services\HttpResources;

use App\Enums\PaginationType;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\CursorPaginator;

class HttpService
{
    /**
     * Build pagination
     */
    protected function buildPagination(
        ?PaginationType $pagination,
        Builder $builder,
        ?int $limit = null,
    ): Paginator|Collection|LengthAwarePaginator|CursorPaginator {
        // If limit is not passed as a parameter, we get the limit from the request
        $limit = $limit ?? request('limit');

        // If limit is still null (no limit param found in the request), we set a default to 15
        if (! $limit) {
            $limit = 15;
        }

        return match ($pagination) {
            PaginationType::LENGTH_AWARE => $builder->paginate($limit),
            PaginationType::SIMPLE => $builder->simplePaginate($limit),
            PaginationType::CURSOR => $builder->cursorPaginate($limit),
            default => $builder->get(),
        };
    }
}
