@echo off
echo ========================================
echo Activando Configuracion LOCAL
echo ========================================
echo.

cd /d E:\appVerumax

REM Backup del .env actual si existe
if exist .env (
    echo Creando backup de .env actual...
    copy .env .env.backup > nul
)

REM Activar configuracion local
echo Activando .env.local...
copy .env.local .env > nul

echo.
echo ========================================
echo Configuracion LOCAL activada
echo ========================================
echo.
echo Credenciales configuradas:
echo - Host: localhost
echo - Usuario: root
echo - Password: [vacio]
echo.
echo Bases de datos:
echo - verumax_general
echo - verumax_certifi
echo - verumax_identi
echo.
pause
