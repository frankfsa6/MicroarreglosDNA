<?php
  include("bd.php");
  set_time_limit(0);
  // Clase para crear archivos
  class ArchC{
    private $pasosMM;		// Pasos/mm XYZ que se avanzarán en cada caso
    private $actual;	// Coordenadas mm de posición actual XYZ
    private $lugares;	// Coordenadas mm de los lugares obtenidos
    private $zslide = 1.5;	  // Número de milimetros -1.5mm que baja para poner puntos en el Slide
    private $zespera = 4;     // Aproximación en Z para lugares principales
    private $bombAV = [0,0];  // Pines para activar bomba de agua y vacío
    private $pasosDecimales = [0,0,0]; // Decimales de cuentas XYZ para corregir pasos
    // Constructor que inicializa rutina
    public function __construct(){
      // Coordenadas y datos principales
      $this->DatosDB();
      // Crea campos en sesión si no existen
      session_name("IFCLab");
      session_start();
      $_SESSION['Fin'] = 0;
      $_SESSION['Pausa'] = 0;
      $_SESSION['Cambio'] = [0,0];
      session_write_close();
    }
    // Lleva motores al origen
    public function SensarOrigen( $joys = null ){
	    // Inicia valores
      $this->actual = [0,0,0];
      $this->ActualizaMov("Sensando origen");
      // Ejecuta origen en la rutina
      if( $joys == null ){
        $this->Pausas();
        $str = "sudo ../C/o 0 0 0 2>&1";
        exec($str, $out);
        $str = $out;
      }
      // Joystick requiere mediciones de regreso
      else{
        // En caso de medir por segunda vez los pasos o calibrar lugar, compara contraseña
        if( $joys == 1 || (is_array($joys) && $joys[1] === "Fisio") ){
          // Ejecuta pasitos
          $this->Pausas();
          $str = "sudo ../C/o 0 0 0 2>&1";
          exec($str, $out);
          $str = "";
          // Si no recibe 3 lecturas (Z,X,Y), hubo error
          if( count($out) == 3 ){
            $ejes = ["x","y","z"];
            for($i=0; $i<3; $i++){
              // Asigna eje correcto XYZ y obtiene mm recorridos según los pasos 
              $pasitos = ($i == 0)? abs((int)$out[1]) : ( ($i == 1)? abs((int)$out[2]) : abs((int)$out[0]) );
              $mms = $pasitos/$this->pasosMM[$i];
              // Manda datos a base y devuelve pasos en milímetros
              $conexion = ConectarBD();
              if($conexion != false){
                mysqli_set_charset($conexion,"utf8");
                $sql = "SELECT IDPin FROM rutina WHERE ID='".$_SESSION['ID']."'";
                if ( mysqli_query($conexion, $sql)->num_rows !=0 ) {
                  $res = mysqli_query($conexion, $sql);
                  while ( $dato = mysqli_fetch_assoc($res) )
                    $tipoPin = $dato['IDPin'];
                  mysqli_free_result($res);
                }
                $sql = "UPDATE config SET ".$ejes[$i]."=".$mms." WHERE nombre='".$joys[2]."' AND IDPin=".$tipoPin;
                mysqli_query($conexion, $sql);
              }
              $str .= ",".$pasitos." pasos = ".$mms." mm ( ".round($this->pasosMM[$i], 3)." pasos / mm )";           
            }
          }
          // No recibe los pasos porque hubo error
          else{
            for($i=0; $i<3; $i++)
              $str.=",no fue detectado correctamente el sensor";
          }
        }
        // Manda error de contraseña y no mueve motores
        else{
          $str = "";
          for($i=0; $i<3; $i++)
            $str.=",datos incorrectos para realizar calibración";
        }
      }
      return $str;
    }
    // JOYSTICK: devuelve coordenadas XYZ desde base de datos del lugar buscado o pasos/mm
    public function JoysLugDB($lug){
      // Velocidad y cadena de regreso
      $vel = 150;
      $str = "";
      // Pide datos de pasos de motor por eje
      if( $lug == "pasosmm" ){
          for($i=0; $i<3; $i++)
            $str .= $this->pasosMM[$i].",";
      } 
      // Se va hacia el lugar dado
      else{
        // Separa los datos "lugar,mmX,mmY,mmZ" y busca coordenadas del lugar
        $act = explode(",", $lug);
        $lug = $this->lugares[$act[0]];
        // Pone coordenadas actuales para dar movimiento
        for($i=0; $i<3; $i++){
          $this->actual[$i] = $act[$i+1];
        }
        $this->LugarD($act[0], $vel, $vel, "Lugar", 1);
        $str = $act[0].",".implode( ",", $this->lugares[$act[0]] );
      }
      return $str;
    }
	  // JOYSTICK: mueve motores dados por joystick y devuelve final en mm
    public function JoysMot($eje ,$dir, $pos, $v){
      // Valor de pasos/mm y velocidad
      $pasosmm = ($eje=="x") ? $this->pasosMM[0] : ( ($eje=="y") ? $this->pasosMM[1] : $this->pasosMM[2] );
      $vel = ( $v==1 ) ? 100 : ( ($v==2) ? 220 : 1500 );
      // Ejecuta función y devuelve mm finales
	    exec("sudo ../C/".$eje."j $dir $vel 2>&1", $out);
      $pos += ($dir == "0") ? (-1)*((int)$out[0])/$pasosmm : ((int)$out[0])/$pasosmm;
      unset($out);
      return $pos;
    }
    // JOYSTICK: mueve motores en diagonal dados por joystick y devuelve final en mm
    public function JoysMotD($dirX, $dirY, $posX, $posY, $v){
      // Velocidad para ejecutar código C
      $vel = ( $v==1 ) ? 80 : ( ($v==2) ? 200 : 1000 );
      exec("sudo ../C/xyj $dirX $dirY $vel 2>&1", $out);
      // Devuelve mm finales con los mismos pasos de regreso
      $posX += ($dirX == "0")? (-1)*((int)$out[0])/$this->pasosMM[0] : ((int)$out[0])/$this->pasosMM[0];
      $posY += ($dirY == "0")? (-1)*((int)$out[0])/$this->pasosMM[1] : ((int)$out[0])/$this->pasosMM[1];
      unset($out);
      return ($posX.",".$posY);
    }
    // JOYSTICK: prueba las conexiones eléctricas en el sistema
    public function JoysPruebaCon($prueba){
      switch($prueba){
        // Espera 2 minutos para apagar si son salidas (bombas)
        case "Bomba de agua":
          $this->BAgua(1);
          sleep(120);
          $this->BAgua(0);
          $str = "Fin de la activación para la bomba de agua. En caso de no haber encendido, verifique su conexión y el pin asociado en la configuración del sistema";
          break;
        case "Bomba de vacío":
          $this->BVac(1);
          sleep(120);
          $this->BVac(0);
          $str = "Fin de la activación para la bomba de vacío. En caso de no haber encendido, verifique su conexión y el pin asociado en la configuración del sistema";
          break;
        // Espera interrupción al ser entradas (sensores de límite y emergencia)
        case "Sensor X":
          exec("sudo ../C/prueba 1 2>&1", $out);
          $str = $out[0];
          break;
        case "Sensor Y":
          exec("sudo ../C/prueba 2 2>&1", $out);
          $str = $out[0];
          break;
        case "Sensor Z":
          exec("sudo ../C/prueba 3 2>&1", $out);
          $str = $out[0];
          break; 
        case "Botón de emergencia":
          exec("sudo ../C/prueba 4 2>&1", $out);
          $str = $out[0];
          break;         
      }
      return $str;
    }
    // Usa diagonales para lugares principales
    public function LugarD($lugar,$vxy,$vz,$typeZ, $joys = null){
      $this->ActualizaMov($lugar);
      // Si es un lugar definido, primero sube eje Z para evitar chocar
      if($this->actual[2] != 0 && $typeZ == "Lugar"){
        $pasos = round($this->actual[2]*$this->pasosMM[2]);
        // Ajusta pasos decimales Z
        $this->pasosDecimales[2] += $this->actual[2]*$this->pasosMM[2]-$pasos;
        if( $this->pasosDecimales[2] >= 1 ){
          $pasos+=2;
          $this->pasosDecimales[2]--;
        }
        // Checa si existen pausas o ejecuta
        $this->Pausas();
        $str = "sudo ../C/z 0 $pasos $vz 2>&1";
        exec($str);
        unset($str, $pasos);
        $this->actual[2] = 0;
      }
      // En caso de ser un slide, sube mínimo para moverse entre vidrios
      elseif($this->actual[2] != $this->lugares["Slide"][2] && $typeZ == "Slide"){
        $prox = $this->lugares["Slide"][2];
        $real = $prox-$this->actual[2];
        // Dirección de movimiento y modifica posición actual
        $dir = ($real > 0)? 1 : 0;
        $this->actual[2] += $real;
        $pasos = round(abs($real)*$this->pasosMM[2]);
        // Ajusta pasos decimales
        $this->pasosDecimales[2] += abs($real)*$this->pasosMM[2]-$pasos;
        if ( $this->pasosDecimales[2] >= 1 ){
          $pasos+=2;
          $this->pasosDecimales[2]--;
        }
        // Ejecuta pasos Z
        $this->Pausas();
        $str = "sudo ../C/z $dir $pasos $vz 2>&1";
        exec($str);
        unset($str, $prox, $dir, $pasos, $real);
      }
      // Compara lugares próximos XY
      for($i=0; $i<2; $i++){
        $prox[$i] = $this->lugares[$lugar][$i];
        $real[$i] = $prox[$i]-$this->actual[$i];
        // Dirección de movimiento
        $dir[$i] = ($real[$i] > 0)? 1 : 0;
        // Modifica posición actual: (2000 pasos/rev)/(12 mm/rev) = 166.6667 pasos/mm
        $this->actual[$i] += $real[$i];
        $pasos[$i] = round(abs($real[$i])*$this->pasosMM[$i]);
        // Ajusta pasos decimales
        $this->pasosDecimales[$i] += abs($real[$i])*$this->pasosMM[$i]-$pasos[$i];
        if ( $this->pasosDecimales[$i] >= 1 ){
          $pasos[$i]+=2;
          $this->pasosDecimales[$i]--;
        }
      }
      // Mueve al lugar definido XY y checa si existen pausas o ejecuta
      $this->Pausas();
      $str = "sudo ../C/xy $dir[0] $dir[1] $pasos[0] $pasos[1] $vxy 2>&1";
      exec($str);
      unset($str, $prox, $dir, $pasos, $real);
      // Al ser lugares definidos, finaliza eje Z para llegar al lugar
      if ($typeZ == "Lugar"){
        $prox = $this->lugares[$lugar][2];
        // Cuando es joystick, quita acercamiento 4mm
        if($joys == 1 && $lugar == "Vacío")
          $prox += 4; 
        $real = $prox-$this->actual[2];
        // Dirección de movimiento
        $dir = ($real > 0)? 1 : 0;
        // Modifica posición actual
        $this->actual[2] += $real;
        $pasos = round(abs($real)*$this->pasosMM[2]);
        // Ajusta pasos decimales
        $this->pasosDecimales[2] += abs($real)*$this->pasosMM[2]-$pasos;
        if ( $this->pasosDecimales[2] >= 1 ){
          $pasos+=2;
          $this->pasosDecimales[2]--;
        }
        // Ejecuta pasos Z
        $this->Pausas();
        $str = "sudo ../C/z $dir $pasos $vz 2>&1";
        exec($str);
        unset($str, $prox, $dir, $pasos, $real);
      }
      $this->ActualizaMov($lugar);
    }
    // Realiza oscilaciones en lavado
    public function Lavado($osc){
      // Actualiza acción
      $vv = 100;
      $this->ActualizaMov("Oscilaciones en lavado");
      // Oscila alrededor de 4 mm
      $pasosX = round(4*$this->pasosMM[0]);
      for($i=0; $i<$osc*2; $i++){
        // Realiza movimiento
        $dir = ($i%2 == 0)? 1 : 0;
        $this->Pausas();
        $str = "sudo ../C/xy $dir 0 $pasosX 0 $vv 2>&1";
        exec($str);
      }
      // Vacía variables y apaga bomba de agua
      unset($str, $pasosX, $vv);
    }
    // Realiza vacío después de lavado o la toma de muestra con un tiempo de espera
    public function Espera($tiempo){
      // Baja para limpieza 4 mm
      $vv = 100;
      $pasos = round(4*$this->pasosMM[2]);
      // Realiza movimiento de bajada
      $this->Pausas();
      $str = "sudo ../C/z 1 $pasos $vv 2>&1";
      exec($str);
      unset($str, $pasos, $vv);
      $this->actual[2] += 4;
      $this->ActualizaMov("Tiempo de espera");
      // Espera en vacío 
      sleep($tiempo);    
    }
    //Enciende o apaga la bomba de vacío
    public function BVac($estado){
      $str = "sudo bash ../C/pinesExt.sh ".$this->bombAV[1]." $estado 2>&1";
      exec($str);  
      unset($str);
    }
    //Enciende o apaga la bomba de agua
    public function BAgua($estado){
      $str = "sudo bash ../C/pinesExt.sh ".$this->bombAV[0]." $estado 2>&1";
      exec($str); 
      unset($str); 
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
                $pasosX = round($multip*$numDist*$this->pasosMM[0]);
                $this->Pausas();
                $str = "sudo ../C/xy $dirX 0 $pasosX 0 $vpunto 2>&1";
                exec($str);
                unset($str, $pasosX);
                $this->actual[0] += ( $dirX==0 )?-$multip*$numDist:$multip*$numDist;
              }
              // Mueve en Y hacia adelante
              $pasosY = round($numDist*$this->pasosMM[1]);
              $this->Pausas();
              $str = "sudo ../C/xy 0 1 0 $pasosY $vpunto 2>&1";
              exec($str);
              unset($str, $pasosY);
              // Cambia dirección en X y actualiza multiplicador con coordenada actual Y
              $dirX = ( $k%2==0 )?0:1;
              $multip = 1;
              $this->actual[1] += $numDist;
            }
            // Si la gotita no está en arreglo del número dado, se pone
            if( !in_array($k, $mat5x3[$numImp]) ) {
              // Avanza mmX acumulados si no acaba de avanzar fila y actualiza coordenada X
              if( $k!=6 && $k !=11 ){
                $pasosX = round($multip*$numDist*$this->pasosMM[0]);
                $this->Pausas();
                $str = "sudo ../C/xy $dirX 0 $pasosX 0 $vpunto 2>&1";
                exec($str);
                unset($str, $pasosX);
                $this->actual[0] += ( $dirX==0 )?-$multip*$numDist:$multip*$numDist;
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
    // Hace toda la rutina de un ciclo de insertar los chips dobles en todos los slides (vidrios) 
    public function InsertarChipsSlides($columnasPlaca,$filasPlaca,$vxy,$vz,$puntosDup,$XMuestraDist,$YDist,$YSlideDist,$XSlideDist){
      for($i=1; $i<=$columnasPlaca; $i++){
        for($j=1; $j<=$filasPlaca; $j++){
          // Primera vez llega a retícula o se mueve con altura fija entre slides
          if($i==1 && $j==1)
            $this->LugarD("Slide",$vxy,$vz,"Lugar");
          $this->LugarD("Slide",$vxy,$vz,"Slide");
          // Pone primeros puntos
          for($k=0; $k<$puntosDup; $k++)
            $this->InsertarPunto($k,$YDist);
          // Al ser fila par avanza a la izquierda, de lo contrario, a la derecha
          if( $j%2==0 )
            $this->ActualizaCoords(0,-$XMuestraDist,"Slide");
          else
            $this->ActualizaCoords(0,$XMuestraDist,"Slide");
          $this->LugarD("Slide",$vxy,$vz,"Slide");
          // Pone segundos puntos
          for($k=0; $k<$puntosDup; $k++)
            $this->InsertarPunto($k,$YDist);
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
    // Realiza la inserción del punto en el vidrio del Slide
    //Baja el pin de 3mm a 0mm (-3mm Z)
    //Si hay desplazamiento en Y llama a la función: sube:2.5mm-sube/avanza:2.5mm-avanza:y-5mm-baja/avanza:2.5mm-baja:2.5mm (ymm Y)
    //Sube el pin de 0mm a 5mm (+2mm Z)
    public function InsertarPunto($NumToque,$Ydist){
      if ($NumToque == 0)
        $this->PinSB(1,1.5);
      else
        $this->Toque($Ydist);
      $this->ActualizaMov("Inserción de punto");
    }
    // Sube, hace cúpula y baja instantáneamente
    public function Toque($Ydist){
      $vv = 250;
      // Sube 1 mm en Z iniciales
      $pasosIn = round(1*$this->pasosMM[2]);
      // Sube otros 0.5mm en YZ para saltar gotita
      $pasosZ = round(0.5*$this->pasosMM[2]);
      // Avanza Y mm 
      $pasosY = round($Ydist*$this->pasosMM[1]);
      $this->Pausas();
      $str = "sudo ../C/yz $pasosIn $pasosY $pasosZ $vv 2>&1";
      exec($str);
      $this->actual[1] += $Ydist;
      unset($str, $pasosY, $pasosZ, $pasosIn, $vv);
    }
    // Sube o baja el pin
    public function PinSB($dir,$mm){
      // Revisa pausas y ejecuta
      $vv = 100;
      // Si se manda cambio, sube eje Z al origen
      if ($mm == "Cambio")
        $mm = $this->actual[2];
      $pasos = round($mm*$this->pasosMM[2]);
      // Realiza movimiento de bajada o subida
      $this->Pausas();
      $str = "sudo ../C/z $dir $pasos $vv 2>&1";
      exec($str);
      unset($str, $pasos, $vv);
      // Actualiza posición de mmZ
      if ($dir==0)
        $this->actual[2] -= $mm;
      else
        $this->actual[2] += $mm;
      $this->ActualizaMov("Inserción de punto");
    }
    // Checa si existe fin o alguna pausa, y espera hasta que desaparece
    private function Pausas(){
      do{
        session_name("IFCLab");
        session_start();
        $ps = $_SESSION['Pausa'];
        $fin = $_SESSION['Fin'];
        session_write_close();
        // Si existe fin, apaga bombas y termina código
        if( $fin == 1 ){
          $this->BVac(0);
          $this->BAgua(0);
          exit();
        }
        // Si hay pausa, espera para reactivar
        if( $ps == 1 )
          usleep(500000);
      } while( $ps == 1 );
    }
    // Consigue pasos por eje y coordenadas de "lugares"
    private function DatosDB(){
      $conexion = ConectarBD();
      // Comprueba conexión para los datos
      if( $conexion == false ){
        $this->pasosMM = [0,0,0];
        $this->lugares = ["Origen"=>[0,0,0]];
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
        $sql = "SELECT IDPin FROM rutina WHERE ID='".$_SESSION['ID']."'";
        if ( mysqli_query($conexion, $sql)->num_rows !=0 ) {
          $res = mysqli_query($conexion, $sql);
          while ( $dato = mysqli_fetch_assoc($res) )
            $tipoPin = $dato['IDPin'];
          mysqli_free_result($res);
        }
        //Obtiene el valor seleccionado del tipo de pines
        $sql = "SELECT * FROM config WHERE IDPin=".$tipoPin;
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
        // Pide datos de bomba de agua y vacío (alfabéticamente)
        $sql = "SELECT id,valor FROM raspberry WHERE id LIKE 'bom%' ORDER BY id";
        $res = mysqli_query($conexion, $sql);
        if( $res->num_rows !=0 ){
          $i = 0;
          while( $dato = mysqli_fetch_assoc($res) ){
            $this->bombAV[$i] = $dato['valor'];   
            $i++;
          }       
          mysqli_free_result($res);
        }
        // Manda a sesión los datos de la posición de muestra XY para animación
        session_name("IFCLab");
        session_start();
        $_SESSION['Muestra'] = [ $this->lugares["Muestra"][0] , $this->lugares["Muestra"][1] ];
        session_write_close();
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
      // Mete valor de pausa en sesión
      session_name("IFCLab");
      session_start();
      $_SESSION['Cambio'] = [ $cambioPlaca , $cambioVidrio ];
      $_SESSION['Pausa'] = 1;
      session_write_close();
      // Deja en ciclo de pausa hasta que desactiva usuario
      $this->ActualizaMov("Sistema en pausa");
      $this->Pausas();
    }
    public function SumaPasosPerdidos($pasos){
      $this->actual[0] += $pasos[1];
      $this->actual[1] += $pasos[2];
      $this->actual[2] += $pasos[0];
      $this->ActualizaMov("");
    }
    // Actualiza datos actual de sesiones a movimiento principal
    private function ActualizaMov($str){
      session_name("IFCLab");
      session_start();
      $_SESSION['Actual'] = [ round($this->actual[0], 3).", ".round($this->actual[1], 3).", ".round($this->actual[2], 3) , $str];
      session_write_close();
    }
  }
?>
