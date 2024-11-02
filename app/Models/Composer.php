<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletes;

use Illuminate\Notifications\Notifiable;
use Illuminate\Notifications\Notification;

class Composer extends Model
{
    use SoftDeletes,Notifiable;
    protected $fillable = [
        'public_name',
        'users_id',
        'name',
        'surname',
        'vat_number',
        'street',
        'city',
        'postal_code',
        'country',
        'notification_email',
        'telephone',
        
    ];

    protected $hidden = [
        'users_id',
    ];

    protected $guarded = ['deleted_at'];

    use HasFactory;

    public function user(){
        return $this->belongsTo(User::class,'users_id','id');
    }

    public function music_scores(){
        return $this->belongsToMany(MusicScore::class,'fk_music_score_composer','composers_id','music_scores_id');
    }

    public function composerRequest(){
        return $this->hasMany(ComposerRequest::class,'composers_id');        
    }

    public function routeNotificationForMail(Notification $notification): array|string
    {
        return $this->notification_email;
    }
    public function scopeActive($query)
    {
        return $query->whereHas('composerRequest', function ($query) {
            $query->where('composer_status_id', '=', '2');
        });
    }
}
