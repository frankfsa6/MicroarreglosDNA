#!/bin/bash
# --- Unidad de Microarreglos DNA, IFC, UNAM, MX ---
#Revisa paquete Zenity y actualiza paquetes
echo "instDNA">~/Downloads/instDNA.txt
sudo apt-get install zenity -y
zenity --info --title="Instalador Microarreglos DNA" --width=400 --text="Gracias por utilizar 'Microarreglos DNA'. Para evitar brechas de seguridad, se actualizan las dependencias del sistema operativo. \n\n Este proceso tarda alrededor de 40 minutos, aunque suele variar acorde a la velocidad de su internet y cantidad de dependencias por actualizar. \n\n Presione aceptar para continuar."
sudo apt-get update && sudo apt-get upgrade -y
sudo apt-get autoremove -y
#Instala sistema por primera vez y limpia navegador
zenity --info --title="Microarreglos DNA" --width=400 --text="Para continuar con la instalación, se verifica que se encuentren los siguientes programas correctamente actualizados. De lo contrario, se instalan en el sistema (suele tardar un tiempo en descargar los complementos necesarios, acorde a la velocidad de su internet).
\n1) Servidor local (Apache).
\n2) Gestor de versiones (Github) y programa principal (MicroarreglosDNA).
\n3) Lenguajes programadores y base de datos (PHP y MySQL/MariaDB).
\n\n Presione aceptar para continuar."
#Verifica o instala Apache y agrega usuario
sudo apt-get install apache2 -y
sudo usermod -a -G www-data pi
zenity --info --title="Microarreglos DNA" --width=400 --text="Paso 1/3 completo. Servidor local instalado y verificado. \n\n Presione aceptar para continuar."
#Instala git para el programa y verifica programa
sudo apt-get install git -y
if test ! -d /var/www/html/MicroarreglosDNA; then
    sudo mkdir /var/www/html/MicroDNA
    cd /var/www/html/MicroDNA/
    sudo git clone "https://github.com/frankfsa6/MicroarreglosDNA.git" 
    sudo mv MicroarreglosDNA ../
    cd ..
    sudo rm -r MicroDNA
fi
#Define atributos a carpetas 
sudo chmod -R -f 0777 /var/www/html/MicroarreglosDNA
sudo chown -R pi:pi /var/www/html/MicroarreglosDNA
zenity --info --title="Microarreglos DNA" --width=400 --text="Paso 2/3 completo. Gestor de versiones 'Github' y programa 'Microarreglos DNA' instalado correctamente. \n\n Presione aceptar para continuar."
#Verifica o instala PHP y MySQL/MariaDB
sudo apt-get install php -y
sudo apt-get install php-mysql -y
sudo apt-get install mariadb-server -y
#Verifica o instala biblioteca de pines y crea usuario SQL para programa
sudo apt-get install pigpio -y
sudo apt-get autoremove -y
sudo mysql < /var/www/html/MicroarreglosDNA/sql/"usuario.sql"
sudo service apache2 restart
#Otorga permiso superusuario y pone última versión
sudo echo 'www-data ALL=(ALL)NOPASSWD:ALL' | sudo EDITOR='tee -a' visudo
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
zenity --info --title="Microarreglos DNA" --width=400 --text="Paso 3/3 completo. Lenguajes programadores y configuraciones extras instaladas y verificadas. \n\n Ha finalizado de instalar el programa. En su escritorio se instaló el archivo ejecutable para su próximo uso. Acuda al 'Manual de usuario' para mayor detalle sobre su uso. \n\n Microarreglos DNA se abrirá en seguida."
DISPLAY=:0 chromium-browser "http://localhost/MicroarreglosDNA/"
xdg-open "http://localhost/MicroarreglosDNA/"
sudo rm -r ~/Downloads/instDNA.txt