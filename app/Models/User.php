<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    // Campos que se pueden asignar de forma masiva
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    // Campos que NO se devuelven en el JSON (la contraseña nunca sale en las respuestas)
    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Un usuario tiene un token (el de su último login).
     */
    public function token()
    {
        return $this->hasOne(Token::class);
    }
}
