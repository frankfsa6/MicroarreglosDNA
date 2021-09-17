#!/bin/bash

ruta=/var/www/html

if test -f ~/Desktop/*.zip; then
	#Crea respaldo comprimido
	sudo unlink Rasp
	sudo ln -s $ruta/Rasp ~/Desktop
	sudo zip -r $ruta/"Respaldo$(date +"%d%b%y").zip" Rasp
	#Elimina enlace y carpeta anterior
	sudo unlink Rasp
	sudo rm -r $ruta/Rasp
	#Descomprime nuevo ZIP siempre puesto en escritorio
	sudo unzip ~/Desktop/*.zip -d $ruta/
	sudo ln -s $ruta/Rasp ~/Desktop
	#Define atributos a carpetas
	sudo chown -R root:root $ruta/Rasp
	sudo chmod -R 707 $ruta/Rasp
	#Limpia caché de navegador
	sudo rm -R ~/.cache/chromium
fi

read -p "Presione (enter) para finalizar..."
