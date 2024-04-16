<?php

namespace App\Models\Address;

use App\Enums\BarangayClassification;
use App\QueryFilters\Address\CityFilter as CityFilter;
use App\QueryFilters\Address\ClassificationFilter;
use App\QueryFilters\Address\CodeFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Pipeline\Pipeline;

class Barangay extends Model
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
        'city_code',
        'name',
        'code_correspondence',
        'geo_level',
        'old_name',
        'classification',
    ];

    /**
     * The fields that should be hidden
     *
     * @Note There are over 40K barangays, we hide some un-needed fields
     * to lessen the memory size the clients need to download
     *
     * @var array
     */
    protected $hidden = [
        'old_name', 'geo_level', 'created_at', 'updated_at', 'code', 'classification',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'classification' => BarangayClassification::class,
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
                CityFilter::class,
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
    protected function city(): BelongsTo
    {
        return $this->belongsTo(Province::class);
    }
}
