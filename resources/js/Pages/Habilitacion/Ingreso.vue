<script setup>
import { reactive, ref, computed, onMounted, watch } from 'vue';
import axios from 'axios';
import { Head } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'



// --- ESTADOS REACTIVOS ---
const form = reactive({
    id_habilitacion: '',
    modalidad: 'PrIng',
    alumno_id: '',
    alumno_nombre: '',
    profesor_dinf_id: '',
    profesor_dinf_rut: '',
    comision_profesor_id: '',
    comision_profesor_rut: '',
    co_guia_id: '',
    co_guia_rut: '',
    profesor_tutor_id: '',
    profesor_tutor_rut: '',
    semestre_inicio: '',
    titulo: '',
    descripcion_proyecto: '',
    empresa_nombre: '',
    supervisor_empresa: '',
    descripcion_practica: '',
});

const showSuccess = ref(false);
const errorMessage = ref('');
const alumnos = ref([]);
const profesores = ref([]);
const errorAlumno = ref('');

// FILTROS
const profesoresDINF = computed(() =>
    profesores.value.filter(p => (p.departamento || '').toUpperCase().trim() === 'DINF')
);

const profesoresCoGuia = computed(() => profesores.value);

// Computeds de tipo de modalidad
const isProyecto = computed(() => ['PrIng', 'PrInv'].includes(form.modalidad));
const isPractica = computed(() => form.modalidad === 'PrTut');

// --- FUNCIONES ---
const fetchUsers = async () => {
    try {
        const usersRes = await axios.get('/api/users/list');
        if (usersRes.data.alumnos && usersRes.data.profesores) {
            alumnos.value = usersRes.data.alumnos;
            profesores.value = usersRes.data.profesores;
        }

        const profRes = await axios.get('/api/profesores');
        console.log('Respuesta completa de /api/profesores:', profRes.data);
        profesores.value = profRes.data.todos || profesores.value;

        console.log('Profesores finales cargados:', profesores.value);
    } catch (err) {
        console.error('Error fetchUsers', err);
        errorMessage.value =
            'Error al cargar alumnos y profesores: ' +
            (err.response?.data?.message || err.message);
        setTimeout(() => (errorMessage.value = ''), 5000);
    }
};

watch(() => form.profesor_dinf_id, (nuevo) => {
    if (!nuevo || profesores.value.length === 0) {
        form.profesor_dinf_rut = '';
        return;
    }
    const prof = profesores.value.find(p =>
        p?.rut_profesor?.toString?.() === nuevo
    );
    form.profesor_dinf_rut = prof ? prof.rut_profesor : '';
});

watch(() => form.comision_profesor_id, (nuevo) => {
    if (!nuevo || profesores.value.length === 0) {
        form.comision_profesor_rut = '';
        return;
    }
    const prof = profesores.value.find(p =>
        p?.rut_profesor?.toString?.() === nuevo
    );
    form.comision_profesor_rut = prof ? prof.rut_profesor : '';
});

watch(() => form.co_guia_id, (nuevo) => {
    if (!nuevo || profesores.value.length === 0) {
        form.co_guia_rut = '';
        return;
    }
    const prof = profesores.value.find(p =>
        p?.rut_profesor?.toString?.() === nuevo
    );
    form.co_guia_rut = prof ? prof.rut_profesor : '';
});

watch(() => form.profesor_tutor_id, (nuevo) => {
    if (!nuevo || profesores.value.length === 0) {
        form.profesor_tutor_rut = '';
        return;
    }
    const prof = profesores.value.find(p =>
        p?.rut_profesor?.toString?.() === nuevo
    );
    form.profesor_tutor_rut = prof ? prof.rut_profesor : '';
});



// Seleccionar alumno
const buscarAlumnoPorRut = async () => {
    errorAlumno.value = '';
    form.alumno_nombre = '';

    if (!form.alumno_id) return;

    try {
        const res = await axios.get(`/api/alumnos/${form.alumno_id}`);
        if (res.data && res.data.name) {
            form.alumno_nombre = res.data.name;
        } else {
            errorAlumno.value = 'No se encontró un alumno con ese RUT.';
        }
    } catch (err) {
        console.error('Error al buscar alumno:', err);
        errorAlumno.value = 'Error al consultar el alumno o no existe.';
    }
};

// Reset
const resetForm = () => {
    Object.assign(form, {
        modalidad: 'PrIng',
        alumno_id: '',
        alumno_nombre: '',
        profesor_dinf_id: '',
        profesor_dinf_rut: '',
        profesor_tutor_id: '',
        profesor_tutor_rut: '',
        comision_profesor_id: '',
        comision_profesor_rut: '',
        co_guia_id: '',
        co_guia_rut: '',
        semestre_inicio: '',
        titulo: '',
        descripcion_proyecto: '',
        empresa_nombre: '',
        supervisor_empresa: '',
        descripcion_practica: '',
        id_habilitacion: '',
    });
};

// --- VALIDACIONES (sin cambios) ---
const validarNombreEmpresa = (nombre) => /^[A-Za-zÁÉÍÓÚáéíóúÑñ\s]{1,50}$/.test(nombre);
const validarTitulo = (titulo) => /^[A-Za-zÁÉÍÓÚáéíóúÑñ\s]{1,25}$/.test(titulo);
const validarDescripcion = (d) => d.length <= 350 && /^[A-Za-zÁÉÍÓÚáéíóúÑñ0-9\s.,;:¡!¿?"'()\-_/&%$#@]*$/.test(d);
const validarNombreSupervisor = (n) => /^[A-Za-zÁÉÍÓÚáéíóúÑñ\s]{1,50}$/.test(n);

// --- SUBMIT (sin cambios) ---
const submit = async () => {
    showSuccess.value = false;
    errorMessage.value = '';

    if (!form.alumno_id) return (errorMessage.value = 'Seleccione un alumno.');
    if (!form.semestre_inicio) return (errorMessage.value = 'Ingrese semestre de inicio.');

    const semestreRegex = /^\d{4}-(1|2)$/;
    if (!semestreRegex.test(form.semestre_inicio)) {
        return (errorMessage.value = 'Revise los campos: Semestre de Inicio. \n Formato inválido. Use AAAA-1 o AAAA-2.');
    }

    if (isPractica.value && !validarNombreEmpresa(form.empresa_nombre))
        return (errorMessage.value = 'Nombre Empresa inválido.');
    if (isProyecto.value && !validarTitulo(form.titulo))
        return (errorMessage.value = 'Título inválido.');
    if (isProyecto.value && !validarDescripcion(form.descripcion_proyecto))
        return (errorMessage.value = 'Descripción inválida.');
    if (isPractica.value && !validarDescripcion(form.descripcion_practica))
        return (errorMessage.value = 'Descripción inválida.');
    if (isPractica.value && !validarNombreSupervisor(form.supervisor_empresa))
        return (errorMessage.value = 'Nombre Supervisor inválido.');

    if (isProyecto.value && (!form.profesor_dinf_id || !form.comision_profesor_id || !form.titulo))
        return (errorMessage.value = 'Complete guía, comisión y título.');
    if (isPractica.value && (!form.profesor_tutor_id || !form.empresa_nombre || !form.supervisor_empresa))
        return (errorMessage.value = 'Complete tutor, empresa y supervisor.');

    try {
        let yearPart = null;
        let semPart = null;
        if (form.semestre_inicio.includes('-')) [yearPart, semPart] = form.semestre_inicio.split('-');

        const tipoHabilitacionMap = {
            PrIng: 'Proyecto de Ingeniería',
            PrInv: 'Proyecto de Investigación',
            PrTut: 'Práctica Tutelada'
        };

        const payload = {
            tipo_habilitacion: tipoHabilitacionMap[form.modalidad],
            rut_alumno: form.alumno_id ? parseInt(form.alumno_id) : null,
            alumno_nombre: form.alumno_nombre,
            semestre_inicio_anho: yearPart ? parseInt(yearPart) : null,
            semestre_inicio: semPart || null,
            descripcion: isProyecto.value ? form.descripcion_proyecto : form.descripcion_practica,
            titulo: form.titulo || null,
            profesor_guia: isProyecto.value && form.profesor_dinf_id ? parseInt(form.profesor_dinf_id) : null,
            profesor_comision: isProyecto.value && form.comision_profesor_id ? parseInt(form.comision_profesor_id) : null,
            profesor_coguia: isProyecto.value && form.co_guia_id ? parseInt(form.co_guia_id) : null,
            profesor_tutor: isPractica.value && form.profesor_tutor_id ? parseInt(form.profesor_tutor_id) : null,
            nombre_empresa: isPractica.value ? form.empresa_nombre : null,
            nombre_supervisor: isPractica.value ? form.supervisor_empresa : null,
        };

        const res = await axios.post('/api/habilitaciones', payload);
        const newId = res.data.id_habilitacion;
        errorMessage.value = `¡Habilitación ${newId} creada exitosamente!`;
        showSuccess.value = true;
        form.id_habilitacion = newId;
        setTimeout(() => {
            showSuccess.value = false;
            errorMessage.value = '';
            resetForm();
        }, 4000);
    } catch (err) {
        console.error('Error submit', err);
        const responseData = err.response?.data;
        const validationErrors = responseData?.errors;
        const backendMessage = responseData?.message || 'Ocurrió un error al guardar.';
        const backendDetail = responseData?.error_detail;

        if (validationErrors) {
            let s = 'Revise los campos:\n';
            for (const k in validationErrors) s += `- ${validationErrors[k][0]}\n`;
            errorMessage.value = s;
        } else if (backendDetail) {
            errorMessage.value = `${backendMessage}\n\nDetalle: ${backendDetail}`;
        } else {
            errorMessage.value = backendMessage;
        }
        window.scrollTo(0, 0);
    }
};

// onMounted
onMounted(() => {
    fetchUsers();
    const currentYear = new Date().getFullYear();
    const currentMonth = new Date().getMonth();
    const semester = currentMonth >= 7 ? 2 : 1;
    form.semestre_inicio = `${currentYear}-${semester}`;
});
</script>


<template>
    <AuthenticatedLayout>
    <Head title="Ingreso Habilitación" />

    <div class="p-6 max-w-4xl mx-auto bg-white rounded-lg shadow-xl">
        <h1 class="text-2xl font-bold mb-6 text-gray-800">Ingreso de Nueva Habilitación Profesional</h1>

        <div v-if="showSuccess" class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
            ¡Habilitación creada exitosamente!
        </div>
        <div v-if="errorMessage" class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4 whitespace-pre-line" role="alert">
            {{ errorMessage }}
        </div>

        <form @submit.prevent="submit">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="col-span-full border-b pb-4 mb-4">
                    <h2 class="text-xl font-semibold text-gray-700">Datos Generales</h2>
                </div>

                <div>
                    <label for="id_habilitacion" class="block text-sm font-medium text-gray-700">
                        ID Habilitación
                    </label>
                    <input
                        type="text"
                        id="id_habilitacion"
                        v-model="form.id_habilitacion"
                        readonly
                        class="mt-1 block w-full border border-gray-200 bg-gray-100 rounded-md shadow-sm p-2"
                        placeholder="Se generará al guardar"
                    />
                </div>

                <div>
                    <label for="modalidad" class="block text-sm font-medium text-gray-700">Tipo de Habilitación</label>
                    <select id="modalidad" v-model="form.modalidad" required class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2">
                        <option disabled value="">Seleccione una opción</option>
                        <option value="PrIng">Proyecto de Ingeniería</option>
                        <option value="PrInv">Proyecto de Investigación</option>
                        <option value="PrTut">Práctica Tutelada</option>
                    </select>
                </div>

                <div>
                    <label for="semestre" class="block text-sm font-medium text-gray-700">Semestre de Inicio</label>
                    <input type="text" id="semestre" v-model="form.semestre_inicio" required class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2" placeholder="Ej: 2025-S1">
                </div>

                <div>
                    <label for="alumno_rut" class="block text-sm font-medium text-gray-700">RUT Alumno(a)</label>
                    <input
                        type="text"
                        id="alumno_rut"
                        v-model="form.alumno_id"
                        @blur="buscarAlumnoPorRut"
                        required
                        placeholder="Ej: 12345678"
                        class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2"
                    />
                    <p v-if="errorAlumno" class="text-red-600 text-sm mt-1">{{ errorAlumno }}</p>
                </div>

                <div>
                    <label for="alumno_nombre" class="block text-sm font-medium text-gray-700">Nombre Alumno(a)</label>
                    <input type="text" id="alumno_nombre" v-model="form.alumno_nombre" readonly class="mt-1 block w-full border border-gray-200 bg-gray-100 rounded-md shadow-sm p-2">
                </div>
            </div>

            <!-- 🔹 Sección Proyecto -->
            <div v-if="isProyecto" class="mt-8 border p-4 rounded-lg bg-indigo-50">
                <h2 class="text-xl font-semibold mb-4 text-indigo-800">Datos del Proyecto (Ingeniería / Investigación)</h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Profesor Guía -->
                    <div>
                        <label for="profesor_guia" class="block text-sm font-medium text-gray-700">Profesor Guía DINF</label>
                        <select id="profesor_guia" v-model="form.profesor_dinf_id" required class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2">
                            <option disabled value="">Seleccione un profesor</option>
                            <option v-for="p in profesoresDINF" :key="p.rut_profesor" :value="p.rut_profesor.toString()">
                                {{ p.nombre_profesor }}
                            </option>
                        </select>
                        <input type="text" v-model="form.profesor_dinf_rut" readonly placeholder="RUT profesor guía"
                            class="mt-2 block w-full border border-gray-200 bg-gray-100 rounded-md shadow-sm p-2" />
                    </div>

                    <!-- Profesor Comisión -->
                    <div>
                        <label for="comision_profesor" class="block text-sm font-medium text-gray-700">Profesor Comisión</label>
                        <select id="comision_profesor" v-model="form.comision_profesor_id" required class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2">
                            <option disabled value="">Seleccione un profesor</option>
                            <option v-for="p in profesoresDINF" :key="p.rut_profesor" :value="p.rut_profesor.toString()">
                                {{ p.nombre_profesor }}
                            </option>
                        </select>
                        <input type="text" v-model="form.comision_profesor_rut" readonly placeholder="RUT profesor comisión"
                            class="mt-2 block w-full border border-gray-200 bg-gray-100 rounded-md shadow-sm p-2" />
                    </div>

                    <!-- Co-Guía -->
                    <div>
                        <label for="co_guia" class="block text-sm font-medium text-gray-700">Co-Guía (Opcional)</label>
                        <select id="co_guia" v-model="form.co_guia_id" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2">
                            <option value="">Ninguno</option>
                            <option v-for="p in profesoresCoGuia" :key="p.rut_profesor" :value="p.rut_profesor.toString()">
                                {{ p.nombre_profesor }}
                            </option>
                        </select>
                        <input type="text" v-model="form.co_guia_rut" readonly placeholder="RUT co-guía"
                            class="mt-2 block w-full border border-gray-200 bg-gray-100 rounded-md shadow-sm p-2" />
                    </div>

                    <div class="col-span-full">
                        <label for="titulo" class="block text-sm font-medium text-gray-700">Título del Proyecto</label>
                        <input type="text" id="titulo" v-model="form.titulo" required class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2">
                    </div>

                    <div class="col-span-full">
                        <label for="descripcion_proyecto" class="block text-sm font-medium text-gray-700">Descripción/Objetivo del Proyecto</label>
                        <textarea id="descripcion_proyecto" v-model="form.descripcion_proyecto" rows="3" required class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2"></textarea>
                    </div>
                </div>
            </div>

            <!-- 🔹 Sección Práctica -->
            <div v-if="isPractica" class="mt-8 border p-4 rounded-lg bg-yellow-50">
                <h2 class="text-xl font-semibold mb-4 text-yellow-800">Datos de la Práctica Tutelada</h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Profesor Tutor -->
                    <div>
                        <label for="profesor_tutor" class="block text-sm font-medium text-gray-700">Profesor Tutor DINF</label>
                        <select id="profesor_tutor" v-model="form.profesor_tutor_id" required class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2">
                            <option disabled value="">Seleccione un profesor</option>
                            <option v-for="p in profesoresDINF" :key="p.rut_profesor" :value="p.rut_profesor.toString()">
                                {{ p.nombre_profesor }}
                            </option>
                        </select>
                        <input type="text" v-model="form.profesor_tutor_rut" readonly placeholder="RUT profesor tutor"
                            class="mt-2 block w-full border border-gray-200 bg-gray-100 rounded-md shadow-sm p-2" />
                    </div>

                    <div>
                        <label for="empresa_nombre" class="block text-sm font-medium text-gray-700">Nombre de la Empresa</label>
                        <input type="text" id="empresa_nombre" v-model="form.empresa_nombre" required class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2">
                    </div>

                    <div>
                        <label for="supervisor_empresa" class="block text-sm font-medium text-gray-700">Nombre Supervisor Empresa</label>
                        <input type="text" id="supervisor_empresa" v-model="form.supervisor_empresa" required class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2">
                    </div>

                    <div class="col-span-full">
                        <label for="descripcion_practica" class="block text-sm font-medium text-gray-700">Descripción de la Práctica</label>
                        <textarea id="descripcion_practica" v-model="form.descripcion_practica" rows="3" required class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2"></textarea>
                    </div>
                </div>
            </div>

            <div class="mt-6 pt-4 border-t flex justify-end">
                <button type="submit" class="px-6 py-2 bg-blue-600 text-white font-semibold rounded-md shadow-md hover:bg-blue-700 transition duration-150">
                    Guardar Habilitación
                </button>
            </div>
        </form>
    </div>
    </AuthenticatedLayout>
</template>
