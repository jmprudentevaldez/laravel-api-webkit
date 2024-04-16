<?php

namespace App\Models\Address;

use App\Enums\MunicipalClassification;
use App\QueryFilters\Address\ClassificationFilter;
use App\QueryFilters\Address\CodeFilter;
use App\QueryFilters\Address\ProvinceFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Pipeline\Pipeline;

class City extends Model
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
        'province_code',
        'name',
        'code_correspondence',
        'classification',
        'income_classification',
        'old_name',
        'city_class',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'classification' => MunicipalClassification::class,
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
                ClassificationFilter::class,
                ProvinceFilter::class,
            ])
            ->thenReturn();
    }

    /**
     * A City comprises an address
     *
     * @returns HasMany
     */
    protected function address(): HasMany
    {
        return $this->hasMany(Address::class);
    }

    /**
     * A City belongs to province
     *
     * @returns BelongsTo
     */
    protected function province(): BelongsTo
    {
        return $this->belongsTo(ProvinceFilter::class);
    }
}
