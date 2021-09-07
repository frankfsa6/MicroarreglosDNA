/*
gcc -Wall -pthread -o pinesFunc pinesFunc.c -lpigpio
sudo ./pinesFunc
*/
#include <stdio.h>
#include <stdlib.h>
#include <signal.h>
#include <math.h>
#include <pigpio.h>
#include "pinesRasp.h"
/* Variables globales
	fin (interrupción por software para fin de código)
 	sensor (veces que se tocan los sensores)
	ints (detección de alguna interrupción)
	pausa (usada mientras se pulse el botón de emergencia)
	pasos (totales avanzados)
	max (pasos teóricos)
	pinPul (pulso del eje en cuestión)
	pinDir (dirección del eje en cuestión)
	pinesPul (pulsos duales del eje en cuestión)
	pinesDir (direcciones duales del eje en cuestión)
*/
int fin = 0, sensor = 0, ints = 0, pausa = 0;
int pasos = 0, max, pinPul, pinDir;
int pinesPul[2], pinesDir[2];
// Interrumpe programa por sensor de límite X
void IntsX(int gpio, int level, uint32_t tick){
	if (gpioRead(limX) == 1){
		ints = 1;
		max = pasos;
	}
	return;
}
// Interrumpe programa por sensor de límite Y
void IntsY(int gpio, int level, uint32_t tick){
	if (gpioRead(limY) == 1){
		ints = 1;
		max = pasos;
	}
	return;
}
// Interrumpe programa por sensor de límite Z
void IntsZ(int gpio, int level, uint32_t tick){
	if (gpioRead(limZ) == 1){
		ints = 1;
		max = pasos;
	}
	return;
}
// Interrumpe y finaliza programa por usuario en joystick
void IntsFin(int gpio, int level, uint32_t tick){
	if (gpioRead(finCodC) == 1){
		fin = 1;
		max = pasos;
	}
	return;
}
// Interrumpe por botón de emergencia
void IntsBotE(int gpio, int level, uint32_t tick){
	if (gpioRead(botE) == 1)
		pausa = 1;
	return;
}
// Función que pausa el sistema principal mientras se activa el botón
void Pausa(){
	while(gpioRead(botE) == 1)
		gpioDelay(200);
	pausa = 0;
	return;
}
//Secuencia de retroceso al tocar cualquier sensor de límite
void Retroceso(){
	int j, dirAnt;
	// Determina dirección de regreso
	dirAnt = gpioRead(pinDir);
	if (dirAnt == 0)
		gpioWrite(pinDir,1);
	else
		gpioWrite(pinDir,0);
	// Regresa pasos necesarios para alejarse del sensor
	for (j=0; j<pasosRet; j++){
		gpioWrite(pinPul,1);
		gpioDelay(6);
		gpioWrite(pinPul,0);
		gpioDelay(velOrig);
	}
	// Reasigna dirección original y corrige pasos
	gpioWrite(pinDir, dirAnt);
	pasos -= pasosRet;
	return;
}
//Secuencia de retroceso dual al tocar cualquier sensor de límite
void RetrocesoDual(){
	int j, dirAnt[2];
	// Determina direcciones XY de regreso
	for (j=0; j<2; j++){
		dirAnt[j] = gpioRead(pinesDir[j]);
		if (dirAnt[j] == 0)
			gpioWrite(pinesDir[j],1);
		else
			gpioWrite(pinesDir[j],0);
	}
	// Regresa pasos necesarios para alejarse del sensor
	for (j=0; j<pasosRet; j++){
		gpioWrite(pinesPul[0],1);
		gpioWrite(pinesPul[1],1);
		gpioDelay(6);
		gpioWrite(pinesPul[0],0);
		gpioWrite(pinesPul[1],0);
		gpioDelay(velOrig);
	}
	// Reasigna dirección original y corrige pasos
	gpioWrite(pinesDir[0], dirAnt[0]);
	gpioWrite(pinesDir[1], dirAnt[1]);
	pasos -= pasosRet;
	return;
}
// Configura lo necesario para el fin y botón de emergencia/pausa
void PinesFinConfig(){
	// Fin por software con resistencia auxiliar y función de interrupción
	gpioSetMode(finCodC,PI_INPUT);
	gpioSetPullUpDown(finCodC,PI_PUD_DOWN);
	gpioSetAlertFunc(finCodC, IntsFin);
	// Pausa por botón de emergencia a tierra y función de interrupción
	gpioSetMode(botE,PI_INPUT);
	gpioSetPullUpDown(botE,PI_PUD_DOWN);
	gpioSetAlertFunc(botE, IntsBotE);
	return;
}
// Configura lo necesario en eje X
void PinesXConfig(){
	// Pulso y dirección como salida
	gpioSetMode(dirX,PI_OUTPUT);
	gpioSetMode(pulX,PI_OUTPUT);
	// Sensor como entrada con resistencia auxiliar y función de interrupción
	gpioSetMode(limX,PI_INPUT);
	gpioSetPullUpDown(limX,PI_PUD_DOWN);
	gpioSetAlertFunc(limX, IntsX);
	return;
}	
// Configura lo necesario en eje Y
void PinesYConfig(){
	// Pulso y dirección como salida
	gpioSetMode(dirY,PI_OUTPUT);
	gpioSetMode(pulY,PI_OUTPUT);
	// Sensor como entrada con resistencia auxiliar y función de interrupción
	gpioSetMode(limY,PI_INPUT);
	gpioSetPullUpDown(limY,PI_PUD_DOWN);
	gpioSetAlertFunc(limY, IntsY);	
	return;
}
// Configura lo necesario en eje Z
void PinesZConfig(){
	// Pulso y dirección como salida
	gpioSetMode(dirZ,PI_OUTPUT);
	gpioSetMode(pulZ,PI_OUTPUT);
	// Sensor como entrada con resistencia auxiliar y función de interrupción
	gpioSetMode(limZ,PI_INPUT);
	gpioSetPullUpDown(limZ,PI_PUD_DOWN);
	gpioSetAlertFunc(limZ, IntsZ);	
	return;
}
