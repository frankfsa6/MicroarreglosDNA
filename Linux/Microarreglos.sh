#!/bin/bash
# --- Unidad de Microarreglos DNA, IFC, UNAM, MX ---
Revisar(){
	#Evita apagado de pantalla
	xset -dpms
	xset s noblank
	xset s off
	#Revisa paquete Zenity
	sudo apt-get install zenity -y
	sudo apt-get install zip -y
}
#Actualiza un comprimido
Actualizar(){
	#Verifica que exista comprimido
	if test -f ./Rasp*.zip; then
		#Verifica que exista carpeta para respaldar
		if test -d /var/www/html/Rasp; then
			#Crea link temporal a carpeta
			sudo ln -s /var/www/html/Rasp ./
			#Genera respaldo con base de datos recopilada
			sudo mysqldump dbrobot > /var/www/html/Rasp/sql/dbrobot.sql
			sudo zip -r /var/www/html/"Respaldo$(date +"%d%b%y").zip" Rasp
			#Elimina link temporal y carpeta actual del programa
			sudo unlink Rasp
			sudo rm -r /var/www/html/Rasp
		fi
		#Descomprime archivos y sobreescribe manual
		sudo unzip ./Rasp*.zip -d /var/www/html/
		sudo cp /var/www/html/Rasp/"Manual de usuario.pdf" ./
		#Define atributos a carpetas y elimina archivo comprimido
		sudo chmod -R -f 0707 /var/www/html/Rasp
		sudo chown -R pi:pi /var/www/html/Rasp
		sudo rm ./Rasp*.zip
		#Mensaje exitoso
		zenity --info --title="Microarreglos DNA" --width=300 --text="Programa 'Microarreglos DNA' actualizado correctamente."
	else
		#Mensaje fallido
		zenity --error --title="Microarreglos DNA" --width=300 --text="No se han encontrado los archivos del programa. Verifique que exista un archivo comprimido llamado 'Rasp(fecha).zip' en el mismo lugar donde se encuentra el instalador."
	fi
}
#Limpieza cache del navegador
Limpiar(){
	sudo rm -R ~/.cache/chromium
}
#Respaldo del sistema
Respaldar(){
	#Verifica que exista carpeta para respaldar
	if test -d /var/www/html/Rasp; then
		#Crea link temporal a carpeta
		sudo ln -s /var/www/html/Rasp ./
		#Genera respaldo con base de datos recopilada
		sudo mysqldump dbrobot > /var/www/html/Rasp/sql/dbrobot.sql
		sudo zip -r ./"Rasp$(date +"%d%b%y").zip" Rasp
		#Elimina link temporal
		sudo unlink Rasp
		#Mensaje exitoso
		zenity --info --title="Microarreglos DNA" --width=300 --text="Se ha creado exitosamente un respaldo llamado 'Rasp(fecha.zip)' en el mismo lugar donde se encuentra el instalador."
	else
		#Mensaje fallido
		zenity --error --title="Microarreglos DNA" --width=300 --text="No se ha encontrado carpeta para respaldar."
	fi
}
#Usado primera vez o para verificar sistema
Instalar(){	
	sudo apt-get update -y
	#Verifica o instala Apache y agrega usuario
	sudo apt-get install apache2 -y
	sudo usermod -a -G www-data pi
	zenity --info --title="Microarreglos DNA" --width=300 --text="Paso 1/3 completo. Servidor local instalado y verificado. \n\n Presione aceptar para continuar."
	#Instala programa al descomprimir archivos
	sudo unzip ./Rasp*.zip -d /var/www/html/
	sudo cp /var/www/html/Rasp/"Manual de usuario.pdf" ./
	#Define atributos a carpetas y elimina archivo comprimido
	sudo chmod -R -f 0707 /var/www/html/Rasp
	sudo chown -R pi:pi /var/www/html/Rasp
	sudo rm ./Rasp*.zip
	zenity --info --title="Microarreglos DNA" --width=300 --text="Paso 2/3 completo. Programa 'Microarreglos DNA' instalado correctamente. \n\n Presione aceptar para continuar."
	#Verifica o instala PHP y MySQL/MariaDB
	sudo apt-get install php -y
	sudo apt-get install php-mysql -y
	sudo apt-get install mariadb-server -y
	#Verifica o instala biblioteca de pines y crea usuario SQL para programa
	sudo apt-get install pigpio -y
	sudo apt-get autoremove -y
	sudo mysql < /var/www/html/Rasp/sql/"usuario.sql"
	sudo service apache2 restart
	#Otorga permiso superusuario y manda mensaje exitoso
	sudo echo 'www-data ALL=(ALL)NOPASSWD:ALL' | sudo EDITOR='tee -a' visudo
	zenity --info --title="Microarreglos DNA" --width=300 --text="Paso 3/3 completo. Lenguajes programadores y configuraciones extras instaladas y verificadas. \n\n Ha finalizado de instalar el programa. Acuda al 'Manual de usuario' para mayor detalle sobre su uso."
}
#Inicia revisando configuraciones y manda opciones a realizar
Revisar
op=1
while [ $op -gt 0 ] && [ $op -lt 6 ] 
do
	op=$(zenity --list --title="Bienvenido a Microarreglos DNA" --width=120 --height=350 --text="Gracias por utilizar 'Microarreglos DNA'. Seleccione una de las siguientes opciones y presione aceptar para continuar. \n\nPara terminar el programa, cierre la ventana o presione cancelar.\n" --column="  #" --column="   Acciones disponibles" "1" "Actualizar el programa (previamente instalado)." "2" "Realizar limpieza del navegador." "3" "Generar un respaldo de datos." "4" "Actualizar dependencias del sistema operativo (recomendable para la primera vez)." "5" "Instalar programa completo y complementos (primera vez)." )
	case $op in
		#Actualiza sistema y limpia navegador
		1) zenity --info --title="Microarreglos DNA" --width=300 --text="Para actualizar el programa, debe existir un archivo comprimido llamado 'Rasp(fecha).zip' en el mismo lugar donde se encuentra el instalador. \n\n Presione aceptar para continuar."
		Limpiar
		Actualizar
		;;
		#Limpia navegador
		2) zenity --info --title="Microarreglos DNA" --width=300 --text="Previo a la limpieza, procure tener cerrada cualquier ventana del navegador. \n\n Presione aceptar para continuar."
		Limpiar
		zenity --info --title="Microarreglos DNA" --width=300 --text="La memoria interna del navegador se ha limpiado."
		;;
		#Realiza respaldo de carpeta completa
		3) zenity --info --title="Microarreglos DNA" --width=300 --text="El respaldo se hace de todo el programa, generando un archivo comprimido llamado 'Rasp(fecha).zip' en el escritorio. \n\n Presione aceptar para continuar."
		Respaldar
		;;
		#Actualiza sistema con paquetes y reinicia equipo
		4) zenity --info --title="Microarreglos DNA" --width=300 --text="Para evitar brechas de seguridad, se actualizan las dependencias del sistema operativo. Este proceso tarda alrededor de 40 minutos, aunque suele variar acorde a la velocidad de su internet y cantidad de dependencias por actualizar. \n\n Presione aceptar para continuar."
		sudo apt-get update && sudo apt-get upgrade -y
		sudo apt-get autoremove -y
		zenity --info --title="Microarreglos DNA" --width=300 --text="Sistema y dependencias instalados y verificados. Se debe realizar un reinicio del sistema inmediatamente para aplicar los cambios. Guarde sus documentos y tareas activas.  \n\n Una vez realizado el reinicio del sistema, vuelva a ejecutar este instalador e instale el programa y complementos necesarios. \n\n Presione aceptar para continuar."
		sudo shutdown -r 0
		;;
		#Instala sistema por primera vez y limpia navegador
		5) zenity --info --title="Microarreglos DNA" --width=300 --text="Para el funcionamiento apropiado, se verifica que se encuentren los siguientes programas correctamente actualizados. De lo contrario, se instalan en el sistema (suele tardar un tiempo en descargar los complementos necesarios, acorde a la velocidad de su internet).
		\n1) Servidor local (Apache).
		\n2) Programa para 'Microarreglos DNA'.
		\n3) Lenguajes programadores y base de datos (PHP y MySQL/MariaDB).
		\n\n Presione aceptar para continuar."
		sudo rm ./Microarreglos*.zip
		Instalar
		Limpiar
		;; 
	esac
done
