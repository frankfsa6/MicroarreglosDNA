@echo off
title Instalador MicroarreglosDNA
color 9
chcp 65001
mode 80,20
cls
if exist "%TEMP%\InstaladorMicroDNA.tmp" (
    echo .
    echo .
    echo  MicroarreglosDNA ya se está instalando...
    timeout 3
    exit /b 1
)
copy NUL "%TEMP%\InstaladorMicroDNA.tmp" 
cls
echo .
echo .
echo  Instalador MicroarreglosDNA 
echo .
echo  Bienvenido a MicroarreglosDNA versión Windows.
echo  A continuación, se abrirá el enlace web para seguir la 'Guía de Instalación'.
echo  Siga la sección dedicada a Windows para instalar correctamente el programa.
echo  Este archivo ejecutable lo guiará a los enlaces descritos en el documento.
echo  Presione cualquier tecla para abrir el enlace...
pause
start ""  "https://github.com/frankfsa6/MicroarreglosDNA/blob/master/Guía de instalación.pdf"
cls
echo .
echo .
echo  Instalador MicroarreglosDNA 
echo .
echo  PASO 1) Descargar e instalar servidor local: XAMPP.
echo .
echo  A continuación, se abrirá el enlace web para descargar la aplicación. 
echo  Es recomendable seguir la Guía de Instalación, en la sección de Windows.
echo  Instale el programa y al finalizar, regrese a esta ventana.
echo  Presione cualquier tecla para abrir el enlace...
pause
start ""  "https://www.apachefriends.org/es/download.html"
cls
echo .
echo .
echo  Instalador MicroarreglosDNA
echo .
echo  PASO 2) Descargar e instalar el gestor de versiones del programa: Git
echo .
echo  Enseguida, se abrirá el enlace web para descargar la aplicación. 
echo  Es recomendable seguir la Guía de Instalación, en la sección de Windows.
echo  Instale el programa y al finalizar, regrese a esta ventana.
echo  Presione cualquier tecla para abrir el enlace...
pause
start ""  "https://git-scm.com/downloads"
cls
echo .
echo .
echo  Instalador MicroarreglosDNA
echo .
echo  PASO 3) Descargar e instalar el programa principal: MicroarreglosDNA
echo .
echo  Enseguida, se realizará la instalación del programa en su versión actual. 
echo  De haberse instalado los programas anteriores correctamente, 
echo  el navegador abrirá la página con el programa instalado al terminar.
echo  Presione cualquier tecla para continuar la instalación...
pause
cd "C:\xampp\htdocs\"
git clone "https://github.com/frankfsa6/MicroarreglosDNA.git"
start ""  "C:\xampp\htdocs\MicroarreglosDNA\Windows\MicroarreglosDNA.bat"
cls
echo .
echo .
echo  Instalador MicroarreglosDNA
echo .
echo  Ha finalizado la instalación de MicroarreglosDNA.
echo  En su escritorio se instaló el archivo ejecutable para su próximo uso.
echo  Seguiremos mejorando el programa, gracias por su apoyo. 
echo  Presione cualquier tecla para cerrar el instalador...
pause
del "%TEMP%\InstaladorMicroDNA.tmp" 
exit /b 1