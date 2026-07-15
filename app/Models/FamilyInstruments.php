<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class FamilyInstruments extends Model
{
    use HasFactory;

    protected $table = "family_instruments";

    protected $fillable = [
        'name',
        'request',
        'approved',
    ];

    protected $hidden = [
        'request',
        'approved',
    ];

    public function instruments(){
        return $this->hasMany(Instrument::class,'family_instruments_id');
    }

    public function scopeActive(Builder $query){
        $query->whereNotNull('request')->whereNotNull('approved')
            ->orWhereNull('request')->whereNull('approved');
    }
}
