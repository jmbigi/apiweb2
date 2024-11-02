<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class StyleMusic extends Model
{
    use HasFactory;

    protected $table = "style_musics";

    protected $fillable = [
        'name',
        'request',
        'approved'
    ];

    protected $hidden = [
        'request',
        'approved',
    ];

    public function music_scores(){
        return $this->belongsToMany(MusicScore::class,'fk_music_score_style_music','style_musics_id','music_scores_id');
    }

    public function scopeActive(Builder $query){
        $query->whereNotNull('request')->whereNotNull('approved');
    }
}
