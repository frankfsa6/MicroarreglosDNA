#!/bin/bash

ruta=/var/www/html
#Crea link y comprime carpeta
sudo unlink Rasp
sudo ln -s $ruta/Rasp ~/Desktop
sudo zip -r ~/Desktop/"Rasp$(date +"%d%b%y").zip" Rasp

read -p "Presione (enter) para finalizar..."
