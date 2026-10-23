<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Habilitacion extends Model
{
    protected $table = 'habilitacion';
    protected $primaryKey = 'id_habilitacion';
    public $timestamps = false;

    protected $fillable = [
        'rut_alumno',
        'semestre_inicio',
        'descripcion',
        'nota_final',
        'fecha_nota',
        'semestre_inicio_anho',
        'tipo_habilitacion'
    ];

    public function alumno()
    {
        return $this->belongsTo(Alumno::class, 'rut_alumno', 'rut_alumno');
    }

    public function supervisores()
    {
        return $this->hasMany(Supervisa::class, 'id_habilitacion', 'id_habilitacion')
            ->with('profesor');
    }
}
