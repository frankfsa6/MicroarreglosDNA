/*
gcc -Wall -pthread -o xyj xyj.c -lpigpio -lm 2>&1
sudo ./xyj 0 1 90 # DirX -DirY - Vel
*/
#include "pinesFunc.c"
// Principal de motores
int main(int argc, char *argv[]){
	// Comprueba funcionamiento de pigpio y llamada correcta del programa
	if (gpioInitialise() < 0 || argc <= 3) 
		return -1;
	// Variables locales
	int auxa, auxs, k, h, p = 0, vel = atoi(argv[3]);
	float contvel, a;
	// Pines XY y fin configurados
	PinesXConfig();
	PinesYConfig();
	PinesFinConfig();
	// Asigna pulsos y direcciones en variables globales
	pinesPul[0] = pulX;
	pinesPul[1] = pulY;
	pinesDir[0] = dirX;
	pinesDir[1] = dirY;
	for(h=0; h<2; h++){
		if (atoi(argv[h+1]) == 0)
			gpioWrite(pinesDir[h],0);
		else
			gpioWrite(pinesDir[h],1);
	}
	// Velocidad actual, h: pasos al vértice, auxa: pasos acumulativos para cambiar velocidad, auxs: pasos constantes de cambio al 1%
	contvel=vel*2;
	h=1000;
	auxa = h/100;
	auxs = auxa;
	// a: distancia del vértice al foco que determina razón de cambio, k: velocidad máxima recibida
	a=pow(h,2)/(4*(contvel-vel));
	k=atoi(argv[3]);
	//Inicia secuencia de pasos hasta detección de fin o límite
	while ( fin == 0 && ints == 0 ){
		// Pregunta por pausa
		if(pausa == 1)
			Pausa();
		// Mueve motores
		gpioWrite(pinesPul[0],1);
		gpioDelay(6);
		gpioWrite(pinesPul[0],0);
		gpioDelay(contvel);
		gpioWrite(pinesPul[1],1);
		gpioDelay(6);
		gpioWrite(pinesPul[1],0);
		gpioDelay(contvel);
		pasos++;
		// Calcula velocidad al llegar a pasos acumulativos siempre que no llegue a vértice
		if ( pasos == (auxa-1) && pasos <= h ){
			// Incrementa pasos, p:pasos faltantes para el vértice
			auxa += auxs;
			p += auxs;
			contvel=round( (pow((p-h),2)+4*a*k)/(4*a) );	
		}			
	}
	// Regresa pasos si toca límite
	if ( ints == 1 )
		RetrocesoDual();
	// Imprime pasos y finaliza
	printf("%i",pasos);
	gpioTerminate();
	return 0;
}
