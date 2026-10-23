<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AlumnoController extends Controller
{
    public function buscarPorRut($rut)
    {
        $alumno = DB::table('alumno')
            ->where('rut_alumno', $rut)
            ->select('rut_alumno', 'nombre_alumno as name')
            ->first();

        if (!$alumno) {
            return response()->json(['message' => 'Alumno no encontrado'], 404);
        }

        return response()->json($alumno);
    }
}
