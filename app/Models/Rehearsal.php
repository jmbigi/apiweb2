<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rehearsal extends Model
{
    use HasFactory;

    protected $fillable = [
        'ensemble_id',
        'title',
        'date',
        'time',
        'location',
        'instructor_id',
        'notes',
        'status',
    ];

    protected $casts = [
        'date' => 'date',
        'time' => 'datetime:H:i',
        'status' => 'boolean',
    ];

    public function ensemble()
    {
        return $this->belongsTo(Ensemble::class);
    }

    public function instructor()
    {
        return $this->belongsTo(User::class, 'instructor_id');
    }
}
