<script setup>
import { reactive, ref } from 'vue';
import axios from 'axios';
import { Head } from '@inertiajs/vue3';

const form = reactive({
    rut_alumno: '',
    id_habilitacion: '',
    tipo_habilitacion: '',
    semestre_inicio: '',
    descripcion: '',
    nota_final: '',
    fecha_nota: '',
});

const habilitacionEncontrada = ref(false);
const errorMessage = ref('');
const showSuccess = ref(false);

// Buscar por RUT
const buscarPorRut = async () => {
    errorMessage.value = '';
    showSuccess.value = false;
    habilitacionEncontrada.value = false;

    if (!form.rut_alumno) {
        errorMessage.value = 'Debe ingresar un RUT.';
        return;
    }

    try {
        const response = await axios.get(`/api/habilitaciones/rut/${form.rut_alumno}`);
        const datos = response.data;

        if (datos.length === 0) {
            errorMessage.value = 'No existe habilitación para este alumno.';
            return;
        }

        const h = datos[0]; // un alumno tiene una sola habilitación

        form.id_habilitacion = h.id_habilitacion;
        form.tipo_habilitacion = h.tipo_habilitacion;
        form.semestre_inicio = h.semestre_inicio;
        form.descripcion = h.descripcion;
        form.nota_final = h.nota_final;
        form.fecha_nota = h.fecha_nota;

        habilitacionEncontrada.value = true;

    } catch (error) {
        errorMessage.value = 'Error al buscar habilitación.';
    }
};

// Modificar habilitación
const modificar = async () => {
    try {
        await axios.put(`/api/habilitaciones/${form.id_habilitacion}`, {
            tipo_habilitacion: form.tipo_habilitacion,
            semestre_inicio: form.semestre_inicio,
            descripcion: form.descripcion,
            nota_final: form.nota_final,
        });

        showSuccess.value = true;
        habilitacionEncontrada.value = false;

        setTimeout(() => showSuccess.value = false, 3500);

    } catch (error) {
        errorMessage.value = 'Error al modificar la habilitación.';
    }
};

// Eliminar habilitación
const eliminar = async () => {
    try {
        await axios.delete(`/api/habilitaciones/${form.id_habilitacion}`);

        showSuccess.value = true;
        habilitacionEncontrada.value = false;

        // limpiar form
        for (const k in form) form[k] = '';

        setTimeout(() => showSuccess.value = false, 3500);

    } catch (error) {
        errorMessage.value = 'Error al eliminar la habilitación.';
    }
};
</script>

<template>
    <Head title="Modificar o Eliminar por RUT" />

    <div class="p-6 max-w-3xl mx-auto bg-white shadow-md rounded-lg">

        <h1 class="text-xl font-bold mb-4">Buscar, Modificar o Eliminar Habilitación</h1>

        <div v-if="showSuccess" class="bg-green-200 text-green-800 px-4 py-3 rounded mb-4">
            Operación realizada correctamente.
        </div>

        <div v-if="errorMessage" class="bg-red-200 text-red-800 px-4 py-3 rounded mb-4">
            {{ errorMessage }}
        </div>

        <!-- Buscar por RUT -->
        <div class="flex gap-4 mb-6">
            <input
                v-model="form.rut_alumno"
                type="number"
                placeholder="RUT alumno"
                class="border p-2 w-full rounded"
            >

            <button
                @click="buscarPorRut"
                class="bg-blue-600 text-white px-4 py-2 rounded"
            >
                Buscar
            </button>
        </div>

        <!-- Si encuentra algo -->
        <div v-if="habilitacionEncontrada" class="border-t pt-6">

            <div class="grid grid-cols-1 gap-4">

                <div>
                    <label class="text-sm text-gray-700">Tipo Habilitación</label>
                    <select v-model="form.tipo_habilitacion" class="border p-2 w-full rounded">
                        <option value="Proyecto Ingeniería">Proyecto Ingeniería</option>
                        <option value="Proyecto Investigación">Proyecto Investigación</option>
                        <option value="Práctica Tutelada">Práctica Tutelada</option>
                    </select>
                </div>

                <div>
                    <label class="text-sm text-gray-700">Semestre Inicio</label>
                    <input v-model="form.semestre_inicio" type="number" class="border p-2 w-full rounded">
                </div>

                <div>
                    <label class="text-sm text-gray-700">Descripción</label>
                    <textarea v-model="form.descripcion" rows="3" class="border p-2 w-full rounded"></textarea>
                </div>

            </div>

            <div class="flex gap-4 mt-6 justify-end">

                <button
                    @click="modificar"
                    class="bg-green-600 text-white px-4 py-2 rounded"
                >
                    Guardar Cambios
                </button>

                <button
                    @click="eliminar"
                    class="bg-red-600 text-white px-4 py-2 rounded"
                >
                    Eliminar
                </button>
            </div>

        </div>

    </div>
</template>
