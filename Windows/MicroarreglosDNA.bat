@echo off
title MicroarreglosDNA
color 9
cls
if exist "%TEMP%\MicroDNA.tmp" (
    echo MicroarreglosDNA se encuentra ya funcionando, presione enter para salir...
    echo .
    set /p X=MicroarreglosDNA is already running, press Enter to exit...
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
git pull
start /b C:\xampp\xampp_start.exe
start ""  http://localhost/MicroarreglosDNA/
cls
echo MicroarreglosDNA activo, presione enter para terminar el programa...
echo .
set /p X=MicroarreglosDNA running, press Enter to finish the program...
cls
echo Terminando y cerrando los servicios de MicroarreglosDNA...
echo .
START /b C:\xampp\xampp_stop.exe
del "%TEMP%\MicroDNA.tmp"
echo MsgBox "El servicio se ha detenido de forma exitosa.", 64, "Se detuvo el servicio de Microarreglos" >%temp%\mensaje.vbs
start %temp%\mensaje.vbs
