<?php

namespace App\Models;

use App\Models\MusicScore;
use App\Models\StyleMusic;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;

class FkMusicScoreStyle extends Model
{
    use HasFactory;
    protected $table = "fk_music_score_style_music";

    public function styleName(){
        return $this->belongsTo(StyleMusic::class, 'style_musics_id');
    }
    public function allMusicData(){
        return $this->belongsTo(MusicScore::class, 'music_scores_id')->where('status','1');
    }
}

