<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Supervisa extends Model
{
    protected $table = 'supervisa';
    public $timestamps = false;

    protected $fillable = [
        'rut_profesor',
        'id_habilitacion',
        'tipo_profesor', // guía, comisión, etc.
    ];

    public function profesor()
    {
        return $this->belongsTo(Profesor::class, 'rut_profesor', 'rut_profesor');
    }

    public function habilitacion()
    {
        return $this->belongsTo(Habilitacion::class, 'id_habilitacion', 'id_habilitacion');
    }
}
