# HabilProf

Sistema web para gestionar las **Habilitaciones Profesionales** de los alumnos del Departamento de Ingeniería Informática (DINF). Lo desarrollé como proyecto para el ramo **Taller de Ingeniería de Software** en la universidad.

La aplicación permite a los profesores registrar, consultar, modificar y eliminar las habilitaciones de los alumnos (proyectos de título y prácticas), y genera listados por semestre y por profesor. Además sincroniza automáticamente alumnos, profesores y notas desde una base de datos externa de la universidad.

## Funcionalidades

- **Inicio de sesión** con usuario y contraseña. Cada cuenta está asociada al RUT de un profesor.
- **Ingreso de habilitaciones** de tres tipos:
  - **Proyecto de Ingeniería** y **Proyecto de Investigación**: título, profesor guía, profesor de comisión y co-guía (opcional).
  - **Práctica Tutelada**: profesor tutor, empresa y supervisor de la empresa.
- **Búsqueda, modificación y eliminación** de una habilitación a partir del RUT del alumno.
- **Listados**:
  - Habilitaciones por semestre.
  - Histórico de habilitaciones por profesor.
- **Sincronización automática** (`sync:universidad`): copia alumnos, profesores y notas finales desde la base de datos de origen de la universidad, validando RUT, nombres, departamento (`DINF` / `Externo`), notas (1.0 a 7.0) y fechas.

### Reglas de negocio

- Un alumno debe existir en la base de datos para registrarle una habilitación.
- Cada alumno puede tener **una sola** habilitación.
- Los IDs de habilitación son correlativos de 6 dígitos (`000001` a `999999`).
- El registro se hace dentro de una transacción: si algo falla, no se guarda nada.

## Tecnologías

| Capa | Herramientas |
|---|---|
| Backend | PHP 8.2+, Laravel 12, Laravel Breeze, Sanctum |
| Frontend | Vue 3, Inertia.js, Tailwind CSS, Vite |
| Base de datos | PostgreSQL (una base de destino y una de origen para la sincronización) |

## Estructura del proyecto

```
app/
├── Console/Commands/SyncDatosUniversidad.php   # Comando de sincronización
├── Http/Controllers/
│   ├── HabilitacionController.php              # CRUD de habilitaciones
│   ├── ListadoController.php                   # Listados por semestre y profesor
│   ├── AlumnoController.php                    # Búsqueda de alumnos por RUT
│   └── Api/UserSyncController.php              # Listado de alumnos y profesores
└── Models/                                     # Alumno, Profesor, Habilitacion, Supervisa, User
resources/js/Pages/Habilitacion/                # Vistas en Vue (Ingreso, Listados, Modificar/Eliminar)
routes/
├── web.php                                     # Rutas de las páginas
└── api.php                                     # Endpoints JSON
run_sync_universidad.php                        # Ejecuta la sincronización cada 60 segundos
run_scheduler.bat                               # Ejecuta el scheduler de Laravel en Windows
```

## Modelo de datos

Las tablas del dominio están en PostgreSQL:

- `alumno` (`rut_alumno`, `nombre_alumno`)
- `profesor` (`rut_profesor`, `nombre_profesor`, `departamento`)
- `habilitacion` (`id_habilitacion`, `rut_alumno`, `semestre_inicio_anho`, `semestre_inicio`, `descripcion`, `tipo_habilitacion`, `nota_final`, `fecha_nota`)
- `proyecto_ingenieria` / `proyecto_investigacion` (`id_habilitacion`, `titulo`)
- `practica_tutelada` (`id_habilitacion`, `nombre_empresa`, `nombre_supervisor`)
- `supervisa` (`rut_profesor`, `id_habilitacion`, `tipo_profesor`: guía, comisión, co-guía o tutor)
- `users` (`usuario`, `password`, `rut_profesor`, `name`)

La base de origen de la universidad contiene las tablas `sistema_alumnos`, `sistema_profesores` y `notas_en_linea`.

> **Nota:** en el proyecto original las tablas del dominio se crearon directamente en PostgreSQL. La migración `database/migrations/2025_01_01_000000_create_habilprof_tables.php` se reconstruyó a partir del código, así que `php artisan migrate` crea todo. Puede no ser idéntica al esquema original.

## Instalación

Requisitos: PHP 8.2+, Composer, Node.js y PostgreSQL.

```bash
# 1. Instalar dependencias
composer install
npm install

# 2. Configurar el entorno
cp .env.example .env
php artisan key:generate
```

Edita `.env` con los datos de las dos bases de datos (`.env.example` ya trae esta estructura):

```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=habilprof
DB_USERNAME=postgres
DB_PASSWORD=

# Base de datos de origen (sistema de la universidad)
DB_SOURCE_HOST=127.0.0.1
DB_SOURCE_PORT=5432
DB_SOURCE_DATABASE=universidad
DB_SOURCE_USERNAME=postgres
DB_SOURCE_PASSWORD=
```

```bash
# 3. Crear las tablas (las de Laravel y las del dominio)
php artisan migrate

# 4. Levantar el servidor, Vite y la sincronización al mismo tiempo
npm run dev:start
```

La aplicación queda disponible en `http://localhost:8000`.

## Sincronización con la universidad

Hay tres formas de ejecutar la sincronización:

```bash
# Una sola vez
php artisan sync:universidad

# En bucle, cada 60 segundos
php run_sync_universidad.php

# Con el scheduler de Laravel (programado cada minuto)
php artisan schedule:run
```

En Windows, `run_scheduler.bat` sirve para lanzar el scheduler desde el Programador de tareas. Usa la carpeta donde está el archivo y el `php` del PATH. Si PHP no está en el PATH, define la variable `PHP_EXE` con la ruta al ejecutable. La salida queda en `scheduler.log`.

## API

| Método | Ruta | Descripción |
|---|---|---|
| `GET` | `/api/habilitaciones/next-id` | Siguiente ID de habilitación disponible |
| `POST` | `/api/habilitaciones` | Registrar una habilitación |
| `GET` | `/api/habilitaciones/rut/{rut}` | Habilitaciones de un alumno |
| `PUT` | `/api/habilitaciones/{id}` | Modificar una habilitación |
| `DELETE` | `/api/habilitaciones/{id}` | Eliminar una habilitación |
| `GET` | `/api/alumnos/{rut}` | Buscar un alumno por RUT |
| `GET` | `/api/profesores` | Profesores del DINF y lista completa |
| `GET` | `/api/users/list` | Alumnos y profesores registrados |

## Contexto

Proyecto académico del ramo Taller de Ingeniería de Software. Lo subo a GitHub como registro de lo que hice, no está pensado para producción: por ejemplo, los endpoints de la API no tienen autenticación.

## Autor

**Luis Felipe Núñez Muñoz**
