<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class ComposerStatus extends Model
{
    protected $table = "composer_status";

    protected $fillable = [
        'name'
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    public function composer_request(){
        return $this->hasMany(ComposerRequest::class,'composer_status_id');
    }
}