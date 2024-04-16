<?php

namespace App\Models\Address;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Address extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'home_address',
        'barangay_id',
        'city_id',
        'province_id',
        'region_id',
        'postal_code',
    ];

    /**
     * The attributes that are hidden
     *
     * @var string[]
     */
    protected $hidden = ['id', 'user_profile_id'];

    /**
     * The relationships to eager-load
     */
    protected $with = ['city', 'province', 'region', 'barangay'];

    /**
     * An address belongs to a user
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * An address is part of a barangay
     */
    public function barangay(): BelongsTo
    {
        return $this->belongsTo(Barangay::class);
    }

    /**
     * An address is part of a city/municipality
     */
    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }

    /**
     * An address is part of a province
     */
    public function province(): BelongsTo
    {
        return $this->belongsTo(Province::class);
    }

    /**
     * An address is part of a region
     */
    public function region(): BelongsTo
    {
        return $this->belongsTo(Region::class);
    }
}
