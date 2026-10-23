<script setup>
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <div class="p-8">
        <h1 class="text-2xl font-bold mb-6">Listados</h1>

        <div class="space-y-4">

            <Link
                :href="route('listado.semestre')"
                class="bg-blue-600 text-white px-4 py-2 rounded"
            >
                Listado por Semestre
            </Link>

            <Link
                :href="route('listado.profesor')"
                class="bg-green-600 text-white px-4 py-2 rounded"
            >
                Listado Histórico por Profesor
            </Link>

        </div>
    </div>
</template>
