<?php
  set_time_limit(0);
  include("bd.php");
  // **********************  Importante ***********************
    // El codigo G trabaja con coordenadas, asi que se cambian las variables que calculan pasos entre lugares
    // Por coordenadas. Es importante hacer correctamente las operaciones y agregar un paramatro para modificar
    // los pasos en C y el codigo G,
    // Las coordenadas en la base de datos están dadas en milímetros
    // Las coordenadas en código G están en mm, no en pasos
    // Se agregan los 0.00 para darle formato al codigo G
  class ArchG{
    private $pasos;		 //Pasos que se avanzarán en cada caso y se enviarán al archivo C
    private $actual;	 //Coordenadas posición actual en mm
    private $lugares;	 //Coordenadas de los lugares obtenidos en mm
    private $nombreG ; //Nombre del archivo en código G
    private $zslide = 1.5;	  // Número de milimetros -1.5mm que baja para poner puntos en el Slide
    private $zespera = 4;     // Aproximación en Z para lugares principales
    private $zsep = 10;     // Separación del sensor Z

    // Constructor que fija tipo de archivo e inicializa rutina
    public function __construct(string $nombreRutina){
      $this -> DatosDB();
      $this -> NuevoArchivoG($nombreRutina); // Crea un nuevo archivo de código G en la raiz
    }
    //Procesar el texto G y escribe en el archivo
    public function escribeArchivo(string $texto){
      $archivo = fopen("../".$this->nombreG,"a"); //abre la ruta del archivo, para más comodidad se deja en raíz
      fwrite($archivo, $texto);
      fclose($archivo);
      unset($archivo);
    }
    // Lleva motores al origen para iniciar proceso
    public function SensarOrigen(){
      $this->actual = [0,0, $this->zsep];
      $texto = "G01 Z0 F500 (Origen del sistema) \n";
      $texto .= "G01 Z-".$this->zsep." F500 \n";
      $texto .= "G01 X0 Y0 F500 \n";
      $this->escribeArchivo($texto);
      unset($texto);
    }
    // Usa diagonales para lugares principales
    public function LugarD($lugar,$vxy,$vz,$typeZ){
      // Primero sube eje Z para evitar chocar: (2000 pasos/rev)/(8.02mm/rev) = 250 pasos/mm
      if($this->actual[2] != $this->zsep && $typeZ == "Lugar"){
        $texto = "G00 Z-".$this->zsep." (".$lugar.") \n";
        $this->escribeArchivo($texto);
        $this->actual[2] = $this->zsep;
      }
      // En caso de ser un slide, sube mínimo para moverse entre vidrios
      elseif ($this->actual[2] != $this->lugares["Slide"][2] && $typeZ == "Slide"){
        $prox = $this->lugares["Slide"][2];
        // Modifica posición actual
        $this->actual[2] = $prox;
        $texto = "G00 Z-".$prox." \n";
        $this->escribeArchivo($texto);
        unset($prox, $texto);
      }
      // Compara lugares próximos XY
      for($i=0; $i<2; $i++){
        // Adquiere el proximo lugar al que se va a mover
        $prox[$i] = $this->lugares[$lugar][$i];
        $this->actual[$i] = $prox[$i];
      }
      $texto = "G00 X".$prox[0]." Y-".$prox[1]." \n";
      $this->escribeArchivo($texto);
      unset($prox, $texto);
      // Al ser lugares definidos, finaliza eje Z para llegar al lugar
      if ($typeZ == "Lugar"){
        $prox = $this->lugares[$lugar][2];
        // Modifica posición actual
        $this->actual[2] = $prox;
        $texto = "G00 Z-".$prox." \n";
        $this->escribeArchivo($texto);
        unset($prox, $texto);
      }
    }
    // Realiza oscilaciones en lavado
    public function Lavado($osc){
        // Oscila alrededor de 4 mm
        $mov = 4.00;
        for($i=0; $i<$osc*2; $i++){
          // Realiza movimiento
          $pasosG = ($i%2 == 0) ? $this->actual[0]+$mov : $this->actual[0];
          $texto = "G00 X".$pasosG." \n";
          $this->escribeArchivo($texto);
          unset($texto, $pasosG);
        }
        $pasosG =  $this->actual[0];
        unset($texto, $pasosG);
    }
    // Realiza vacío después de lavado o la toma de muestra con un tiempo de espera
    public function Espera($tiempo){
      //Baja para limpieza 4 mm
      $pasos = 4 + $this->actual[2];
      $this->actual[2] += 4;
      // Realiza movimiento de bajada
      $texto = "G01 Z-".$pasos." F500 \n";
      $this->escribeArchivo($texto);
      unset($pasos,$texto);
    }
    //Enciende o apaga la bomba de vacío
    public function BVac($estado){
      $texto = ($estado == 1 )? "M03 (Enciende bomba de vacio)\n" : "M05 (Apaga la bomba de vacio)\n";
      $this->escribeArchivo($texto);
      unset($texto);
    }
    //Hace los toques de limpieza seguidos
    public function ToquesLimpieza($toques){
      for($i=0; $i<$toques; $i++)
        $this->InsertarPunto($i,0.5);
    }
    //Hace toda la rutina de un ciclo de insertar los puntos en todos los slides (vidrios)
    public function InsertarPuntosSlides($columnasPlaca,$filasPlaca,$vxy,$vz,$DupDots,$YSpace,$YSlideDist,$XSlideDist){
      for ($i=1; $i<=$columnasPlaca; $i++){
        for ($j=1; $j<=$filasPlaca; $j++){
          // Primera vez llega a retícula
          if ($i==1 && $j==1)
            $this->LugarD("Slide",$vxy,$vz,"Lugar");
          $this->LugarD("Slide",$vxy,$vz,"Slide");
          // Pone puntos simples o duplicados
          for($k=0; $k<$DupDots; $k++)
            $this->InsertarPunto($k,$YSpace);
          // En columna impar, avanza en Y
          if ($i%2 == 1 && $j!=$filasPlaca)
            $this->ActualizaCoords(1,$YSlideDist,"Slide");
          // En columna par, retrocede en Y
          elseif ($i%2 == 0 && $j!=$filasPlaca)
            $this->ActualizaCoords(1,-$YSlideDist,"Slide");
        }
        // Al acabar la columna, mueve X
        $this->ActualizaCoords(0,$XSlideDist,"Slide");
      }
    }
    // Realiza la inserción del punto en el vidrio del Slide
    //Baja el pin de 3mm a 0mm (-3mm Z)
    //Si hay desplazamiento en Y llama a la función: sube:2.5mm-sube/avanza:2.5mm-avanza:y-5mm-baja/avanza:2.5mm-baja:2.5mm (ymm Y)
    //Sube el pin de 0mm a 5mm (+2mm Z)
    public function InsertarPunto($NumToque,$Ydist){
      if ($NumToque == 0)
        $this->PinSB(1,1.5);
      else
        $this->Toque($Ydist);
    }
    // Sube, hace cúpula y baja instantáneamente
    public function Toque($Ydist){
      $vv = 250;
      // Sube 1.5 mm en Z iniciales
      $pasos = $this->actual[2]-1.5;
      $texto = "G00 Z-".$pasos." \n";
      $this->escribeArchivo($texto);
      unset($pasos,$texto);
      // Avanza Y mm 
      $pasos = $this->actual[1]+$Ydist;
      $texto = "G00 Y-".$pasos." \n";
      $this->escribeArchivo($texto);
      unset($pasos,$texto);
      $this->actual[1] += $Ydist;
      // Baja a poner gotita
      $pasos = $this->actual[2]+1.5;
      $texto = "G00 Z-".$pasos." \n";
      $this->escribeArchivo($texto);
      unset($pasos,$texto);
    }
    // Sube o baja el pin
    public function PinSB($dir,$mm){
      if ($mm == "Cambio")
        $mm = $this->actual[2]-$this->zsep;
      if ($dir==0)
        $this->actual[2] -= $mm;
      else
        $this->actual[2] += $mm;
      $pasos =  $this->actual[2];
      //Movimiento en Z del pin
      $texto = "G00 Z-".$pasos." \n";
      $this->escribeArchivo($texto);
      unset($pasos,$texto, $mm);
    }
    // Consigue pasos por eje y coordenadas de "lugares"
    private function DatosDB(){
      $conexion = ConectarBD();
      // Comprueba conexión para los datos
      if( $conexion == false ){
        $this->pasosMM = [0,0,0];
        $this->lugares = ["Origen"=>[0,0,$this->zsep]];
      }
      else{
        // Pide datos de pasos de motor por eje
        mysqli_set_charset($conexion,"utf8");
        $sql = "SELECT id,valor FROM raspberry WHERE tipo != 'gpio'";
        $res = mysqli_query($conexion, $sql);
        if( $res->num_rows !=0 ) {
          // Asigna valores de base
          while ( $dato = mysqli_fetch_assoc($res) )
            $cuentas[$dato['id']] = (float) $dato['valor'];
          mysqli_free_result($res);
          // Realiza cuentas para XYZ
          $ejeBD = ["X","Y","Z"];
          for($i = 0; $i<3; $i++)
            $this->pasosMM[$i] = $cuentas['pasosRev'.$ejeBD[$i]]/$cuentas['tor'.$ejeBD[$i]];
        }
        // Pide datos de coordenadas de lugares principales
        $sql = "SELECT * FROM config";
        $res = mysqli_query($conexion, $sql);
        if( $res->num_rows !=0 ) {
          while( $dato = mysqli_fetch_assoc($res) )
            $this->lugares[$dato['nombre']] = [$dato["x"], $dato["y"], $dato["z"]];
          mysqli_free_result($res);
      }
        //Configura auxiliares que se utilizarán en la inserción de puntos de slides, limpieza y muestra
        $this->lugares["Vacío"][2] -= $this->zespera;
        $this->ReiniciaCoords(2,"Slide","Retícula");
        $this->lugares["Slide"][2] = $this->lugares["Retícula"][2]-$this->zslide;
        $this->ReiniciaCoords(2,"Toque de limpieza","Limpieza");
        $this->lugares["Toque de limpieza"][2] = $this->lugares["Limpieza"][2]-$this->zslide;
        $this->ReiniciaCoords(2,"Toma de muestra","Muestra");
        $this->lugares["Toma de muestra"][2] = $this->lugares["Muestra"][2]-$this->zespera;
      }
    }
    //Reinicia los parámetros del lugar indicado (0 en X,  1 en Y, 2 en XY )
    public function ReiniciaCoords($i,$LugarRein,$ValorRein){
      if ($i == 0 || $i == 2)
          $this->lugares[$LugarRein][0]=$this->lugares[$ValorRein][0];
      if ($i == 1 || $i == 2)
          $this->lugares[$LugarRein][1]=$this->lugares[$ValorRein][1];
    }
    //Actualiza los datos de las coordenadas del lugar indicado (eje, mm, lugar)
    public function ActualizaCoords($ejeN, $distMM, $Lugar){
      $this->lugares[$Lugar][$ejeN] += $distMM;
    }
    //Actualiza las pausas por cambio de placa o de vidrio de limpieza
    public function ActualizaPausa($cambioPlaca,$cambioVidrio){
      $texto = "M00 ";
      if(!$cambioPlaca && !$cambioVidrio)
        $texto .= "(Pausa para humedecer pines) \n";
      else{
        if($cambioVidrio)
          $texto .= "(Pausa para cambio de vidrio) \n";
        if($cambioPlaca)
          $texto .= "(Pausa para cambio de placa) \n";
      } 
      $this->escribeArchivo($texto);
      unset($texto);
    }
    //Funcion para borrar del archivo G
    private function NuevoArchivoG(string $nombreRutina){
      //Busca si existe el archivo G
      $this->nombreG = $nombreRutina.".nc";
      if(file_exists("../".$this->nombreG))
        unlink("../".$this->nombreG);
      // Obtiene datos de la base de datos para ponerlos en el header como comentarios
      $pines = getDBdata("pines");
      $lavado = getDBdata("lavado");
      $slide = getDBdata("slide");
      //Se dan los datos iniciales
      $texto = "(Rutina:".$nombreRutina.")\n";
      if($pines != null){
        $texto .= "(Pines en XY:".$pines["PinesX"].", 4)\n";
        unset($pines);
      }
      if($lavado != null){
        $texto .= "(Ciclos de lavado:".$lavado["ciclos"].", oscilaciones:".$lavado["oscilaciones"].")\n";
        unset($lavado);
      }
      if($slide != null){
        $texto .= "(Slides en XY:".$slide["filasplaca"].", ".$slide["columnasplaca"].")\n";
        unset($slide);
      }
      $archivo = fopen("../".$this->nombreG,"a");
      fwrite($archivo, $texto);
      fclose($archivo);
      unset($texto, $archivo);
    }
    // Finaliza rutinas
    public function FinCodigoG(){
      $texto = "M05 (Fin de la rutina)";
      $this->escribeArchivo($texto);
    }
  }
?>
