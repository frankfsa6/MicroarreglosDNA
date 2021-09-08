<?php
  set_time_limit(0);
  include("bd.php");
  // **********************  Importante ***********************
    // El codigo G trabaja con coordenadas mm, asi que se cambian las variables que calculan pasos entre lugares.
    // Es importante hacer correctamente las operaciones y agregar un parámetro para modificar
    // los pasos en C y el codigo G,
    // Las coordenadas en la base de datos están dadas en milímetros
    // Las coordenadas en código G están en mm, no en pasos
  class ArchG{
    private $pasos;		 //Pasos que se avanzarán en cada caso y se enviarán al archivo C
    private $actual;	 //Coordenadas posición actual en mm
    private $lugares;	 //Coordenadas de los lugares obtenidos en mm
    private $nombreG ; //Nombre del archivo en código G
    private $zslide = 1.5;	  // Número de milimetros -1.5mm que baja para poner puntos en el Slide
    private $zespera = 4;     // Aproximación en Z para lugares principales
    private $zsep = 10;     // Separación del sensor Z

    // Constructor que fija tipo de archivo e inicializa rutina
    public function __construct($nombreRutina, $info = null){
      $this -> DatosDB();
      $this -> NuevoArchivoG($nombreRutina, $info); // Crea un nuevo archivo de código G en la raiz
    }
    //Procesar el texto G y escribe en el archivo
    public function escribeArchivo($texto){
      $archivo = fopen("../G/".$this->nombreG,"a"); //abre la ruta del archivo, para más comodidad se deja en raíz
      fwrite($archivo, $texto);
      fclose($archivo);
      unset($archivo);
    }
    // Lleva motores al origen para iniciar proceso
    public function SensarOrigen(){
      $this->actual = [0,0, $this->zsep];
      $this->lugares["Origen"][2] += $this->zsep;
      $texto = "G00 Z-".$this->zsep." (Origen del sistema) \n";
      $texto .= "G00 X0 Y0 \n";
      $this->escribeArchivo($texto);
      unset($texto);
    }
    // Usa diagonales para lugares principales
    public function LugarD($lugar, $vxy, $vz, $typeZ, $extra = null){
      // Primero sube eje Z para evitar chocar: (2000 pasos/rev)/(8.02mm/rev) = 250 pasos/mm
      if($this->actual[2] != $this->zsep && $typeZ == "Lugar"){
        $texto = "G00 Z-".$this->zsep." \n";
        $this->escribeArchivo($texto);
        $this->actual[2] = $this->zsep;
      }
      // En caso de ser un slide, sube mínimo para moverse entre vidrios
      else if ($this->actual[2] != $this->lugares["Slide"][2] && $typeZ == "Slide"){
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
      $texto = "G00 X".$prox[0]." Y-".$prox[1]." (".$lugar.$extra.") \n";
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
      // Realiza primer movimiento de bajada
      $texto = "G01 Z-".$pasos." \n";
      $this->escribeArchivo($texto);
      unset($pasos,$texto);
      // Secuencia sube-baja para tiempos de espera
      for($i=0; $i<=$tiempo*3; $i++)
        $this->PinSB($i%2, 0.5);
    }
    //Enciende o apaga la bomba de vacío
    public function BVac($estado){
      $texto = ($estado == 1 )? "M03 (Enciende bomba de vacío)\n" : "M05 (Apaga la bomba de vacío)\n";
      $this->escribeArchivo($texto);
      unset($texto);
    }
    //Hace los toques de limpieza seguidos
    public function ToquesLimpieza($toques){
      for($i=0; $i<$toques; $i++)
        $this->Toque($i,0.5);
    }
    //Hace toda la rutina de un ciclo de insertar los puntos en todos los slides (vidrios)
    public function InsertarPuntosSlides($columnasPlaca,$filasPlaca,$vxy,$vz,$DupDots,$YSpace,$YSlideDist,$XSlideDist){
      for ($i=1; $i<=$columnasPlaca; $i++){
        for ($j=1; $j<=$filasPlaca; $j++){
          // Primera vez llega a retícula
          if ($i==1 && $j==1)
            $this->LugarD("Slide",$vxy,$vz,"Lugar"," $i x $j");
          else
            $this->LugarD("Slide",$vxy,$vz,"Slide"," $i x $j");
          // Pone puntos simples o duplicados
          for($k=0; $k<$DupDots; $k++)
            $this->Toque($k,$YSpace);
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
    // Hace toda la rutina de un ciclo de insertar los números en todos los slides (vidrios)
    public function InsertarNumSlides($columnasPlaca, $filasPlaca, $vxy, $vz, $numImp, $numDist, $YSlideDist, $XSlideDist){
      // Matrices de puntos 5x3 donde no hay puntito, yendo de 9 a 2
      $mat5x3 = [ [7,9,14], [7,9], [6,7,8,9,12,13,14,15], [2,7,9], [2,7,9,14], [6,7,9,10,14,15], [7,9,12,14],[4,7,9,12] ];
      $vpunto = 150;
      // Recorre la retícula completa en zigzag
      for ($i=1; $i<=$columnasPlaca; $i++){
        for ($j=1; $j<=$filasPlaca; $j++){
          // Primera vez se va al slide en 1.5mmZ, y después al slide a colocar número
          if ($i==1 && $j==1)
            $this->LugarD("Slide",$vxy,$vz,"Lugar");
          else
            $this->LugarD("Slide",$vxy,$vz,"Slide");
          // Siempre pone el puntito 1 en todos los números y fija altura en 1.5 mm
          $this->PinSB(1, 1.5);
          $this->PinSB(0, 1.5);
          $multip = 1;
          $dirX = 1;
          for($k=2; $k<=15; $k++){
            // Mueve en dirección Y cada 5 puntitos para siguiente fila
            if( $k==6 || $k==11 ){
              // Mueve en X hasta llegar al extremo si multiplicador quedó pendiente
              if( $multip!=1 ){
                // Caso especial para ajustar número 4
                if( $numImp==5 && $k==11 )
                  $multip--;
                $this->actual[0] += ( $dirX==0 )?-$multip*$numDist:$multip*$numDist;
                $texto = "G00 X".$this->actual[0]." \n";
                $this->escribeArchivo($texto);
                unset($texto);
              }
              // Mueve en Y hacia adelante
              $this->actual[1] += $numDist;
              $texto = "G00 Y-".$this->actual[1]." \n";
              $this->escribeArchivo($texto);
              unset($texto);
              // Cambia dirección en X y actualiza multiplicador con coordenada actual Y
              $dirX = ( $k%2==0 )?0:1;
              $multip = 1;
            }
            // Si la gotita no está en arreglo del número dado, se pone
            if( !in_array($k, $mat5x3[$numImp]) ) {
              // Avanza mmX acumulados si no acaba de avanzar fila y actualiza coordenada X
              if( $k!=6 && $k !=11 ){
                $this->actual[0] += ( $dirX==0 )?-$multip*$numDist:$multip*$numDist;
                $texto = "G00 X".$this->actual[0]." \n";
                $this->escribeArchivo($texto);
                unset($texto);
              }
              // Pone gotita instantáneamente y reinicia multiplicador
              $this->PinSB(1, 1.5);
              $this->PinSB(0, 1.5);
              $multip = 1;
            }
            // Aumenta multiplicador al encontrar vacío si no acaba de avanzar fila
            else if( $k!=6 && $k !=11 )
              $multip++;
          }
          // Mueve +Y en columnas impares de retícula
          if ($i%2 == 1 && $j!=$filasPlaca)
            $this->ActualizaCoords(1,$YSlideDist,"Slide");
          // Mueve -Y en columnas impares de retícula
          elseif ($i%2 == 0 && $j!=$filasPlaca)
            $this->ActualizaCoords(1,-$YSlideDist,"Slide");
        }
        // Al terminar filas, avanza columna a la derecha de la retícula
        $this->ActualizaCoords(0,$XSlideDist,"Slide");
      }
      unset($multip, $dirX);
    }
    // Baja a poner gotita, sube y avanza en Y
    public function Toque($NumToque,$Ydist){
      $vv = 250;
      // Primera vez baja 1.5 mm a poner gotitas, después 0.5 mm al estar dentro del vidrio
      if( $NumToque == 0 )
        $this->actual[2] += 1.5;
      else
        $this->actual[2] += 0.5;
      $texto = "G00 Z-".$this->actual[2]." \n";
      $this->escribeArchivo($texto);
      unset($texto);
      // Sube 0.5 mm en altura dentro del vidrio
      $this->actual[2] -= 0.5;
      $texto = "G00 Z-".$this->actual[2]." \n";
      $this->escribeArchivo($texto);
      unset($texto);
      // Avanza Y mm 
      $this->actual[1] += $Ydist;
      $texto = "G00 Y-".$this->actual[1]." \n";
      $this->escribeArchivo($texto);
      unset($texto);
    }
    // Sube o baja el pin
    public function PinSB($dir,$mm){
      if ($mm == "Cambio")
        $mm = $this->actual[2]-$this->zsep;
      if ($dir==0)
        $this->actual[2] -= $mm;
      else
        $this->actual[2] += $mm;
      //Movimiento en Z del pin
      $texto = "G00 Z-".$this->actual[2]." \n";
      $this->escribeArchivo($texto);
      unset($texto, $mm);
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
      else if ($cambioPlaca && $cambioVidrio)
        $texto .= "(Pausa para cambio de placa y vidrio) \n";
      else if($cambioVidrio)
          $texto .= "(Pausa para cambio de vidrio) \n";
      else
          $texto .= "(Pausa para cambio de placa) \n";
      $this->escribeArchivo($texto);
      unset($texto);
    }
    // Función para reescribir archivo G
    private function NuevoArchivoG($nombreRutina, $info = null){
      // Busca si existe el archivo G
      $this->nombreG = $nombreRutina.".nc";
      if( file_exists("../G/".$this->nombreG) )
        unlink("../G/".$this->nombreG);
      // Obtiene datos de la base si es rutina principal
      if( $info == null ){
        // Obtiene datos de la base de datos para ponerlos en el header como comentarios
        $pines = getDBdata("pines");
        $lavado = getDBdata("lavado");
        $slide = getDBdata("slide");
        $reti = getDBdata("reticula");
        //Se dan los datos iniciales
        $texto = "(Rutina: $nombreRutina)\n";
        if($pines != null)
          $texto .= "(Pines: ".$pines["PinesX"]."x4)\n";
        if($reti != null){
          $texto .= "(Puntos por arreglo: ".$reti["XDots"]."x".$reti["YDots"]." con ".$reti["DuplicateDots"]." dup)\n";
          $texto .= "(Coords y espaciado: ".$reti["XCoords"]."x".$reti["YCoords"]."mm con ".$reti["XSpace"]."x".$reti["YSpace"]."um)\n";
          $texto .= "(Placas a realizar: ".$reti["TotalPlates"].")\n";
        }
        if($slide != null)
          $texto .= "(Slides a imprimir: ".$slide["filasplaca"]."x".$slide["columnasplaca"].")\n";
        if($lavado != null)
          $texto .= "(Ciclos de lavado: ".$lavado["ciclos"]." con ".$lavado["oscilaciones"]." osc)\n";
        unset($pines, $lavado, $slide, $reti);
      }
      // Datos fijos si es tipo numeración o chips múltiples
      else {
        $info = explode(",",$info);
        $texto = "(Rutina específica: ".$info[0].")\n";
        $texto .= "(Distribución de pines: 6x1)\n";
        if( (int)$info[1]<10 )
          $texto .= "(Sección por imprimir: superior)\n";
        else
          $texto .= "(Sección por imprimir: inferior girado)\n";
        $texto .= "(Slides a imprimir: ".$info[2]."x".$info[3].")\n";
        $texto .= "(Ciclos de lavado y oscilaciones: 3,4)\n";
      }
      // Guarda comentarios de inicio
      $texto .= "\n";
      $archivo = fopen("../G/".$this->nombreG,"a");
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
