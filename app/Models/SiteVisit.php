<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SiteVisit extends Model
{
    use HasFactory;

    protected $fillable = ['page', 'visited_at'];

    public $timestamps = false; // No necesitamos `created_at` ni `updated_at`
}
