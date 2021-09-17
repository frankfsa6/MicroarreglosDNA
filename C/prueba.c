/*
gcc -Wall -pthread -o prueba prueba.c -lpigpio -lm
sudo ./prueba 1 # Entrada asociada para probar
*/
#include "pinesFunc.c"
// Principal de motores (posZ, posX, posY)
int main(int argc, char *argv[]){
	int espSeg = 120;
	// Comprueba funcionamiento de pigpio y llamada correcta del programa
	if (gpioInitialise() < 0 || argc <= 1){
		printf("Error en el sistema principal");
		return -1;
	}
	// Configura paro por software
	int i = atoi(argv[1]);
	gpioSetMode(finCodC,PI_INPUT);
	gpioSetPullUpDown(finCodC,PI_PUD_DOWN);
	gpioSetAlertFunc(finCodC, IntsFin);
	// Busca opción dada en sensores para pin
	switch(i){
		case 1:
			PinesXConfig();
			break;
		case 2:
			PinesYConfig();
			break;
		case 3:
			PinesZConfig();
			break;
		case 4:
			gpioSetMode(botE,PI_INPUT);
			gpioSetPullUpDown(botE,PI_PUD_DOWN);
			gpioSetAlertFunc(botE, IntsBotE);	
			break;
	}
	// Permanece en ciclo por 1 minuto o hasta que detecte interrupción
	i=0;
	while( i<espSeg && ints == 0 && fin == 0 && pausa == 0 ){
		time_sleep(1);
		i++;
	}
	// Manda mensaje dependiendo de la situación
	if(fin == 1)
		printf("Prueba detenida por el usuario");
	else{
		if(i == espSeg)
			printf("Tiempo de espera finalizado. Revise sus conexiones y los pines asociados en configuración");
		else{
			if(ints == 1)
				printf("Sensor de límite detectado correctamente");
			else
				printf("Botón de emergencia detectado correctamente");
		}
	}
	// Finaliza pigpio
	gpioTerminate();
	return 0;
}
