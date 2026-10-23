<script setup>
import { Link, router } from '@inertiajs/vue3'

// Función para hacer logout
const logout = () => {
  router.post('/logout')
}
</script>

<template>
  <div class="min-h-screen bg-gray-100">

    <!-- BARRA SUPERIOR -->
    <nav class="bg-white border-b shadow p-4 flex justify-between items-center">
      
      <!-- Logo o Nombre App -->
      <div class="text-lg font-bold text-gray-800">
        Mi App
      </div>

      <!-- Botones de navegación -->
      <div class="flex items-center space-x-4">
        <!-- Botón Listados -->
      <Link
        :href="route('habilitacion.listado')"
        class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 transition"
      >
        Listados
      </Link>
      <!-- Botón Modificar / Eliminar -->
      <Link
        :href="route('habilitacion.modificar')"
        class="inline-flex items-center px-4 py-2 bg-yellow-600 text-white rounded hover:bg-yellow-700 transition"
      >
        Modificar / Eliminar
      </Link>

        <!-- Logout -->
        <button 
          @click="logout"
          class="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600 transition"
        >
          Logout
        </button>
      </div>
    </nav>

    <!-- CONTENIDO PRINCIPAL -->
    <main class="p-6">
      <slot />
    </main>

  </div>
</template>
