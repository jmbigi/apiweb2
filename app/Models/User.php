<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Laratrust\Traits\LaratrustUserTrait;

use Illuminate\Database\Eloquent\SoftDeletes;


class User extends Authenticatable
{
    use LaratrustUserTrait;
    use SoftDeletes;
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'id_card',
        'telephone',
        'password',
        'status'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function composer()
    {
        return $this->hasOne(Composer::class, 'users_id', 'id');
    }

    public function music_scores()
    {
        return $this->hasMany(MusicScore::class);
    }

    public function logs()
    {
        return $this->hasMany(LogDisplayMusicScore::class);
    }

    public function subscriptions()
    {
        return $this->hasMany(SubscribedUser::class, 'user_id');
    }
    public function favMusic()
    {
        return $this->belongsToMany(MusicScore::class, 'fav_music_score', 'user_id', 'music_scores_id');
    }

    public function premiumTrial()
    {
        return $this->hasOne(PremiumTrial::class);
    }

    public function musicScoreDetailLogs()
    {
        return $this->hasMany(LogViewMusicScoreDetail::class);
    }
}
