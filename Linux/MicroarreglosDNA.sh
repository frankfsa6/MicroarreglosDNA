#!/bin/bash
# --- Unidad de Microarreglos DNA, IFC, UNAM, MX ---
#Evita apagado de pantalla
xset -dpms
xset s noblank
xset s off
#Actualiza de github
echo \n\n Iniciando MicroarreglosDNA...\n\n
cd "/var/www/html/MicroarreglosDNA"
sudo git reset --hard origin/master
sudo git pull
#Abre página de internet y finaliza programa
sudo mkdir ~/Desktop/MicroarreglosDNA
sudo cp /y "/var/www/html/MicroarreglosDNA/Linux/MicroarreglosDNA.sh" "~/Desktop/MicroarreglosDNA/"
sudo cp /y "/var/www/html/MicroarreglosDNA/Linux/*url" "~/Desktop/MicroarreglosDNA/"
sudo xdg-open "http://localhost/MicroarreglosDNA/"
read -t 5 -p "MicroarreglosDNA ya está funcionando, esta ventana se cerrará automáticamente ..."
