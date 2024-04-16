<?php

namespace App\Models\Address;

use App\QueryFilters\Address\CodeFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Pipeline\Pipeline;

class Region extends Model
{
    use HasFactory;

    /**
     * The properties that are mass-assignable
     *
     * @var string[]
     */
    protected $fillable = [
        'id',
        'code',
        'name',
        'alt_name',
        'geo_level',
        'code_correspondence',
    ];

    /**
     * @Scope
     * Pipeline for HTTP query filters
     */
    public function scopeFiltered(Builder $builder): Builder
    {
        return app(Pipeline::class)
            ->send($builder)
            ->through([
                CodeFilter::class,
            ])
            ->thenReturn();
    }

    /**
     * A region comprises an address
     *
     * @returns HasMany
     */
    protected function address(): HasMany
    {
        return $this->hasMany(Address::class);
    }
}
