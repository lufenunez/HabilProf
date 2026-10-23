@echo off
REM Directorio del proyecto: la carpeta donde está este .bat
set PROJECT_DIR=%~dp0
REM Ejecutable de PHP: usa el del PATH, o define PHP_EXE antes de ejecutar
if not defined PHP_EXE set PHP_EXE=php
set LOG_FILE=%PROJECT_DIR%scheduler.log

REM 1. Navegar al directorio (fundamental)
cd /d "%PROJECT_DIR%"

echo. >> "%LOG_FILE%"
echo --- SCHEDULER INICIADO %date% %time% --- >> "%LOG_FILE%"

REM Redirige todas las salidas/errores de PHP al log.
"%PHP_EXE%" -d display_errors=stderr artisan schedule:run >> "%LOG_FILE%" 2>&1

echo --- SCHEDULER FINALIZADO --- >> "%LOG_FILE%"
