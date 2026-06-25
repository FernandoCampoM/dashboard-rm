@echo off
SET SERVICE_EXE=RMPAYCalendarservice.exe
SET SERVICE_NAME=RMPAYCALENDAR

echo.
echo =======================================================
echo  Automatizando detencion y desinstalacion del servicio
echo =======================================================
echo.

REM 1. VERIFICACION Y DETENCION DEL SERVICIO
echo 1. Deteniendo el servicio %SERVICE_NAME%...
sc query "%SERVICE_NAME%" | find "STATE"
REM Detiene el servicio si está corriendo
%SERVICE_EXE% stop

REM Pausa para asegurar que la detención se complete
timeout /t 5 /nobreak > nul

REM 2. DESINSTALAR EL SERVICIO
echo 2. Desinstalando el servicio...
%SERVICE_EXE% uninstall

REM 3. VERIFICACION FINAL (Opcional)
echo 3. Verificando que el servicio ya no existe...
sc query "%SERVICE_NAME%" 
echo.
echo Proceso finalizado. El servicio ya no deberia aparecer en "services.msc".
echo.

pause