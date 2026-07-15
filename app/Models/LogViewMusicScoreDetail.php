<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LogViewMusicScoreDetail extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'music_score_id'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function musicScore()
    {
        return $this->belongsTo(MusicScore::class);
    }
}
