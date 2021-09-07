#!/bin/bash
# Ejecutable que manda pulso, deja prendido o apaga un GPIO dado
# 1 argumento: gpio referenciado
# 2 argumento: enciende o apaga pin
if [ $# -gt 0 ] && [ $# -lt 3 ]; then
	# En caso de ser un argumento, manda 2 pulsos
	if [ $# -eq 1 ]; then
		sudo echo $1 > /sys/class/gpio/export
		sudo echo out > /sys/class/gpio/gpio$1/direction
		sudo echo 1 > /sys/class/gpio/gpio$1/value
		sudo echo 0 > /sys/class/gpio/gpio$1/value
		sudo echo $1 > /sys/class/gpio/unexport
	# Al ser 2 argumentos, el segundo habilita o deshabilita gpio
	else
		# Enciende gpio
		if [ $2 -eq "1" ]; then
			sudo echo $1 > /sys/class/gpio/export
			sudo echo out > /sys/class/gpio/gpio$1/direction
			sudo echo 1 > /sys/class/gpio/gpio$1/value	
		# Apaga gpio
		else
			sudo echo 0 > /sys/class/gpio/gpio$1/value
			sudo echo $1 > /sys/class/gpio/unexport
		fi
	fi
fi
