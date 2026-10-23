<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Profesor extends Model
{
    protected $table = 'profesor';
    protected $primaryKey = 'rut_profesor';
    public $timestamps = false;

    protected $fillable = [
        'rut_profesor',
        'nombre_profesor',
        'departamento'
    ];

    public function supervisa()
    {
        return $this->hasMany(Supervisa::class, 'rut_profesor', 'rut_profesor')
            ->with('habilitacion.alumno');
    }
}
