@echo off
title MicroarreglosDNA
color 9
cls
if exist "%TEMP%\MicroDNA.tmp" (
    echo MicroarreglosDNA se encuentra ya funcionando, presione cualquier tecla para salir...
    echo .
    set /p X=MicroarreglosDNA is already running, press any key to exit...
    exit /b 1
)
copy NUL "%TEMP%\MicroDNA.tmp"
echo Actualizando e iniciando MicroarreglosDNA...
echo .
echo Updating and starting MicroarreglosDNA...
echo .
echo .
echo .
cd C:\xampp\htdocs\MicroarreglosDNA
git reset --hard origin/master
start /b C:\xampp\xampp_start.exe
start ""  http://localhost/MicroarreglosDNA/
cls
echo MicroarreglosDNA activo, presione cualquier tecla para terminar el programa...
echo .
set /p X=MicroarreglosDNA running, press any key to finish the program...
cls
echo Terminando y cerrando los servicios de MicroarreglosDNA...
echo .
START /b C:\xampp\xampp_stop.exe
del "%TEMP%\MicroDNA.tmp"