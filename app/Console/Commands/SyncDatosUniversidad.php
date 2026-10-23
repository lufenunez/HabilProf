<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SyncDatosUniversidad extends Command
{
    protected $signature = 'sync:universidad';
    protected $description = 'Sincroniza alumnos, profesores y notas desde la base fuente hacia la base destino';

    public function handle()
    {
        $this->info('Sincronización de Universidad iniciada...');

        try {

            //Carga datos alumno
            $alumnos = DB::connection('pgsql_source')->table('sistema_alumnos')->get();

            foreach ($alumnos as $a) {
                if (
                    !$this->validarRut($a->rut_alumno) ||
                    !$this->validarNombre($a->nombre_alumno)
                ) continue; 

                $updated = DB::connection('pgsql')->table('alumno')->updateOrInsert(
                    ['rut_alumno' => $a->rut_alumno],
                    [
                        'rut_alumno' => $a->rut_alumno,
                        'nombre_alumno' => $a->nombre_alumno
                    ]
                );
            }

            //Carga datos profesores
            $profesores = DB::connection('pgsql_source')->table('sistema_profesores')->get();

            foreach ($profesores as $p) {
                if (
                    !$this->validarRut($p->rut_profesor) ||
                    !$this->validarNombre($p->nombre_profesor) ||
                    !$this->validarDepartamento($p->departamento)
                ) continue;

                $updated = DB::connection('pgsql')->table('profesor')->updateOrInsert(
                    ['rut_profesor' => $p->rut_profesor],
                    [
                        'rut_profesor' => $p->rut_profesor,
                        'nombre_profesor' => $p->nombre_profesor,
                        'departamento' => $p->departamento
                    ]
                );
            }

            //Carga de notas
            $notas = DB::connection('pgsql_source')->table('notas_en_linea')->get();

            foreach ($notas as $n) {
                $idHabilitacion = DB::connection('pgsql')->table('habilitacion')
                    ->where('rut_alumno', $n->rut_alumno)
                    ->value('id_habilitacion');

                if (!$idHabilitacion) continue;

                $notaFinal = $this->validarNota($n->nota) ? $n->nota : null;
                $fechaNota = $this->validarFecha($n->fecha_nota) ? $n->fecha_nota : null;

                DB::connection('pgsql')->table('habilitacion')->updateOrInsert(
                    ['id_habilitacion' => $idHabilitacion],
                    [
                        'nota_final' => $notaFinal,
                        'fecha_nota' => $fechaNota,
                    ]
                );
            }

            $this->info('Carga automática completada correctamente');
            Log::info('Carga automática completada correctamente');

        } catch (\Exception $e) {
            $this->error('Error en carga automática: ' . $e->getMessage());
            Log::error('Error en carga automática: ' . $e->getMessage());
        }
    }

    //Validaciones

    private function validarRut($rut): bool
    {
        return preg_match('/^[0-9]{1,8}$/', $rut);
    }

    private function validarNombre($nombre): bool
    {
        return preg_match('/^[A-Za-zÁÉÍÓÚáéíóúÑñ ]{1,50}$/', $nombre);
    }

    private function validarNota($nota): bool
    {
        return is_numeric($nota) && $nota >= 1.0 && $nota <= 7.0;
    }

    private function validarFecha($fecha): bool
    {
        return preg_match('/^\d{4}-\d{2}-\d{2}( \d{2}:\d{2}:\d{2})?$/', $fecha);
    }

    private function validarDepartamento($dep): bool
    {
        return in_array($dep, ['DINF', 'Externo']);
    }
}
