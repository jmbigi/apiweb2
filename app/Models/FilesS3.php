<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class FilesS3 extends Model
{
    use HasFactory;

    protected $table = "files_s3_s";

    protected $fillable = [
        'path',
        'storagePlace',
        'extension', //pdf, png, etc
        'fileable_id',
        'fileable_type'
    ];

    public function fileable(): MorphTo
    {
        return $this->morphTo();
    }
}
