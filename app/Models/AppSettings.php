<?php

namespace App\Models;

use App\Enums\AppTheme;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AppSettings extends Model
{
    use HasFactory;

    protected $table = 'app_settings';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'value',
    ];

    /**
     * The attributes that should be casts
     *
     * @var array
     */
    protected $casts = [
        'theme' => AppTheme::class,
    ];
}
