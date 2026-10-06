<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Token extends Model
{
    // Campos que se pueden asignar de forma masiva
    protected $fillable = [
        'user_id',
        'token',
    ];

    /**
     * El token pertenece a un usuario.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
