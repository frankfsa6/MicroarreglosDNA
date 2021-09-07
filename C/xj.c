/*
gcc -Wall -pthread -o xj xj.c -lpigpio -lm 2>&1
sudo ./xj 0 100 # Dir - Vel
*/
#include "pinesFunc.c"
// Principal de motores
int main(int argc, char *argv[]){
	// Comprueba funcionamiento de pigpio y llamada correcta del programa
	if (gpioInitialise() < 0 || argc <= 2) 
		return -1;
	// Variables locales
	int auxa, auxs, k, h, p = 0, vel = atoi(argv[2]);
	float contvel, a;
	// Pines X y fin configurados
	PinesXConfig();
	PinesFinConfig();
	// Asigna pulso y dirección en variables globales
	pinPul = pulX;
	pinDir = dirX;
	if (atoi(argv[1]) == 0)
		gpioWrite(pinDir,0);
	else
		gpioWrite(pinDir,1);
	// Velocidad actual, h: pasos al vértice, auxa: pasos acumulativos para cambiar velocidad, auxs: pasos constantes de cambio al 1%
	contvel=vel*2;
	h=1000;
	auxa = h/100;
	auxs = auxa;
	// a: distancia del vértice al foco que determina razón de cambio, k: velocidad máxima recibida
	a=pow(h,2)/(4*(contvel-vel));
	k=atoi(argv[2]);
	//Inicia secuencia de pasos hasta detección de fin o límite
	while ( fin == 0 && ints == 0 ){
		// Pregunta por pausa
		if(pausa == 1)
			Pausa();
		// Mueve motores
		gpioWrite(pinPul,1);
		gpioDelay(6);
		gpioWrite(pinPul,0);
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
		Retroceso();
	// Imprime pasos y finaliza
	printf("%i",pasos);
	gpioTerminate();
	return 0;
}
