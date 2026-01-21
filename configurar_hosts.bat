@echo off
echo ========================================
echo Configurar archivo HOSTS para VERUMax
echo ========================================
echo.
echo IMPORTANTE: Este script requiere permisos de ADMINISTRADOR
echo.
echo Se agregaran las siguientes lineas al archivo hosts:
echo   127.0.0.1  verumax.local
echo   127.0.0.1  www.verumax.local
echo   127.0.0.1  sajur.verumax.local
echo   127.0.0.1  liberte.verumax.local
echo.
pause

REM Verificar permisos de administrador
net session >nul 2>&1
if %errorLevel% neq 0 (
    echo.
    echo ERROR: Este script debe ejecutarse como ADMINISTRADOR
    echo.
    echo Haz clic derecho en el archivo y selecciona "Ejecutar como administrador"
    echo.
    pause
    exit /b 1
)

REM Hacer backup del archivo hosts
echo.
echo Creando backup de hosts...
copy C:\Windows\System32\drivers\etc\hosts C:\Windows\System32\drivers\etc\hosts.backup.%date:~-4,4%%date:~-7,2%%date:~-10,2% >nul

REM Verificar si ya existen las entradas
findstr /C:"verumax.local" C:\Windows\System32\drivers\etc\hosts >nul
if %errorLevel% equ 0 (
    echo.
    echo Las entradas de verumax.local ya existen en el archivo hosts.
    echo Si quieres reconfigurar, edita manualmente: C:\Windows\System32\drivers\etc\hosts
    echo.
    pause
    exit /b 0
)

REM Agregar entradas al archivo hosts
echo.
echo Agregando entradas al archivo hosts...
echo. >> C:\Windows\System32\drivers\etc\hosts
echo # ============================================================================ >> C:\Windows\System32\drivers\etc\hosts
echo # VERUMax - Desarrollo Local >> C:\Windows\System32\drivers\etc\hosts
echo # ============================================================================ >> C:\Windows\System32\drivers\etc\hosts
echo 127.0.0.1  verumax.local >> C:\Windows\System32\drivers\etc\hosts
echo 127.0.0.1  www.verumax.local >> C:\Windows\System32\drivers\etc\hosts
echo 127.0.0.1  sajur.verumax.local >> C:\Windows\System32\drivers\etc\hosts
echo 127.0.0.1  liberte.verumax.local >> C:\Windows\System32\drivers\etc\hosts

echo.
echo ========================================
echo Configuracion completada exitosamente
echo ========================================
echo.
echo Dominios locales configurados:
echo   - http://verumax.local
echo   - http://sajur.verumax.local
echo   - http://liberte.verumax.local
echo.
echo Backup creado en:
echo   C:\Windows\System32\drivers\etc\hosts.backup.%date:~-4,4%%date:~-7,2%%date:~-10,2%
echo.
echo IMPORTANTE: Reinicia Apache en XAMPP Control Panel
echo.
pause
