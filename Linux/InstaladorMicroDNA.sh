#!/bin/bash
# --- Unidad de Microarreglos DNA, IFC, UNAM, MX ---
#Evita apagado de pantalla
xset -dpms
xset s noblank
xset s off
#Revisa paquete Zenity
sudo apt-get install zenity -y
sudo apt-get install zip -y
#Actualiza sistema con paquetes
zenity --info --title="Instalador Microarreglos DNA" --width=120 --height=350 --text="Gracias por utilizar 'Microarreglos DNA'. Para evitar brechas de seguridad, se actualizan las dependencias del sistema operativo. Este proceso tarda alrededor de 40 minutos, aunque suele variar acorde a la velocidad de su internet y cantidad de dependencias por actualizar. \n\n Presione aceptar para continuar."
sudo apt-get update && sudo apt-get upgrade -y
sudo apt-get autoremove -y
#Instala sistema por primera vez y limpia navegador
zenity --info --title="Microarreglos DNA" --width=300 --text="Para continuar con la instalación, se verifica que se encuentren los siguientes programas correctamente actualizados. De lo contrario, se instalan en el sistema (suele tardar un tiempo en descargar los complementos necesarios, acorde a la velocidad de su internet).
\n1) Servidor local (Apache).
\n2) Lenguajes programadores y base de datos (PHP y MySQL/MariaDB).
\n3) Gestor de versiones Github y programa 'MicroarreglosDNA'.
\n\n Presione aceptar para continuar."
#Verifica o instala Apache y agrega usuario
sudo apt-get install apache2 -y
sudo usermod -a -G www-data pi
zenity --info --title="Microarreglos DNA" --width=300 --text="Paso 1/3 completo. Servidor local instalado y verificado. \n\n Presione aceptar para continuar."
#Instala programa al descomprimir archivos
sudo apt-get install git -y
cd "/var/www/html/"
git clone "https://github.com/frankfsa6/MicroarreglosDNA.git"
zenity --info --title="Microarreglos DNA" --width=300 --text="Paso 2/3 completo. Gestor de versiones 'Github' y programa 'Microarreglos DNA' instalado correctamente. \n\n Presione aceptar para continuar."
#Verifica o instala PHP y MySQL/MariaDB
sudo apt-get install php -y
sudo apt-get install php-mysql -y
sudo apt-get install mariadb-server -y
#Verifica o instala biblioteca de pines y crea usuario SQL para programa
sudo apt-get install pigpio -y
sudo apt-get autoremove -y
sudo mysql < /var/www/html/MicroarreglosDNA/sql/"usuario.sql"
sudo service apache2 restart
#Otorga permiso superusuario y manda mensaje exitoso
sudo echo 'www-data ALL=(ALL)NOPASSWD:ALL' | sudo EDITOR='tee -a' visudo
zenity --info --title="Microarreglos DNA" --width=300 --text="Paso 3/3 completo. Lenguajes programadores y configuraciones extras instaladas y verificadas. \n\n Ha finalizado de instalar el programa. Acuda al 'Manual de usuario' para mayor detalle sobre su uso. Microarreglos DNA se abrirá en seguida."
sudo exec "/var/www/html/MicroarreglosDNA/Linux/MicroarreglosDNA.sh"