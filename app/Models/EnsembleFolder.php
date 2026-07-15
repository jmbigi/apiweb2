<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EnsembleFolder extends Model
{
    use HasFactory;

    protected $fillable = [
        'ensemble_id',
        'name',
        'parent_id',
    ];

    public function ensemble()
    {
        return $this->belongsTo(Ensemble::class);
    }

    public function children()
    {
        return $this->hasMany(EnsembleFolder::class, 'parent_id');
    }

    public function parent()
    {
        return $this->belongsTo(EnsembleFolder::class, 'parent_id');
    }

    public function scores()
    {
        return $this->hasMany(MusicScore::class, 'ensemble_folder_id');
    }
}
