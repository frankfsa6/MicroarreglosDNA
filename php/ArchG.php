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
    private $zslide = 1.5;	// Número de milimetros -1.5mm que baja para poner puntos en el Slide
    private $zespera = 4;   // Aproximación en Z para lugares principales

    // Constructor que fija tipo de archivo e inicializa rutina
    public function __construct($nombreRutina, $info = null){
      // Rutina principal
      if( $info == null )
        $this -> DatosDB();
      // Rutina específica
      else{
        $temp = explode(",",$info);
        $this -> DatosDB( $temp[0] );
      }
      $this -> NuevoArchivoG($nombreRutina, $info); 
    }
    // Procesar el texto G y escribe en el archivo
    public function escribeArchivo($texto){
      $archivo = fopen("../G/".$this->nombreG,"a"); //abre la ruta del archivo, para más comodidad se deja en raíz
      fwrite($archivo, $texto);
      fclose($archivo);
      unset($archivo);
    }
    // Lleva motores al origen para iniciar proceso
    public function SensarOrigen(){
      $this->actual = $this->lugares["Origen"];
      $texto = "G00 Z-".$this->actual[2]." (Origen del sistema) \n";
      $texto .= "G00 X0 Y0 \n";
      $this->escribeArchivo($texto);
      unset($texto);
    }
    // Usa diagonales para lugares principales
    public function LugarD($lugar, $vxy, $vz, $typeZ, $extra = null){
      // En lugares, primero sube eje Z para evitar chocar
      if($this->actual[2] != $this->lugares["Origen"][2] && $typeZ == "Lugar"){
        $this->actual[2] = $this->lugares["Origen"][2];
        $texto = "G00 Z-".$this->actual[2]." \n";
        $this->escribeArchivo($texto);
        unset($texto);
      }
      // Al ser un slide, sube mínimo para moverse entre vidrios
      else if ($this->actual[2] != $this->lugares["Slide"][2] && $typeZ == "Slide"){
        $this->actual[2] = $this->lugares["Slide"][2];
        $texto = "G00 Z-".$this->actual[2]." \n";
        $this->escribeArchivo($texto);
        unset($texto);
      }
      // Adquiere y escribe XY del próximo lugar al que se va a mover
      for($i=0; $i<2; $i++)
        $this->actual[$i] = $this->lugares[$lugar][$i];
      $texto = "G00 X".$this->actual[0]." Y-".$this->actual[1]." (".$lugar.$extra.") \n";
      $this->escribeArchivo($texto);
      unset($texto);
      // Si son lugares definidos, finaliza eje Z para llegar al lugar
      if ($typeZ == "Lugar"){
        $this->actual[2] = $this->lugares[$lugar][2];
        $texto = "G00 Z-".$this->actual[2]." \n";
        $this->escribeArchivo($texto);
        unset($texto);
      }
    }
    // Realiza oscilaciones en lavado
    public function Lavado($osc){
      // Oscila alrededor de 4 mm en X únicamente
      $mov = 4;
      for($i=0; $i<$osc*2; $i++){
        $texto = ($i%2 == 0) ? "G00 X".$this->actual[0]+$mov." \n" : "G00 X".$this->actual[0]." \n";
        $this->escribeArchivo($texto);
        unset($texto);
      }
    }
    // Simula tiempo de espera con vibraciones en vacío y toma de muestra
    public function Espera($tiempo){
      //Baja 4mm como acercamiento
      $this->actual[2] += 4;
      $texto = "G00 Z-".$this->actual[2]." \n";
      $this->escribeArchivo($texto);
      unset($texto);
      // Secuencia sube-baja para tiempos de espera
      for($i=0; $i<=$tiempo*3; $i++)
        $this->PinSB($i%2, 0.5);
    }
    // Enciende o apaga la bomba de vacío
    public function BVac($estado){
      $texto = ($estado == 1 )? "M03 (Enciende bomba de vacío)\n" : "M05 (Apaga la bomba de vacío)\n";
      $this->escribeArchivo($texto);
      unset($texto);
    }
    // Hace los toques de limpieza seguidos cada 0.5mmY
    public function ToquesLimpieza($toques){
      $sepY = 0.5;
      for($i=0; $i<$toques; $i++)
        $this->Toque($i, $sepY, $toques);
    }
    // Inserta los puntos Y en todos los slides (vidrios)
    public function InsertarPuntosSlides($columnasPlaca,$filasPlaca,$vxy,$vz,$DupDots,$YSpace,$YSlideDist,$XSlideDist){
      for($i=1; $i<=$columnasPlaca; $i++){
        for($j=1; $j<=$filasPlaca; $j++){
          // Primera vez llega a retícula, después entre vidrios con misma altura
          if($i==1 && $j==1)
            $this->LugarD("Slide",$vxy,$vz,"Lugar"," $i x $j");
          else
            $this->LugarD("Slide",$vxy,$vz,"Slide"," $i x $j");
          // Pone puntos simples o duplicados
          for($k=0; $k<$DupDots; $k++)
            $this->Toque($k, $YSpace, $DupDots);
          // En columna impar, avanza en Y
          if($i%2 == 1 && $j!=$filasPlaca)
            $this->ActualizaCoords(1,$YSlideDist,"Slide");
          // En columna par, retrocede en Y
          elseif($i%2 == 0 && $j!=$filasPlaca)
            $this->ActualizaCoords(1,-$YSlideDist,"Slide");
        }
        // Al acabar la columna, mueve X
        $this->ActualizaCoords(0,$XSlideDist,"Slide");
      }
    }
    // Insertar los números en todos los slides (vidrios)
    public function InsertarNumSlides($columnasPlaca, $filasPlaca, $vxy, $vz, $numImp, $numDist, $YSlideDist, $XSlideDist){
      // Matrices de puntos 5x3 donde no hay puntito, yendo de 9 a 2
      $mat5x3 = [ [7,9,14], [7,9], [6,7,8,9,12,13,14,15], [2,7,9], [2,7,9,14], [6,7,9,10,14,15], [7,9,12,14],[4,7,9,12] ];
      $vpunto = 150;
      // Recorre la retícula completa en zigzag
      for ($i=1; $i<=$columnasPlaca; $i++){
        for ($j=1; $j<=$filasPlaca; $j++){
          // Primera vez llega a retícula, después entre vidrios con misma altura
          if($i==1 && $j==1)
            $this->LugarD("Slide",$vxy,$vz,"Lugar"," $i x $j");
          else
            $this->LugarD("Slide",$vxy,$vz,"Slide"," $i x $j");
          // Siempre pone el puntito 1 en todos los números y fija altura en 0.5 mm
          $this->PinSB(1, 1.5);
          $this->PinSB(0, 0.5);
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
                $this->actual[0] += ( $dirX==0 ) ? -$multip*$numDist : $multip*$numDist;
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
                $this->actual[0] += ( $dirX==0 ) ? -$multip*$numDist : $multip*$numDist;
                $texto = "G00 X".$this->actual[0]." \n";
                $this->escribeArchivo($texto);
                unset($texto);
              }
              // Pone gotita instantáneamente y reinicia multiplicador
              $this->PinSB(1, 0.5);
              $this->PinSB(0, 0.5);
              $multip = 1;
            }
            // Aumenta multiplicador al encontrar vacío si no acaba de avanzar fila
            else if( $k!=6 && $k !=11 )
              $multip++;
          }
          // Mueve +Y en columnas impares de retícula
          if ($i%2 == 1 && $j!=$filasPlaca)
            $this->ActualizaCoords(1,$YSlideDist,"Slide");
          // Mueve -Y en columnas pares de retícula
          elseif ($i%2 == 0 && $j!=$filasPlaca)
            $this->ActualizaCoords(1,-$YSlideDist,"Slide");
        }
        // Al terminar filas, avanza columna a la derecha de la retícula
        $this->ActualizaCoords(0,$XSlideDist,"Slide");
      }
      unset($multip, $dirX);
    }
    // Hace toda la rutina de un ciclo de insertar los chips dobles en todos los slides (vidrios) 
    public function InsertarChipsSlides($columnasPlaca,$filasPlaca,$vxy,$vz,$puntosDup,$XMuestraDist,$YDist,$YSlideDist,$XSlideDist){
      for($i=1; $i<=$columnasPlaca; $i++){
        for($j=1; $j<=$filasPlaca; $j++){
          // Primera vez llega a retícula o se mueve con altura fija entre slides
          if($i==1 && $j==1)
            $this->LugarD("Slide",$vxy,$vz,"Lugar"," $i x $j");
          else
            $this->LugarD("Slide",$vxy,$vz,"Slide"," $i x $j");
          // Pone primeros puntos
          for($k=0; $k<$puntosDup; $k++)
            $this->Toque($k, $YDist, $puntosDup);
          // Al ser fila par avanza a la izquierda, de lo contrario, a la derecha
          if( $j%2==0 )
            $this->ActualizaCoords(0,-$XMuestraDist,"Slide");
          else
            $this->ActualizaCoords(0,$XMuestraDist,"Slide");
          $this->LugarD("Slide",$vxy,$vz,"Slide");
          // Pone segundos puntos
          for($k=0; $k<$puntosDup; $k++)
            $this->Toque($k, $YDist, $puntosDup);
          // En columna impar, avanza en Y
          if($i%2 == 1 && $j!=$filasPlaca)
            $this->ActualizaCoords(1,$YSlideDist,"Slide");
          // En columna par, retrocede en Y
          elseif($i%2 == 0 && $j!=$filasPlaca)
            $this->ActualizaCoords(1,-$YSlideDist,"Slide");
        }
        // Al acabar la columna, mueve X distancia de slide; si la fila es impar, regresa el extra de separación doble
        $this->ActualizaCoords(0,$XSlideDist,"Slide");
        if( $filasPlaca%2==1 )
          $this->ActualizaCoords(0,-$XMuestraDist,"Slide");
      }
    }
    // Baja a poner gotita, sube y avanza en Y
    public function Toque($NumToque, $Ydist, $fin){
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
      // Avanza Y mm si no es última vez de toque
      if( $NumToque != $fin-1 ){
        $this->actual[1] += $Ydist;
        $texto = "G00 Y-".$this->actual[1]." \n";
        $this->escribeArchivo($texto);
        unset($texto);
      }
    }
    // Sube o baja el pin dados los mm
    public function PinSB($dir,$mm){
      if ($mm == "Cambio")
        $mm = $this->actual[2]-$this->lugares["Origen"][2];
      //Asigna dirección del movimiento en Z
      if ($dir==0)
        $this->actual[2] -= $mm;
      else
        $this->actual[2] += $mm;
      $texto = "G00 Z-".$this->actual[2]." \n";
      $this->escribeArchivo($texto);
      unset($texto);
    }
    // Consigue pasos por eje y coordenadas de "lugares"
    private function DatosDB($tipoPin = null){
      // Comprueba conexión para los datos
      $conexion = ConectarBD();
      if( $conexion == true ){
        // Pide datos de coordenadas de lugares principales
        mysqli_set_charset($conexion,"utf8");
        // En caso de ser rutina principal, obtiene el tipo de pin de la base de datos
        if($tipoPin == null){
          $sql = "SELECT IDPin FROM pines WHERE ID='".$_SESSION['ID']."'";
          if ( mysqli_query($conexion, $sql)->num_rows !=0 ) {
            $res = mysqli_query($conexion, $sql);
            while ( $dato = mysqli_fetch_assoc($res) )
              $tipoPin = $dato['IDPin'];
            mysqli_free_result($res);
          }
        }
        //Obtiene el valor seleccionado del tipo de pines
        $sql = "SELECT * FROM config WHERE IDPin=".$tipoPin;
        $res = mysqli_query($conexion, $sql);
        if( $res->num_rows !=0 ) {
          while( $dato = mysqli_fetch_assoc($res) )
            $this->lugares[$dato['nombre']] = [$dato["x"], $dato["y"], $dato["z"]];
          mysqli_free_result($res);
        }
        //Configura auxiliares para la inserción de puntos de slides, limpieza y muestra
        $this->lugares["Vacío"][2] -= $this->zespera;
        $this->ReiniciaCoords(2,"Slide","Retícula");
        $this->lugares["Slide"][2] = $this->lugares["Retícula"][2]-$this->zslide;
        $this->ReiniciaCoords(2,"Toque de limpieza","Limpieza");
        $this->lugares["Toque de limpieza"][2] = $this->lugares["Limpieza"][2]-$this->zslide;
        $this->ReiniciaCoords(2,"Toma de muestra","Muestra");
        $this->lugares["Toma de muestra"][2] = $this->lugares["Muestra"][2]-$this->zespera;
      }
    }
    // Reinicia los parámetros del lugar indicado (0 en X,  1 en Y, 2 en XY )
    public function ReiniciaCoords($i, $copia, $original){
      if ($i == 0 || $i == 2)
        $this->lugares[$copia][0] = $this->lugares[$original][0];
      if ($i == 1 || $i == 2)
        $this->lugares[$copia][1] = $this->lugares[$original][1];
    }
    // Actualiza los datos de las coordenadas del lugar indicado (eje, mm, lugar)
    public function ActualizaCoords($eje, $mm, $lugar){
      $this->lugares[$lugar][$eje] += $mm;
    }
    // Genera las pausas requeridas para los cambios
    public function ActualizaPausa($cambioPlaca, $cambioVidrio, $extra = null){
      $texto = "M00 ";
      if(!$cambioPlaca && !$cambioVidrio)
        $texto .= "(Pausa para humedecer pines) \n";
      else if ($cambioPlaca && $cambioVidrio)
        $texto .= "(Pausa para cambio de placa y vidrio".$extra.") \n";
      else if($cambioVidrio)
          $texto .= "(Pausa para cambio de vidrio".$extra.") \n";
      else
          $texto .= "(Pausa para cambio de placa".$extra.") \n";
      $this->escribeArchivo($texto);
      unset($texto);
    }
    // Función para crear archivo G
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
        if( $pines["IDPin"]=="1" )
          $texto .= "(Pines: ".$pines["PinesX"]."x4 tipo cerámico)\n";
        else
          $texto .= "(Pines: ".$pines["PinesX"]."x4 tipo acero)\n";
        $texto .= "(Puntos por arreglo: ".$reti["XDots"]."x".$reti["YDots"]." con ".$reti["DuplicateDots"]." dup)\n";
        $texto .= "(Coords y espaciado: ".$reti["XCoords"]."x".$reti["YCoords"]."mm con ".$reti["XSpace"]."x".$reti["YSpace"]."um)\n";
        $texto .= "(Placas a realizar: ".$reti["TotalPlates"].")\n";
        $texto .= "(Slides a imprimir: ".$slide["columnasplaca"]."x".$slide["filasplaca"].")\n";
        $texto .= "(Ciclos de lavado: ".$lavado["ciclos"]." con ".$lavado["oscilaciones"]." osc)\n";
        $texto .= "(Tiempo de muestra, vacío y último vacío: ".$lavado["tmuestra"].", ".$lavado["vacio"]." y ".$lavado["uvacio"]." s)\n";
        unset($pines, $lavado, $slide, $reti);
      }
      // Datos fijos si es tipo numeración o chips múltiples
      else {
        $info = explode(",",$info);
        if( $info[5]=="Numeración" ){
          $texto = "(Rutina específica: ".$info[5].")\n";
          if( $info[0]=="1" )
            $texto .= "(Pines: 6x1 tipo cerámico)\n";
          else
            $texto .= "(Pines: 6x1 tipo acero)\n";
          if( (int)$info[1]<10 )
            $texto .= "(Sección por imprimir: superior)\n";
          else
            $texto .= "(Sección por imprimir: inferior para slides girados)\n";
          $texto .= "(Coords y espaciado: ".$info[1]."x".$info[2]."mm con 130um)\n";
          $texto .= "(Slides a imprimir: ".$info[3]."x".$info[4].")\n";
          $texto .= "(Ciclos de lavado: 3 con 4 osc)\n";
          $texto .= "(Tiempo de muestra, vacío y último vacío: 1, 2 y 3 s)\n";
        }
        else{
          $texto = "(Rutina específica: ".$info[12].")\n";
          if( $info[1]=="1" )
            $extra = "con serie duplicada";
          else
            $extra = null;
          if( $info[0]=="1" )
            $texto .= "(Pines: 4x2 tipo cerámico $extra)\n";
          else
            $texto .= "(Pines: 4x2 tipo acero $extra)\n";
          $texto .= "(Puntos por arreglo: ".$info[6]."x".$info[7]." con ".$info[8]." dup)\n";
          $texto .= "(Coords y espaciado: ".$info[2]."x".$info[3]."mm con ".$info[4]."x".$info[5]."um)\n";
          $texto .= "(Placas a realizar: ".$info[9].")\n";
          $texto .= "(Slides a imprimir: ".$info[10]."x".$info[11].")\n";
          $texto .= "(Ciclos de lavado: 3 con 4 osc)\n";
          $texto .= "(Tiempo de muestra, vacío y último vacío: 1, 2 y 3 s)\n";
        }
      }
      // Guarda comentarios de inicio
      $texto .= "\n";
      $archivo = fopen("../G/".$this->nombreG, "a");
      fwrite($archivo, $texto);
      fclose($archivo);
      unset($texto, $archivo);
    }
    // Finaliza rutinas
    public function FinCodigoG(){
      $texto = "M05 (Fin de la rutina)";
      $this->escribeArchivo($texto);
      unset($texto);
    }
  }
?>
