<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Ensemble extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'cif',
        'description',
        'owner_id',
        'status',
    ];

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function members()
    {
        return $this->belongsToMany(User::class, 'ensemble_user')
            ->withPivot('role', 'status')
            ->withTimestamps();
    }

    public function folders()
    {
        return $this->hasMany(EnsembleFolder::class);
    }

    public function rehearsals()
    {
        return $this->hasMany(Rehearsal::class);
    }

    public function scores()
    {
        return $this->hasMany(MusicScore::class, 'ensemble_id');
    }
}
