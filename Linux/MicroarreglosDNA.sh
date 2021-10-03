#!/bin/bash
# --- Unidad de Microarreglos DNA, IFC, UNAM, MX ---
#Actualiza de github
echo .
echo Iniciando MicroarreglosDNA...
echo .
echo .
cd /var/www/html/MicroarreglosDNA
sudo git reset --hard origin/master
sudo git pull
#Crea accesos directos
sudo mkdir ~/Desktop/MicroarreglosDNA
sudo cp -u /var/www/html/MicroarreglosDNA/Linux/MicroarreglosDNA.sh ~/Desktop/MicroarreglosDNA/
sudo cp -u /var/www/html/MicroarreglosDNA/Linux/*.url ~/Desktop/MicroarreglosDNA/
#Abre página de internet y finaliza programa
sudo service apache2 restart
DISPLAY=:0 chromium-browser 'http://localhost/MicroarreglosDNA/'
DISPLAY=:0 chrome 'http://localhost/MicroarreglosDNA/'
DISPLAY=:0 firefox 'http://localhost/MicroarreglosDNA/'
