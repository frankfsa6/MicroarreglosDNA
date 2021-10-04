@echo off
title Instalador MicroarreglosDNA
color 9
chcp 65001
mode 80,20
echo MsgBox "Bienvenido a MicroarreglosDNA. Enseguida, se va dirigir a la 'Guia de instalacion'. Siga la parte dedicada a Windows para instalar correctamente el programa. Este archivo ejecutable lo redirige a los enlaces descritos en el documento.", 64, "Instalador Microarreglos DNA">%temp%\InstaDNA.vbs
cscript %temp%\InstaDNA.vbs
start ""  "https://github.com/frankfsa6/MicroarreglosDNA/blob/master/Instalador.pdf"
echo MsgBox "Paso 1) Descargar e instalar el servidor local: XAMPP. Enseguida, se va a dirigir al enlace web para su descarga. Instale el programa y al finalizar regrese a esta ventana. Siga la 'Guia de instalacion' para detalles del proceso.", 64, "Instalador Microarreglos DNA">%temp%\InstaDNA.vbs
cscript %temp%\InstaDNA.vbs
start ""  "https://www.apachefriends.org/es/download.html"
echo MsgBox "Paso 2) Descargar e instalar el gestor de versiones del programa: Git. Enseguida, se va a dirigir al enlace web para su descarga. Instale el programa y al finalizar regrese a esta ventana. Siga la 'Guia de instalacion' para detalles del proceso.", 64, "Instalador Microarreglos DNA">%temp%\InstaDNA.vbs
cscript %temp%\InstaDNA.vbs
start ""  "https://git-scm.com/downloads"
echo MsgBox "Paso 3) Verificar el programa principal: MicroarreglosDNA. Finalmente, se va a instalar el programa principal con sus datos actualizados. Siga la 'Guia de instalacion' para detalles del proceso.", 64, "Instalador Microarreglos DNA">%temp%\InstaDNA.vbs
cscript %temp%\InstaDNA.vbs
cd "C:\xampp\htdocs\"
git clone "https://github.com/frankfsa6/MicroarreglosDNA.git"
del "%temp%\InstaDNA.vbs" 
start /b ""  "C:\xampp\htdocs\MicroarreglosDNA\Windows\MicroarreglosDNA.bat"
exit /b 1