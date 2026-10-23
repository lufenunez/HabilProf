<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Habilitacion;
class HabilitacionController extends Controller
{
    public function store(Request $request)
    {
        // Validación
        $validated = $request->validate([
            'rut_alumno' => 'required|integer', 
            'semestre_inicio_anho' => 'required|integer|min:2000|max:2100',
            'semestre_inicio' => 'required|integer|min:1|max:2', 
            'descripcion' => 'nullable|string',
            'tipo_habilitacion' => 'required|string|in:Proyecto de Ingeniería,Proyecto de Investigación,Práctica Tutelada',
            'titulo' => 'nullable|string|max:255',
            'profesor_guia' => 'nullable|integer',
            'profesor_comision' => 'nullable|integer',
            'profesor_coguia' => 'nullable|integer',
            'profesor_tutor' => 'nullable|integer', 
            'nombre_empresa' => 'nullable|string|max:255',
            'nombre_supervisor' => 'nullable|string|max:255',
        ]);

        // Verifica existencia de alumno
        $alumnoExiste = DB::table('alumno')
            ->where('rut_alumno', $validated['rut_alumno'])
            ->exists();

        if (!$alumnoExiste) {
            return response()->json([
                'errors' => [
                    'rut_alumno' => ['El RUT ingresado no existe en la base de datos de alumnos.']
                ]
            ], 422);
        }

        // Verifica si ya tiene habilitación
        $existe = DB::table('habilitacion')
            ->where('rut_alumno', $validated['rut_alumno'])
            ->exists();

        if ($existe) {
            return response()->json([
                'errors' => [
                    'rut_alumno' => ['El alumno ya posee una habilitación registrada y no puede tener más de una.']
                ]
            ], 422);
        }

        // Transacción
        DB::beginTransaction();
        
        try {
            // Validaciones adicionales
            if (
                ($validated['tipo_habilitacion'] === 'Proyecto de Ingeniería' ||
                 $validated['tipo_habilitacion'] === 'Proyecto de Investigación') &&
                (!$request->input('profesor_guia') || !$request->input('profesor_comision') || !$request->input('titulo'))
            ) {
                DB::rollBack();
                return response()->json(['errors' => ['proyecto' => ['Faltan campos requeridos para el Proyecto (Guía, Comisión, Título).']]], 422);
            }

            if (
                $validated['tipo_habilitacion'] === 'Práctica Tutelada' &&
                (!$request->input('profesor_tutor') || !$request->input('nombre_empresa') || !$request->input('nombre_supervisor'))
            ) {
                DB::rollBack();
                return response()->json(['errors' => ['practica' => ['Faltan campos requeridos para la Práctica (Tutor, Empresa, Supervisor).']]], 422);
            }

            // Inserta en habilitación
            $nuevoID = DB::table('habilitacion')->insertGetId([
                'rut_alumno' => $validated['rut_alumno'],
                'semestre_inicio_anho' => $validated['semestre_inicio_anho'],
                'semestre_inicio' => $validated['semestre_inicio'],
                'descripcion' => $validated['descripcion'] ?? null,
                'tipo_habilitacion' => $request->input('tipo_habilitacion'),
            ], 'id_habilitacion');

            // Inserciones según tipo
            switch ($validated['tipo_habilitacion']) {
                case 'Proyecto de Ingeniería':
                    DB::table('proyecto_ingenieria')->insert([
                        'id_habilitacion' => $nuevoID,
                        'titulo' => $request->input('titulo'),
                    ]);

                    DB::table('supervisa')->insert([
                        [
                            'rut_profesor' => $request->input('profesor_guia'),
                            'id_habilitacion' => $nuevoID,
                            'tipo_profesor' => 'guía'
                        ],
                        [
                            'rut_profesor' => $request->input('profesor_comision'),
                            'id_habilitacion' => $nuevoID,
                            'tipo_profesor' => 'comision'
                        ],
                    ]);

                    if ($request->filled('profesor_coguia')) {
                        DB::table('supervisa')->insert([
                            'rut_profesor' => $request->input('profesor_coguia'),
                            'id_habilitacion' => $nuevoID,
                            'tipo_profesor' => 'co-guia',
                        ]);
                    }
                    break;

                case 'Proyecto de Investigación':
                    DB::table('proyecto_investigacion')->insert([
                        'id_habilitacion' => $nuevoID,
                        'titulo' => $request->input('titulo'),
                    ]);

                    DB::table('supervisa')->insert([
                        [
                            'rut_profesor' => $request->input('profesor_guia'),
                            'id_habilitacion' => $nuevoID,
                            'tipo_profesor' => 'guía'
                        ],
                        [
                            'rut_profesor' => $request->input('profesor_comision'),
                            'id_habilitacion' => $nuevoID,
                            'tipo_profesor' => 'comision'
                        ],
                    ]);

                    if ($request->filled('profesor_coguia')) {
                        DB::table('supervisa')->insert([
                            'rut_profesor' => $request->input('profesor_coguia'),
                            'id_habilitacion' => $nuevoID,
                            'tipo_profesor' => 'co-guia',
                        ]);
                    }
                    break;

                case 'Práctica Tutelada':
                    DB::table('practica_tutelada')->insert([
                        'id_habilitacion' => $nuevoID,
                        'nombre_empresa' => $request->input('nombre_empresa'),
                        'nombre_supervisor' => $request->input('nombre_supervisor'),
                    ]);

                    DB::table('supervisa')->insert([
                        'rut_profesor' => $request->input('profesor_tutor'),
                        'id_habilitacion' => $nuevoID,
                        'tipo_profesor' => 'tutor',
                    ]);
                    break;
            }

            DB::commit();

            // Retorno exitoso
            return response()->json([
                'message' => 'Habilitación creada con éxito',
                'id_habilitacion' => str_pad($nuevoID, 6, '0', STR_PAD_LEFT)
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error de base de datos al guardar la habilitación. La transacción fue revertida.',
                'error_detail' => $e->getMessage()
            ], 500);
        }
    }
        public function buscarPorRut($rut)
    {
        $habilitacion = Habilitacion::where('rut_alumno', $rut)->get();

        if ($habilitacion->isEmpty()) {
            return response()->json([], 200);
        }

        return response()->json($habilitacion, 200);
    }

            public function update(Request $request, $id)
    {
        $habilitacion = Habilitacion::find($id);

        if (!$habilitacion) {
            return response()->json(['error' => 'No encontrada'], 404);
        }

        $habilitacion->update($request->all());

        return response()->json([
            'message' => 'Modificada correctamente',
            'data' => $habilitacion
        ]);
    }
            public function destroy($id)
    {
        $habilitacion = Habilitacion::find($id);

        if (!$habilitacion) {
            return response()->json(['error' => 'Habilitación no encontrada'], 404);
        }

        $habilitacion->delete();

        return response()->json([
            'message' => 'Habilitación eliminada correctamente'
        ]);
    }


    public function getNextId()
    {
        $ultimoID = DB::table('habilitacion')->max('id_habilitacion');
        $nuevoID = $ultimoID ? $ultimoID + 1 : 1;

        if ($nuevoID > 999999) {
            return response()->json(['error' => 'Límite máximo de ID alcanzado'], 400);
        }

        return response()->json([
            'next_id' => str_pad($nuevoID, 6, '0', STR_PAD_LEFT)
        ]);
    }

    public function obtenerProfesores()
    {
        $profesoresDinf = DB::table('profesor')
            ->whereRaw("LOWER(TRIM(departamento)) = 'dinf'")
            ->select('rut_profesor', 'nombre_profesor', 'departamento')
            ->orderBy('nombre_profesor')
            ->get();

        $profesoresTodos = DB::table('profesor')
            ->select('rut_profesor', 'nombre_profesor', 'departamento')
            ->orderBy('nombre_profesor')
            ->get();

        return response()->json([
            'dinf' => $profesoresDinf,
            'todos' => $profesoresTodos,
        ]);
    }
}

