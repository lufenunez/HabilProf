<script setup>
const props = defineProps({
    habilitaciones: {
        type: Array,
        default: () => [],
    },
    mensaje: {
        type: String,
        default: null,
    },
});
</script>

<template>
    <div class="p-6">
        <h1 class="text-2xl font-bold mb-4">Habilitaciones por Semestre</h1>

        <!-- Mensaje si no hay datos -->
        <div v-if="mensaje" class="text-red-600 font-semibold">
            {{ mensaje }}
        </div>

        <!-- Tabla de resultados -->
        <table v-else class="w-full border-collapse border border-gray-300">
            <thead>
                <tr class="bg-gray-100">
                    <th class="border px-2 py-1">RUT Alumno</th>
                    <th class="border px-2 py-1">Nombre Alumno</th>
                    <th class="border px-2 py-1">Tipo Habilitación</th>
                    <th class="border px-2 py-1">RUT Profesor</th>
                    <th class="border px-2 py-1">Nombre Profesor</th>
                    <th class="border px-2 py-1">Semestre Inicio</th>
                </tr>
            </thead>

            <tbody>
                <tr v-for="(h, i) in habilitaciones" :key="i">
                    <td class="border px-2 py-1">{{ h.rut_alumno }}</td>
                    <td class="border px-2 py-1">{{ h.nombre_alumno }}</td>
                    <td class="border px-2 py-1">{{ h.tipo_habilitacion }}</td>
                    <td class="border px-2 py-1">{{ h.rut_profesor }}</td>
                    <td class="border px-2 py-1">{{ h.nombre_profesor }}</td>
                    <td class="border px-2 py-1">{{ h.semestre_inicio }}</td>
                </tr>
            </tbody>
        </table>
    </div>
</template>
