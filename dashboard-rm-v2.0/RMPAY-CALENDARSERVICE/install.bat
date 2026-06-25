@echo off
SET SERVICE_EXE=RMPAYCalendarservice.exe
SET SERVICE_NAME=RMPAYCALENDAR
SET JAVA_ZIP_FILE=jdk-20.zip
SET JAVA_INSTALL_DIR=jdk-20

echo.
echo ===================================================
echo  Automatizando re-instalacion e inicio del servicio
echo ===================================================
echo.

REM 0. LIMPIEZA PREVIA
echo 0. Intentando DETENER y DESINSTALAR el servicio anterior...
%SERVICE_EXE% stop > nul 2>&1
timeout /t 3 /nobreak > nul
%SERVICE_EXE% uninstall > nul 2>&1
timeout /t 3 /nobreak > nul
echo    * Limpieza previa del servicio completada.

REM ----------------------------------------------------------------------------------
REM SECCIÓN CONDICIONAL: DESCOMPRIMIR JAVA SOLO SI NO EXISTE LA CARPETA
REM ----------------------------------------------------------------------------------
echo.
echo ** Verificando JDK 20 **
IF NOT EXIST "%JAVA_INSTALL_DIR%" (
    
    echo 0.5. Directorio "%JAVA_INSTALL_DIR%" NO encontrado. Procediendo a descomprimir Java.
    echo 0.6. Descomprimiendo %JAVA_ZIP_FILE% en ./%JAVA_INSTALL_DIR%...
    REM El parametro -C "%JAVA_INSTALL_DIR%" desempaca en la subcarpeta jdk-20
    tar -xf "%JAVA_ZIP_FILE%" -C "."
    REM Verificacion de error de tar
    if errorlevel 1 (
        echo.
        echo ERROR: Fallo la descompresion del JDK. Verifica el archivo %JAVA_ZIP_FILE%.
        echo.
        pause
        exit /b 1
    )
    echo    * JDK 20 descomprimido exitosamente.

) ELSE (

    echo 0.5. Directorio "%JAVA_INSTALL_DIR%" encontrado. Saltando la descompresion del JDK.
    
)
echo.
REM ----------------------------------------------------------------------------------

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