<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class EnsembleUser extends Pivot
{
    protected $table = 'ensemble_user';

    protected $fillable = [
        'ensemble_id',
        'user_id',
        'role',
        'status',
    ];

    protected $casts = [
        'status' => 'boolean',
    ];
}
