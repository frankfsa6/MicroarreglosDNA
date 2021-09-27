@echo off
title MicroarreglosDNA
color 9
chcp 65001
mode 80,20
cls
if exist "%TEMP%\MicroDNA.tmp" (
    echo .
    echo .
    echo  MicroarreglosDNA ya está funcionando...
    timeout 3
    exit /b 1
)
echo .
echo .
echo  Iniciando MicroarreglosDNA...
copy NUL "%TEMP%\MicroDNA.tmp"
cd "C:\xampp\htdocs\MicroarreglosDNA"
git reset --hard origin/master
git pull
mkdir "%userprofile%\Desktop\MicroarreglosDNA"
copy /y "C:\xampp\htdocs\MicroarreglosDNA\Windows\MicroarreglosDNA.bat" "%userprofile%\Desktop\MicroarreglosDNA\"
copy /y "C:\xampp\htdocs\MicroarreglosDNA\Windows\*.url" "%userprofile%\Desktop\MicroarreglosDNA\"
start /b C:\xampp\xampp_start.exe
start ""  "http://localhost/MicroarreglosDNA/"
cls
echo .
echo .
echo  MicroarreglosDNA ya está funcionando, presione cualquier tecla para terminar...
pause
cls
echo .
echo .
echo  Terminando MicroarreglosDNA, esta ventana se cerrará automáticamente...
START /b C:\xampp\xampp_stop.exe
del "%TEMP%\MicroDNA.tmp"
exit /b 1