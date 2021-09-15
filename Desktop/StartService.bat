@echo off
cd C:\xampp\htdocs\MicroarreglosDNA
git pull
START /min C:\xampp\xampp_start.exe
"C:\Program Files (x86)\Google\Chrome\Application\chrome.exe" "http://localhost/MicroarreglosDNA/"