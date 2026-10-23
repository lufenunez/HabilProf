<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class UserSyncController extends Controller
{
    public function index()
    {
        // Trae alumnos
        $alumnos = DB::table('alumno')
            ->select('rut_alumno as id', 'nombre_alumno as name')
            ->get();

        // Trae profesores
        $profesores = DB::table('profesor')
            ->select('rut_profesor as id', 'nombre_profesor as name')
            ->get();

        // Enviar ambos por separado
        return response()->json([
            'alumnos' => $alumnos,
            'profesores' => $profesores,
        ]);
    }
}

