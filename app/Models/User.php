<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'usuario',
        'password',
        'rut_profesor',
        'name',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    // Le decimos a Laravel que el campo de login es "usuario"
    public function username()
    {
        return 'usuario';
    }

    public function habilitacionesComoGuia()
    {
        return $this->hasMany(Habilitacion::class, 'profesor_dinf_id');
    }

    public function habilitacionesComoComision()
    {
        return $this->hasMany(Habilitacion::class, 'comision_profesor_id');
    }
}
