<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * Os atributos que podem ser preenchidos em massa.
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'access_level',
        'is_active',
        'last_login_ip', // Novo campo adicionado para registro de auditoria
        'email_verified_at'
    ];

    /**
     * Os atributos que devem ficar ocultos em arrays (JSON).
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Os atributos que devem ser convertidos (cast).
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed', // Garante que a senha seja sempre tratada como Hash
        'is_active' => 'boolean',
        'access_level' => 'integer',
    ];
}