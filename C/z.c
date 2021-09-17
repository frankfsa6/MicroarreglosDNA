/*
gcc -Wall -pthread -o z z.c -lpigpio -lm 2>&1
sudo ./z 0 8000 50 # Dir - Pasos - Vel
*/
#include "pinesFunc.c"
// Principal de motores
int main(int argc, char *argv[]){
	// Comprueba funcionamiento de pigpio y llamada correcta del programa
	if (gpioInitialise() < 0 || argc <= 3) 
		return -1;
	// Variables locales
	int i = 0, auxa, auxs, k, h, p = 0, vel = atoi(argv[3]);
	float contvel, a;
	// Pines Z y fin configurados
	PinesZConfig();
	PinesFinConfig();
	// Asigna pulso y dirección en variables globales
	pinPul = pulZ;
	pinDir = dirZ;
	if (atoi(argv[1]) == 0)
		gpioWrite(pinDir,0);
	else
		gpioWrite(pinDir,1);
	//Control de velocidad (auxa:incremento de pasos actualizable para cambio de velocidad, h:pasos al vértice)
	if (atoi(argv[2])>10000){
		auxa=200;	
		h=1000;			
	}
	else{
		auxa=atoi(argv[2])/100;			
		h=atoi(argv[2])/2;
		vel += 150;		
	}	
	contvel=vel*2;
	// a:, k:velocidad máxima, auxs:incremento constante de pasos (1% del total)
	a=pow(h,2)/(4*(contvel-vel));
	k=atoi(argv[3]);
	auxs=auxa;
	//Inicia secuencia de pasos
	max = atoi(argv[2]);
	for (i=0; i<max; i++){
		// Pregunta por pausa
		if(pausa == 1)
			Pausa();
		// Mueve motores		
		gpioWrite(pinPul,1);
		gpioDelay(6);
		gpioWrite(pinPul,0);
		gpioDelay(contvel);
		pasos++;
		// Si los pasos son mayores a 30,000 y los pasos llegaron al vértice, asigna decremento
		if (atoi(argv[2])>10000 && i==h) 
			auxa=atoi(argv[2])-h;
		// Si los pasos llegan al incremento temporal, aumenta velocidad
		if (i==(auxa-1)){
			auxa+=auxs;
			p+=auxs;
			contvel=round((pow((p-h),2)+4*a*k)/(4*a));	
		}			
	}
	// Corrige pasos si hubo algún sensor
	if ( ints == 1 )
		Retroceso();
	// Imprime pasos y finaliza
	printf("%i",pasos);
	gpioTerminate();
	return 0;
}
