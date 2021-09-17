/*
gcc -Wall -pthread -o xy xy.c -lpigpio -lm
sudo ./xy 0 0 8000 50000 80 # DirX - DirY - PasosX - PasosY - velMax
*/
#include <stdio.h>
#include <stdlib.h>
#include <signal.h>
#include <pigpio.h>
#include <math.h>
#include "pinesRasp.h"
/* Para control de velocidad se sigue una función cuadrática (parábola) tal que:
ecuación de la parábola: (x-h)²=4a(y-k)
y=[(x-h)²+4ak]/4a
contVel=velocidad eje y ; x= pasos eje x; 
(h,k) vertice de parábola dónde h = x/2 , k= velMax
a=h²/4(y-k) dónde y=contVel inicial; (h,k) vertice
* Nota: la ecuación máxima estandar es para control de 20000 pasos
  
* Variables:
contVel: contador de velocidad instantánea
velMax: velocidad máxima de movimiento
pasosXYmax[0] pasos en eje x ; pasosXYmax[1] pasos en eje y, se usan para comparar el máximo de pasos
eje[] indica en qué ejes habrá movimiento; [1,0] eje x, [0,1] eje y, [1,1] ejes xy 
avance: avance total de pasos que se avanceanzará en la trayectoria
auxa: indica punto de decremento de velocidad y conteo de pasos para cambio de velocidad
auxs: número de pasos cada que cambia la velocidad (cada 1% de los pasos)
*/
int PinesPul[] = {pulX,pulY};
int PinesDir[] = {dirX,dirY};
int PinesInt[] = {limX,limY};
int eje[] = {1,1};  // ---> indica en qué ejes habrá movimiento; [0,1] eje x, [1,0] eje y, [1,1] ejes xy 
int pasosXY[2]={0,0}; // ---> contador de pasos X,Y
int pasosXYmax[2]; 		// ---> pasosXYmax[0] pasos en eje x ; pasosXYmax[1] pasos en eje y, comparan el máximo de pasos 
int ints;			// indica si hubo interrupción: ints=0 -> interrupción en eje X  ints=1 -> interrupción en eje Y
int avance=0; 		// ---> avance actual del sistema
int fin=0;
int pausa=0;
//Variables de control de velocidad
int auxa, auxs, k, h;
float contVel, a;
// Interrumpe programa y redefine ciclo for de cuenta de avance
void IntsX(int gpio, int level, uint32_t tick){
	if (gpioRead(limX) == 1)
		avance = pasosXY[eje[1]];
	ints = 0;
	return;
}
// Interrumpe programa y redefine ciclo for de cuenta de avance
void IntsY(int gpio, int level, uint32_t tick){
	if (gpioRead(limY) == 1)
		avance= pasosXY[eje[1]];
	ints = 1;
	return;
}
// Interrumpe y finaliza programa por usuario en joystick
void IntsFin(int gpio, int level, uint32_t tick){
	if (gpioRead(finCodC) == 1)
		fin = 1;
	return;
}
// Interrumpe por botón de emergencia
void IntsBotE(int gpio, int level, uint32_t tick){
	if (gpioRead(botE) == 1)
		pausa = 1;
	return;
}
// Función que pausa el sistema principal mientras se activa el botón
void PausaFin(){
	while(gpioRead(botE) == 1 && fin == 0)
		gpioDelay(200);
	pausa = 0;
	if(fin == 1)
		avance = 0;
	return;
}
//Secuencia de retroceso
void Retroceso(int Pin){
	int j;
	if (gpioRead(PinesDir[Pin]) == 0)
		gpioWrite(PinesDir[Pin],1);
	else
		gpioWrite(PinesDir[Pin],0);
	for (j=0; j<pasosRet; j++){
		gpioWrite(PinesPul[Pin],1);
		gpioDelay(6);
		gpioWrite(PinesPul[Pin],0);
		gpioDelay(90);
	}
	if (ints==0)
		pasosXY[0] -= pasosRet;
	else
		pasosXY[1] -= pasosRet;
	return;
}	
// Modifica velocidad a modo parabólico
void controlDeVelocidad(int velMax){
	if (pasosXYmax[eje[0]]>30000){	
		h=3000;			
		auxa=h/100;
	}
	else{
		h=pasosXYmax[eje[0]]/2;		
		auxa=pasosXYmax[eje[0]]/100;
	}	
	contVel=velMax*2;
	a=pow(h,2)/(4*(contVel-velMax));
	k=velMax;
	auxs=auxa;
	return;
}
// Principal de motores
int main(int argc, char *argv[]){
	int i, j, entero, x = 0, velMax = atoi(argv[5])/2;
	float numMin,numMax,decimal,p1=0,p2=0,aux;	
	if(gpioInitialise()<0 || argc==1) 
		return -1;
	// Pines configurados X , Y
	for(i=0;i<2;i++){
		gpioSetMode(PinesDir[i],PI_OUTPUT);
		gpioSetMode(PinesPul[i],PI_OUTPUT);
		gpioSetMode(PinesInt[i],PI_INPUT);
		gpioSetPullUpDown(PinesInt[i],PI_PUD_DOWN);
		// Asigna dirección
		if(atoi(argv[i+1]) == 0)
			gpioWrite(PinesDir[i],0);
		else
			gpioWrite(PinesDir[i],1);
	}
	gpioSetAlertFunc(limX, IntsX);
	gpioSetAlertFunc(limY, IntsY);
	pasosXYmax[0] = atoi(argv[3]);
	pasosXYmax[1] = atoi(argv[4]);
	// Pines para pausa y fin total
	gpioSetMode(finCodC,PI_INPUT);
	gpioSetPullUpDown(finCodC,PI_PUD_DOWN);
	gpioSetAlertFunc(finCodC, IntsFin);
	gpioSetMode(botE,PI_INPUT);
	gpioSetPullUpDown(botE,PI_PUD_DOWN);
	gpioSetAlertFunc(botE, IntsBotE);
	//Secuencia de pasos para una sola dirección
	if(pasosXYmax[0] == 0 ||  pasosXYmax[1] == 0){
		velMax=velMax*2;
		if(pasosXYmax[0] != 0)
			eje[0]=0;				
		controlDeVelocidad(velMax);
		avance=pasosXYmax[eje[0]]; //eje[0]=0 - eje X, eje[0]=1 - eje Y
		for(i=0; i<avance; i++){
			// Pregunta por pausa
			if(pausa == 1 || fin == 1)
				PausaFin();
			gpioWrite(PinesPul[eje[0]],1);
			gpioDelay(6);
			gpioWrite(PinesPul[eje[0]],0);
			gpioDelay(contVel);
			pasosXY[eje[0]]++;	
			if(pasosXYmax[eje[0]]>30000 && i==h) 
				auxa=pasosXYmax[eje[0]]-h;
			if(i==(auxa-1)){
				auxa+=auxs;
				x+=auxs;
				contVel=round((pow((x-h),2)+4*a*k)/(4*a));	
			}	
		}
	}
	else{		
		//Obtiene numero de pasos para la diagonal dif de 45° 
		if(pasosXYmax[0] > pasosXYmax[1])					
			eje[0]=0;
		else 
			eje[1]=0;
		//Si x > y  eje[0]=0, eje [1]=1 
		//Si y > x  eje[0]=1, eje [1]=0
		numMax=pasosXYmax[eje[0]];
		numMin=pasosXYmax[eje[1]];
		aux=numMax/numMin;
		entero=trunc(aux);
		decimal=aux-entero;
		aux=0;
		controlDeVelocidad(velMax);
		//Secuencia de pasos lineales para diagonales muy pronunciadas
		if(entero>2){
			aux=entero-2;
			aux=aux*numMin;
			entero=2;
			avance=aux;
			velMax=velMax*2;
			for(i=0; i<avance; i++){
				// Pregunta por pausa
				if(pausa == 1 || fin == 1)
					PausaFin();
				gpioWrite(PinesPul[eje[0]],1);
				gpioDelay(6);
				gpioWrite(PinesPul[eje[0]],0);
				gpioDelay(contVel);
				pasosXY[eje[0]]++;
				if (numMax>30000 && x==h) 
					auxa=numMax-h;
				if (pasosXY[eje[0]]==(auxa-1)){
					auxa+=auxs;
					x+=auxs;
					contVel=round((pow((x-h),2)+4*a*k)/(4*a));	
					//printf("%i , %0.0f/",x,contVel);
				}					
			}	
			velMax=velMax/2;
		}
		if(avance==aux){
			p1=round(numMin*decimal);
			p2=numMin-p1;		
			aux=1;
			if(p1 == 0)
				aux--;
			if(pasosXY[eje[0]] == 0)
				auxa=auxs;			
			//Inicia secuencia de pasos
			avance=numMin;
			for(i=0; i<avance; i++){
				for(j=0; j<entero+aux; j++){
					// Pregunta por pausa
					if(pausa == 1 || fin == 1)
						PausaFin();
					gpioWrite(PinesPul[eje[0]],1);
					gpioDelay(6);
					gpioWrite(PinesPul[eje[0]],0);
					gpioDelay(contVel);
					pasosXY[eje[0]]++;
					if (numMax>30000 && x==h) 
						auxa=numMax-h;
					if (pasosXY[eje[0]]==(auxa-1)){
						auxa+=auxs;
						x+=auxs;
						contVel=round((pow((x-h),2)+4*a*k)/(4*a));	
						//printf("%i , %0.0f - ",x,contVel);
					}
				}
				// Pregunta por pausa
				if(pausa == 1 || fin == 1)
					PausaFin();			
				gpioWrite(PinesPul[eje[1]],1);
				gpioDelay(6);
				gpioWrite(PinesPul[eje[1]],0);
				gpioDelay(contVel);
				pasosXY[eje[1]]++;				
				if (i==p1-1)
					aux--;
			}
		}
	}
	//Finaliza pasos y espera un tiempo para revisar si hay interrupción
	if( pasosXY[0] != atoi(argv[3]) || pasosXY[1] != atoi(argv[4]) )
		Retroceso(ints);
	printf("%i ,",pasosXY[0]);
	printf(" %i",pasosXY[1]);
	gpioWrite_Bits_0_31_Clear((1<<dirX)|(1<<pulX));
	gpioWrite_Bits_0_31_Clear((1<<dirY)|(1<<pulY));
	gpioTerminate();
	return 0;
}
