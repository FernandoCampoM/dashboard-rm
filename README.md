# RMPAY Dashboard

Este repositorio contiene el dashboard para la plataforma RMPAY.

---

## 🚀 Ejecución del Servicio del Calendario (Windows)
**Nota:** La Ejecucion de este servicio es necesario para el correcto fucnionamiento del Dashboard
Para poner en marcha el servicio de calendario en tu sistema, sigue estos sencillos pasos:

1.  **Navega a la carpeta de instalación:**
    Abre tu explorador de archivos o la línea de comandos y dirígete al directorio principal del servicio:

    ```bash
    cd RMPAY-CALENDARSERVICE
    ```

2.  **Instala y Ejecuta el Servicio:**
    Ejecuta el script por lotes (`.bat`) para registrar y arrancar el servicio de calendario en Windows.

    ```bash
    .\install.bat
    ```

**Nota:** Este archivo `install.bat` está configurado para usar **WinSW** y el **JDK** ubicado en el directorio `%BASE%` (la misma carpeta del servicio, no borrar ningun de esos archivos).

---

