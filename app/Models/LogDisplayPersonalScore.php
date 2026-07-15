<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LogDisplayPersonalScore extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'filename',
    ];

    protected $casts = [
        'user_id' => 'integer',
    ];

    /**
     * Relación con el usuario (opcional)
     */
    public function user()
    {
        return $this->belongsTo(User::class)->withTrashed();
    }

    /**
     * Scope para logs de usuarios autenticados
     */
    public function scopeAuthenticated($query)
    {
        return $query->whereNotNull('user_id');
    }

    /**
     * Scope para logs de usuarios anónimos
     */
    public function scopeAnonymous($query)
    {
        return $query->whereNull('user_id');
    }

    /**
     * Scope para filtrar por filename
     */
    public function scopeByFilename($query, $filename)
    {
        return $query->where('filename', $filename);
    }
}
