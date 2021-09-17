@echo off
START /min C:\xampp\xampp_stop.exe
echo MsgBox "El servicio se ha detenido de forma exitosa.", 64, "Se detuvo el servicio de Microarreglos" >%temp%\mensaje.vbs
start %temp%\mensaje.vbs