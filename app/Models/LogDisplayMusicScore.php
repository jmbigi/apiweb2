<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LogDisplayMusicScore extends Model
{
    use HasFactory;

    public function user(){
        return $this->belongsTo(User::class,'users_id');
    }

    public function music_score(){
        return $this->belongsTo(MusicScore::class,'music_scores_id');
    }
}
