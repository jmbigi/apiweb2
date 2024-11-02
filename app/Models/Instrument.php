<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Instrument extends Model
{
    use HasFactory;

    protected $table = "instruments";

    protected $fillable = [
        'name',
        'family_instruments_id',
        'request',
        'approved'
    ];

    protected $hidden = [
        'request',
        'approved',
    ];

    public function family(){
        return $this->belongsTo(FamilyInstruments::class,'family_instruments_id');
    }

    public function music_scores(){
        return $this->belongsToMany(MusicScore::class,'fk_music_score_instrument','instruments_id','music_scores_id');
    }

    public function scopeActive(Builder $query){
        $query->whereNotNull('request')->whereNotNull('approved');
    }

    public function instruments(){
        return $this->belongsToMany(Instrument::class,'fk_instrument_family_instrument','instrument_id','family_instrument_id')->withTimestamps();
    }
}
