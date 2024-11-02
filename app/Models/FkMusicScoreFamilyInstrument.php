<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FkMusicScoreFamilyInstrument extends Model
{
    use HasFactory;
    protected $table = "fk_music_score_instrument";

    public function instrumentName(){
        return $this->belongsTo(Instrument::class, 'instruments_id');
    }
    public function allMusicData(){
        return $this->belongsTo(MusicScore::class, 'music_scores_id');
    }
    // public function familyInstrumentName(){
    //     return $this->belongsTo(FamilyInstruments::class, 'name');
    // }
}
