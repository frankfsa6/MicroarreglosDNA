/*
gcc -Wall -pthread -o o o.c -lpigpio -lm
sudo ./o 0 0 0 # PosZ - PosX - PosY
*/
#include "pinesFunc.c"
// Principal de motores (posZ, posX, posY)
int main(int argc, char *argv[]){
	// Comprueba funcionamiento de pigpio y llamada correcta del programa
	if (gpioInitialise() < 0 || argc <= 3) 
		return -1;
	// Variables locales (contadores y coordenadas ZXY actuales)
	int i, j, origen[3] = {atoi(argv[1]),atoi(argv[2]),atoi(argv[3])};
	// Pines configurados XYZ y fin configurados
	PinesXConfig();
	PinesYConfig();
	PinesZConfig();
	PinesFinConfig();
	// Todos los motores en dirección al origen, empezando en Z
	gpioWrite(dirX,0);
	gpioWrite(dirY,0);
	gpioWrite(dirZ,0);
	pinPul = pulZ;
	pinDir = dirZ;
	//Inicia secuencia de pasos en los 3 ejes y cuenta pasos con base en los recibidos
	j = 0;
	while( j<3 && fin == 0 ){
		pasos = origen[j];
		i = 0;
		// Cuenta dos sensores por eje
		while( i<2 && fin == 0 ){
			// Mientras no haya interrupción de límite
			while(ints == 0 && fin == 0){
				// Pregunta por pausa
				if(pausa == 1)
					Pausa();
				// Mueve motores
				gpioWrite(pinPul,1);
				gpioDelay(6);
				gpioWrite(pinPul,0);
				gpioDelay(velOrig);
				pasos--;
			}
			// Imprime pasos en el primer sensado
			if(i==0)
				printf("%i\n", pasos+pasosRet);
			// Se aleja del sensor y empieza el siguiente sensado
			Retroceso(sensor);
			sensor++;
			i++;
			ints = 0;
		}
		// Al terminar eje Z, comienza X
		if(sensor == 2){
			pinPul = pulX;
			pinDir = dirX;
		}
		// Finaliza con eje Y
		else if(sensor == 4){
			pinPul = pulY;
			pinDir = dirY;
		}
		j++;
	}
	// Finaliza pigpio
	gpioTerminate();
	return 0;
}
