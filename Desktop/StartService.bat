@echo off
cd C:\xampp\htdocs\MicroarreglosDNA
git reset --hard origin/master
git pull
START /min C:\xampp\xampp_start.exe
START ""  http://localhost/MicroarreglosDNA/