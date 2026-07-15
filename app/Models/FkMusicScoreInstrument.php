<?php

namespace App\Models;

use App\Models\Instrument;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class FkMusicScoreInstrument extends Model
{
    use HasFactory;

    protected $table = "fk_music_score_instrument";

    public function instrumentName(){
        return $this->belongsTo(Instrument::class, 'instruments_id');
    }
    public function allMusicData(){
        return $this->belongsTo(MusicScore::class, 'music_scores_id')->where('status','1');
    }

}
