//Sube 3mm, sube y avanza 2mm, baja y avanza 2mm
#include <stdio.h>
#include <stdlib.h>
#include <signal.h>
#include <pigpio.h>
#include <math.h>
#include "pinesRasp.h"
/*
gcc -Wall -pthread -o yz yz.c -lpigpio -lm
sudo ./yz 300 250 250 100 # PasosZIniciales - PasosY - PasosZ - velMax
*/
int PinPul[] = {pulY,pulZ};
int PinDir[] = {dirY,dirZ};

int pasosYZ[2]={0,0}; // ---> Contador de pasos Y,Z
int pasosYZmax[2]; 		// ---> pasosYZmax[0] pasos en eje Y; pasosYZmax[1] pasos en eje Z, se usan para comparar el máximo de pasos 
int avance=0; 		// ---> avance actual del sistema

//Variables de control de velocidad
int auxa, auxs, k, h;
float contVel, a;

void controlDeVelocidad(int velMax){
	auxa=pasosYZmax[1]/100;			
	h=pasosYZmax[1]/2;		
    contVel=velMax*2;
	a=pow(h,2)/(4*(contVel-velMax));
	k=velMax;
	auxs=auxa;
	return;
}

int main(int argc, char *argv[]){
    int i, j, x = 0, velMax = atoi(argv[4])/2;
	if (gpioInitialise() < 0 || argc == 1) 
		return -1;
	// Pines configurados Y, Z
	for (i=0;i<2;i++){
		gpioSetMode(PinDir[i],PI_OUTPUT);
		gpioSetMode(PinPul[i],PI_OUTPUT);
	}
    // Asigna direcciones
	gpioWrite(PinDir[0],1);	//Asigna dirección en Y
    gpioWrite(PinDir[1],0); //Asigna dirección en Z
	pasosYZmax[0] = atoi(argv[2]);
	pasosYZmax[1] = atoi(argv[3]);
    controlDeVelocidad(velMax);

    //Inicia secuencia de pasos 
    avance=atoi(argv[1]);
    for (i=0; i<avance; i++){
        gpioWrite(pulZ,1);
        gpioDelay(6);
        gpioWrite(pulZ,0);
        gpioDelay(contVel);
        pasosYZ[1]--;
    }
    if (pasosYZmax[0] == pasosYZmax[1]){		
	//Inicia secuencia de pasos iguales	
		avance=pasosYZmax[1]/2;
        for (j=0; j < 2; j++){
		    for (i=0; i<avance; i++){
                gpioWrite(pulZ,1);
                gpioDelay(6);
                gpioWrite(pulZ,0);
                gpioDelay(contVel);
                pasosYZ[1]++;
                gpioWrite(pulY,1);
                gpioDelay(6);
                gpioWrite(pulY,0);
                gpioDelay(contVel);
                pasosYZ[0]++;
                if (i==(auxa-1)){
                    auxa+=auxs;
                    x+=auxs;
                    contVel=round((pow((x-h),2)+4*a*k)/(4*a));	
                }
            }
            auxa=auxs;
            gpioWrite(PinDir[1],1);
        }			
	}
	else if(pasosYZmax[0] > pasosYZmax[1]){		
        avance=pasosYZmax[1]/2;
        for (i=0; i<avance; i++){
            gpioWrite(pulZ,1);
            gpioDelay(6);
            gpioWrite(pulZ,0);
            gpioDelay(contVel);
            pasosYZ[1]--;
            gpioWrite(pulY,1);
            gpioDelay(6);
            gpioWrite(pulY,0);
            gpioDelay(contVel);
            pasosYZ[0]++;
            if (i==(auxa-1)){
                auxa+=auxs;
                x+=auxs;
                contVel=round((pow((x-h),2)+4*a*k)/(4*a));	
            }
        }	
        avance=pasosYZmax[0]-pasosYZmax[1];
        for (i=0; i<avance; i++){
            gpioWrite(pulY,1);
            gpioDelay(6);
            gpioWrite(pulY,0);
            gpioDelay(contVel);
            pasosYZ[0]++;
        }
        avance=pasosYZmax[1]/2;
        gpioWrite(PinDir[1],1);	
        auxa=auxs;
        for (i=0; i<avance; i++){
            gpioWrite(pulZ,1);
            gpioDelay(6);
            gpioWrite(pulZ,0);
            gpioDelay(contVel);
            pasosYZ[1]++;
            gpioWrite(pulY,1);
            gpioDelay(6);
            gpioWrite(pulY,0);
            gpioDelay(contVel);
            pasosYZ[0]++;
            if (i==(auxa-1)){
                auxa+=auxs;
                x+=auxs;
                contVel=round((pow((x-h),2)+4*a*k)/(4*a));	
            }
        }
	} else {
        avance=pasosYZmax[1]-pasosYZmax[0]/2;
        for (i=0; i<avance; i++){
            gpioWrite(pulZ,1);
            gpioDelay(6);
            gpioWrite(pulZ,0);
            gpioDelay(contVel);
            pasosYZ[1]--;
        }
        avance=pasosYZmax[0]/2;
        for (j=0; j < 2; j++){
		    for (i=0; i<avance; i++){
                gpioWrite(pulZ,1);
                gpioDelay(6);
                gpioWrite(pulZ,0);
                gpioDelay(contVel);
                pasosYZ[1]++;
                gpioWrite(pulY,1);
                gpioDelay(6);
                gpioWrite(pulY,0);
                gpioDelay(contVel);
                pasosYZ[0]++;
                if (i==(auxa-1)){
                    auxa+=auxs;
                    x+=auxs;
                    contVel=round((pow((x-h),2)+4*a*k)/(4*a));	
                }
            }
            auxa=auxs;
            gpioWrite(PinDir[1],1);
        }			
        avance=pasosYZmax[1]-pasosYZmax[0]/2;
        for (i=0; i<avance; i++){
            gpioWrite(pulZ,1);
            gpioDelay(6);
            gpioWrite(pulZ,0);
            gpioDelay(contVel);
            pasosYZ[1]++;
        }
    }
    avance=atoi(argv[1]);
    for (i=0; i<avance; i++){
        gpioWrite(pulZ,1);
        gpioDelay(6);
        gpioWrite(pulZ,0);
        gpioDelay(contVel);
        pasosYZ[1]++;
    }

    printf("%i ,",pasosYZ[0]);
	printf(" %i",pasosYZ[1]);
	gpioWrite_Bits_0_31_Clear((1<<dirZ)|(1<<pulZ));
	gpioWrite_Bits_0_31_Clear((1<<dirY)|(1<<pulY));
	gpioTerminate();
	return 0;
}
