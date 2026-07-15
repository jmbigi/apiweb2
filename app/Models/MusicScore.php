<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\Builder;

class MusicScore extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'date',
        'status'
    ];

    public function composers()
    {
        return $this->belongsToMany(Composer::class, 'fk_music_score_composer', 'music_scores_id', 'composers_id')->withTimestamps();
    }

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id', 'id');
    }

    public function logs()
    {
        return $this->hasMany(LogDisplayMusicScore::class, 'music_scores_id', 'id');
    }

    public function instruments()
    {
        return $this->belongsToMany(Instrument::class, 'fk_music_score_instrument', 'music_scores_id', 'instruments_id')->withTimestamps();
    }

    public function style_musics()
    {
        return $this->belongsToMany(StyleMusic::class, 'fk_music_score_style_music', 'music_scores_id', 'style_musics_id')->withTimestamps();
    }

    public function linksInfo()
    {
        // return $this->hasMany(LinkInfo::class);
        return $this->hasMany(LinkInfo::class, 'music_scores_id', 'id');
    }

    public function files(): MorphMany
    {
        return $this->morphMany(FilesS3::class, 'fileable');
    }
    public function scopeActive(Builder $query)
    {
        $query->where('status', '1');
    }

    public function detailLogs()
    {
        return $this->hasMany(LogViewMusicScoreDetail::class);
    }

    public function ensemble()
    {
        return $this->belongsTo(Ensemble::class, 'ensemble_id');
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function folder()
    {
        return $this->belongsTo(EnsembleFolder::class, 'ensemble_folder_id');
    }

    public function scopePublicOrAccessible(Builder $query, ?User $user = null)
    {
        $query->where(function ($q) {
            $q->whereNull('ensemble_id');
        });

        if ($user) {
            $ensembleIds = $user->ensembles()->pluck('ensembles.id');
            if ($ensembleIds->isNotEmpty()) {
                $query->orWhereIn('ensemble_id', $ensembleIds);
            }
        }
    }
}
