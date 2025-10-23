@echo off
SET SERVICE_EXE=RMPAYCalendarservice.exe
SET SERVICE_NAME=RMPAYCALENDAR

echo.
echo ===================================================
echo  Automatizando re-instalacion e inicio del servicio
echo ===================================================
echo.

REM 0. INTENTAR DESINSTALAR LA VERSION EXISTENTE (Limpieza)
echo 0. Intentando DETENER y DESINSTALAR el servicio anterior...
REM Intentar detener primero (solo funciona si existe y esta corriendo)
%SERVICE_EXE% stop > nul 2>&1
timeout /t 3 /nobreak > nul
REM Intentar desinstalar (solo funciona si existe)
%SERVICE_EXE% uninstall > nul 2>&1
timeout /t 3 /nobreak > nul
echo    * Limpieza previa completada.

REM 1. INSTALAR EL SERVICIO
echo 1. Instalando el nuevo servicio...
%SERVICE_EXE% install

REM Pausa para asegurar que el registro se complete
timeout /t 5 /nobreak > nul

REM 2. INICIAR EL SERVICIO
echo 2. Iniciando el servicio...
%SERVICE_EXE% start

REM 3. VERIFICACION (Opcional, pero util)
echo 3. Verificando el estado del servicio...
sc query "%SERVICE_NAME%" | find "STATE"
echo.
echo Proceso de RE-INSTALACION Y ARRANQUE finalizado.
echo.
 
pause