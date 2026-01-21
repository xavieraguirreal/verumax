@echo off
echo ========================================
echo Activando Configuracion REMOTA
echo ========================================
echo.

cd /d E:\appVerumax

REM Backup del .env actual si existe
if exist .env (
    echo Creando backup de .env actual...
    copy .env .env.backup > nul
)

REM Activar configuracion remota
echo Activando .env.remoto...
copy .env.remoto .env > nul

echo.
echo ========================================
echo Configuracion REMOTA activada
echo ========================================
echo.
echo Credenciales configuradas:
echo - Host: localhost (servidor remoto)
echo - Usuario: verumax_*
echo - Password: [configurado]
echo.
echo Bases de datos:
echo - verumax_general
echo - verumax_certifi
echo - verumax_identi
echo.
echo RECUERDA: Subir los archivos al servidor via FileZilla
echo.
pause
