<script setup>
import { computed } from "vue";

const props = defineProps({
  profesores: Array,
  mensaje: String,
});

// Aplanar todos los registros en una sola tabla
const filas = computed(() => {
  if (!props.profesores) return [];

  return props.profesores.flatMap((p) =>
    (p.habilitaciones || []).map((h) => ({
      rut_profesor: p.rut_profesor,
      nombre_profesor: p.nombre_profesor,
      rut_alumno: h.rut_alumno,
      nombre_alumno: h.nombre_alumno,
      semestre_inicio: h.semestre_inicio, // ← FALTABA
    }))
  );
});
</script>

<template>
  <div class="p-6">
    <h1 class="text-2xl font-bold mb-4">Histórico por Profesor</h1>

    <table
      v-if="filas.length > 0"
      class="w-full border-collapse border border-gray-300 text-left"
    >
      <thead class="bg-gray-100">
        <tr>
          <th class="border px-2 py-1">RUT Profesor</th>
          <th class="border px-2 py-1">Nombre Profesor</th>
          <th class="border px-2 py-1">RUT Alumno</th>
          <th class="border px-2 py-1">Nombre Alumno</th>
          <th class="border px-2 py-1">Semestre</th> <!-- NUEVO -->
        </tr>
      </thead>

      <tbody>
        <tr v-for="(f, index) in filas" :key="index">
          <td class="border px-2 py-1">{{ f.rut_profesor }}</td>
          <td class="border px-2 py-1">{{ f.nombre_profesor }}</td>
          <td class="border px-2 py-1">{{ f.rut_alumno }}</td>
          <td class="border px-2 py-1">{{ f.nombre_alumno }}</td>
          <td class="border px-2 py-1">{{ f.semestre_inicio }}</td> <!-- NUEVO -->
        </tr>
      </tbody>
    </table>

    <p v-else class="text-gray-500 mt-4">
      No hay registros disponibles.
    </p>
  </div>
</template>
