<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class RequestStatus extends Model
{
    protected $table = "request_status";

    protected $fillable = [
        'name'
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    public function composer_request(){
        return $this->hasMany(ComposerRequest::class,'request_status_id');
    }
}