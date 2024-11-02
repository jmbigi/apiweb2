<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

use Illuminate\Database\Eloquent\SoftDeletes;

class ComposerRequest extends Model
{
    use SoftDeletes;
    protected $table = "composer_request";

    protected $fillable = [
        'composers_id',
        'request_date',
        'description',
        'updated_by',
        'composer_status_id',
        'request_status_id',
        'comment'
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    public function composer(){
        return $this->belongsTo(Composer::class,'composers_id');
    }

    public function composer_status(){
        return $this->belongsTo(ComposerStatus::class,'composer_status_id');
    }
    public function request_status(){
        return $this->belongsTo(RequestStatus::class,'request_status_id');        
    }
}
