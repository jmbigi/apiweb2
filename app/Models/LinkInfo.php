<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LinkInfo extends Model
{
    use HasFactory;

    protected $fillable = [
        'url',
        'social_network',
    ];

    public function music_score(){
        return $this->belongsTo(MusicScore::class,'music_scores_id');
    }
}
