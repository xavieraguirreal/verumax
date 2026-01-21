@echo off
echo ========================================
echo Reiniciando Apache en XAMPP
echo ========================================
echo.

echo Deteniendo Apache...
C:\xampp\apache\bin\httpd.exe -k stop
timeout /t 3 /nobreak > nul

echo Iniciando Apache...
C:\xampp\apache\bin\httpd.exe -k start
timeout /t 2 /nobreak > nul

echo.
echo ========================================
echo Apache reiniciado
echo ========================================
echo.
echo Ahora puedes acceder a:
echo http://localhost/setup_local_databases.php
echo.
pause
