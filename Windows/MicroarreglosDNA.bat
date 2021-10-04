@echo off
title MicroarreglosDNA
color 9
chcp 65001
mode 80,20
if exist "%TEMP%\MicroDNA.vbs" (
    echo .
    echo .
    echo .
    echo  MicroarreglosDNA ya está funcionando...
    timeout 3
    exit /b 1
)
echo MsgBox "Actualizando e iniciando los servicios, espere que terminen los procesos asociados.", 64, "Microarreglos DNA">%temp%\MicroDNA.vbs
start %temp%\MicroDNA.vbs
cd "C:\xampp\htdocs\MicroarreglosDNA"
git reset --hard origin/master
git pull
mkdir "%userprofile%\Desktop\MicroarreglosDNA"
copy /y "C:\xampp\htdocs\MicroarreglosDNA\Windows\MicroarreglosDNA.bat" "%userprofile%\Desktop\MicroarreglosDNA\"
copy /y "C:\xampp\htdocs\MicroarreglosDNA\Windows\*.url" "%userprofile%\Desktop\MicroarreglosDNA\"
start /b C:\xampp\xampp_start.exe
start ""  "http://localhost/MicroarreglosDNA/"
echo .
echo .
echo .
echo  MicroarreglosDNA ya está funcionando, presione cualquier tecla para terminar el programa y sus servicios...
pause
START /b C:\xampp\xampp_stop.exe
echo MsgBox "Servicios del programa finalizados.", 64, "Microarreglos DNA">%temp%\MicroDNA.vbs
cscript %temp%\MicroDNA.vbs
del "%TEMP%\MicroDNA.vbs"
exit /b 1