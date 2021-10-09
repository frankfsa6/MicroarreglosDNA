#!/bin/bash
# --- Unidad de Microarreglos DNA, IFC, UNAM, MX ---
sudo apt-get install zenity -y
zenity --info --title="Microarreglos DNA" --width=400 --text="Se descargará la última versión del programa e iniciará automáticamente."
cd /var/www/html/MicroarreglosDNA
sudo git reset --hard origin/master
sudo git pull
#Crea accesos directos
sudo mkdir ~/Desktop/MicroarreglosDNA
sudo cp -u /var/www/html/MicroarreglosDNA/Linux/MicroarreglosDNA.sh ~/Desktop/MicroarreglosDNA/
sudo cp -u /var/www/html/MicroarreglosDNA/Linux/*.url ~/Desktop/MicroarreglosDNA/
sudo chmod -R -f 0777 ~/Desktop/MicroarreglosDNA
#Abre página de internet y finaliza programa
sudo service apache2 restart
DISPLAY=:0 chromium-browser "http://localhost/MicroarreglosDNA/"
xdg-open "http://localhost/MicroarreglosDNA/"
