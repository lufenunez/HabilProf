<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tablas del dominio de HabilProf.
     */
    public function up(): void
    {
        Schema::create('alumno', function (Blueprint $table) {
            $table->unsignedBigInteger('rut_alumno')->primary(); // rut sin dígito verificador
            $table->string('nombre_alumno', 50);
            $table->timestamps();
        });

        Schema::create('profesor', function (Blueprint $table) {
            $table->unsignedBigInteger('rut_profesor')->primary();
            $table->string('nombre_profesor', 50);
            $table->string('departamento', 20); // DINF o Externo
        });

        Schema::create('habilitacion', function (Blueprint $table) {
            $table->increments('id_habilitacion');
            $table->unsignedBigInteger('rut_alumno')->unique(); // una habilitación por alumno
            $table->integer('semestre_inicio_anho');
            $table->smallInteger('semestre_inicio'); // 1 o 2
            $table->text('descripcion')->nullable();
            $table->string('tipo_habilitacion', 50);
            $table->decimal('nota_final', 2, 1)->nullable(); // 1.0 a 7.0
            $table->timestamp('fecha_nota')->nullable();

            $table->foreign('rut_alumno')->references('rut_alumno')->on('alumno');
        });

        Schema::create('proyecto_ingenieria', function (Blueprint $table) {
            $table->unsignedInteger('id_habilitacion')->primary();
            $table->string('titulo', 255);

            $table->foreign('id_habilitacion')->references('id_habilitacion')->on('habilitacion')->cascadeOnDelete();
        });

        Schema::create('proyecto_investigacion', function (Blueprint $table) {
            $table->unsignedInteger('id_habilitacion')->primary();
            $table->string('titulo', 255);

            $table->foreign('id_habilitacion')->references('id_habilitacion')->on('habilitacion')->cascadeOnDelete();
        });

        Schema::create('practica_tutelada', function (Blueprint $table) {
            $table->unsignedInteger('id_habilitacion')->primary();
            $table->string('nombre_empresa', 255);
            $table->string('nombre_supervisor', 255);

            $table->foreign('id_habilitacion')->references('id_habilitacion')->on('habilitacion')->cascadeOnDelete();
        });

        Schema::create('supervisa', function (Blueprint $table) {
            $table->unsignedBigInteger('rut_profesor');
            $table->unsignedInteger('id_habilitacion');
            $table->string('tipo_profesor', 20); // guía, comision, co-guia o tutor

            $table->primary(['rut_profesor', 'id_habilitacion', 'tipo_profesor']);
            $table->foreign('rut_profesor')->references('rut_profesor')->on('profesor');
            $table->foreign('id_habilitacion')->references('id_habilitacion')->on('habilitacion')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('supervisa');
        Schema::dropIfExists('practica_tutelada');
        Schema::dropIfExists('proyecto_investigacion');
        Schema::dropIfExists('proyecto_ingenieria');
        Schema::dropIfExists('habilitacion');
        Schema::dropIfExists('profesor');
        Schema::dropIfExists('alumno');
    }
};
