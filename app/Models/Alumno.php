<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Alumno extends Model
{
    protected $table = 'alumno';
    protected $primaryKey = 'rut_alumno';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'rut_alumno',
        'nombre_alumno',
    ];
}
