<?php

namespace App\Http\Controllers;

use App\Models\Profesor;
use App\Models\Habilitacion;

class ListadoController extends Controller
{
    /**
     * R4.11 - Listado por semestre
     */
public function listadoSemestre()
{
    $habilitaciones = Habilitacion::with(['alumno', 'supervisores.profesor'])
        ->orderBy('semestre_inicio_anho', 'asc')
        ->orderBy('semestre_inicio', 'asc')
        ->get()
        ->map(function ($h) {

            $supervisor = $h->supervisores->first();

            return [
                'rut_alumno'        => $h->alumno->rut_alumno ?? 'N/A',
                'nombre_alumno'     => $h->alumno->nombre_alumno ?? 'N/A',
                'tipo_habilitacion' => $h->tipo_habilitacion,
                'rut_profesor'      => $supervisor?->profesor?->rut_profesor ?? 'N/A',
                'nombre_profesor'   => $supervisor?->profesor?->nombre_profesor ?? 'N/A',
                'semestre_inicio'   => $h->semestre_inicio,
            ];
        });

    if ($habilitaciones->isEmpty()) {
        return inertia('Habilitacion/ListadoSemestre', [
            'habilitaciones' => [],
            'mensaje' => 'No hay datos disponibles para generar el listado solicitado.',
        ]);
    }

    return inertia('Habilitacion/ListadoSemestre', [
        'habilitaciones' => $habilitaciones,
        'mensaje' => null,
    ]);
}



    /**
     * R4.12 - Listado histórico por profesor
     */
    public function listadoProfesor()
{
    $profesores = Profesor::with([
        'supervisa.habilitacion.alumno'
    ])->get();

    // Solo profesores con datos
    $profesores = $profesores->filter(fn($p) => $p->supervisa->isNotEmpty());

    // Formato del requisito
    $profesores = $profesores->map(function ($p) {

        $habilitaciones = $p->supervisa
            ->unique('id_habilitacion')
            ->values()
            ->map(function ($s) {

                $semestreCompleto = $s->habilitacion->semestre_inicio_anho 
                                    . '-' 
                                    . $s->habilitacion->semestre_inicio;

                return [
                    'rut_profesor'     => $s->profesor->rut_profesor,
                    'nombre_profesor'  => $s->profesor->nombre_profesor,
                    'rut_alumno'       => $s->habilitacion->alumno->rut_alumno,
                    'nombre_alumno'    => $s->habilitacion->alumno->nombre_alumno,
                    'semestre_inicio'  => $semestreCompleto, // ← FALTABA
                ];
            });

        return [
            'rut_profesor'    => $p->rut_profesor,
            'nombre_profesor' => $p->nombre_profesor,
            'habilitaciones'  => $habilitaciones
        ];
    })->values();

    return inertia('Habilitacion/ListadoProfesor', [
        'profesores' => $profesores
    ]);
}

}
