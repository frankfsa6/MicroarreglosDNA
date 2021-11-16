<?php
  // Rutina específica de chips múltiples
  set_time_limit(0);
  include("ArchG.php");
  // Chips ( 0-TipoPin, 1-PinesDob, 2-XCoord, 3-YCoord, 4-XEsp, 5-YEsp, 6-XPunt, 7-YPunt, 8-Duplic, 9-Placas, 10-XSlide, 11-YSlide)
  $datos = explode(",", $_POST['datchips']);
  //Variable del cambio de placa/vidrio y distancias en mm (dadas por la estructura de la máquina)
  $cambioPlaca = [0, 0];
  $XSlideDist = 80.9;
  $YSlideDist = 30.9;
  // Distancia entre pines de 9 mm para calcular muestras y distancia entre puntitos de números
  $pinDist = 9;
  $YMuestraDist = 2*$pinDist;
  $XMuestraDist = 4*$pinDist;
  $tmuestra = 1;
  $Muestra = [0, 0];
  // Valores para calcular limpieza (vidrio alcanza para 2 placas muestra superior/inferior)
  $limpDist = 0.5;
  $toquesLimp = 6;
  $origLimp = 3;
  // Variables de retícula y pines
  $pinesDobles = $datos[1];
  $columnasPlaca = $datos[10];
  $filasPlaca = $datos[11];
  // Variables de lavado y vacío
  $cicLav = 3;
  $oscLav = 4;
  $tvac = 2;
  $utvac = 3;
  // Variables para generar diseño del chip
  $XCoords = $datos[2];
  $YCoords = $datos[3];
  $XDist = $datos[4]/1000;
  $YDist = $datos[5]/1000;
  $XPuntos = $datos[6];
  $YPuntos = $datos[7];
  $puntosDup = $datos[8];
  $placasTot = $datos[9];
  // Obtiene datos DB y comienza a ejecutar
  $archivito = new ArchG("chips", implode(",", $datos).",Chips múltiples");
  $archivito->SensarOrigen();
  // Mueve retícula a slide el valor dado de coordenadas y limpieza a 3mmXY de esquina
  $archivito->ActualizaCoords(0, $XCoords,"Retícula");
  $archivito->ActualizaCoords(1, $YCoords,"Retícula");
  $archivito->ReiniciaCoords(2, "Slide", "Retícula");
  $archivito->ActualizaCoords(0, $origLimp, "Limpieza");
  $archivito->ActualizaCoords(1, $origLimp,"Limpieza");
  $archivito->ReiniciaCoords(2,"Toque de limpieza","Limpieza");
  // Rutina completa de tantos puntos se necesiten
  $a = 1;
  while( $a<=$XPuntos ){ 
    // Realiza chips por columnas 
    $b = 1;  
    while( $b<=$YPuntos && $placasTot!=0 ){
      // Primera vez inicia humedeciendo los pines, espera según usuario y luego seca en vacío
      if( $a==1 && $b==1 ){
        $archivito->LugarD("Lavado","Lugar");
        $archivito->ActualizaPausa($cambioPlaca[0],$cambioPlaca[1]);
        $cambioPlaca = [1,1];
        $archivito->BVac(1);
        $archivito->LugarD("Vacío","Lugar");
        $archivito->Espera($tvac);
        $archivito->BVac(0);
      }
      // Demás veces hace tantos ciclos de lavado se necesiten
      else{
        $archivito->BVac(1);
        for($i=0; $i<$cicLav; $i++){
          $archivito->LugarD("Lavado","Lugar");
          $archivito->Lavado($oscLav);
          $archivito->LugarD("Vacío","Lugar");
          // Último lavado cambia tiempo 
          if($i == $cicLav-1)
            $archivito->Espera($utvac);
          else
            $archivito->Espera($tvac);
        }
        $archivito->BVac(0);
      }
      // Crea pausa para cambiar la placa o el vidrio de limpieza
      // Si completó 2 muestras, debe cambiar ambos
      if( $Muestra[0] == 0 ){
        $archivito->PinSB(0,"Cambio");
        if( $Muestra[1] == 1 ){
          $cambioPlaca = [1,0];
          $Muestra[1] = 0;
        }
        else{
          $cambioPlaca = [1,1];
          $Muestra[1]++;
        }
        $archivito->ActualizaPausa($cambioPlaca[0],$cambioPlaca[1]);
      }
      // Toma muestra en el siguiente número y espera
      // En caso de ser primera o segunda columna, suma mmX para siguiente vez; de lo contrario, quita mmX y suma mmY
      $archivito->LugarD("Toma de muestra","Lugar", " ".(1+$Muestra[0])."/12");
      $archivito->Espera($tmuestra);
      $Muestra[0]++;
      if( $Muestra[0] == 12 ){
        $archivito->ReiniciaCoords(2,"Toma de muestra","Muestra");
        $Muestra[0] = 0;
        $placasTot--;
      }
      else if( $Muestra[0]%3 == 0 ){
        $archivito->ActualizaCoords(0, -2*$XMuestraDist, "Toma de muestra");
        $archivito->ActualizaCoords(1, $YMuestraDist, "Toma de muestra");
      }
      else
        $archivito->ActualizaCoords(0, $XMuestraDist, "Toma de muestra");
      // Hace la limpieza de pines y avanza 0.5 mmX para siguiente vez
      // Si ya terminó la primera placa, regresa a XMuestra y aumenta 5 mmY
      $archivito->LugarD("Toque de limpieza","Lugar");
      $archivito->ToquesLimpieza($toquesLimp);
      $archivito->ActualizaCoords(0, $limpDist,"Toque de limpieza");
      if( $Muestra[0]==0 ){
        $archivito->ReiniciaCoords(2,"Toque de limpieza","Limpieza");
        if( $Muestra[1]==1 )
          $archivito->ActualizaCoords(1, 10+2*$limpDist*$toquesLimp,"Toque de limpieza");
      }
      // Comienza a poner puntos en tantos slides se hayan configurado
      // Al terminar los puntos, avanza en Y para reiniciar slide y aumenta cantidad con los duplicados
      if( $pinesDobles==0 )
        $archivito->InsertarPuntosSlides($columnasPlaca,$filasPlaca,$puntosDup,$YDist,$YSlideDist,$XSlideDist, $b);
      else
        $archivito->InsertarChipsSlides($columnasPlaca,$filasPlaca,$puntosDup,$XMuestraDist,$YDist,$YSlideDist,$XSlideDist, $b);
      $archivito->ActualizaCoords(1, $puntosDup*$YDist,"Retícula");
      $archivito->ReiniciaCoords(2,"Slide","Retícula");
      $b+=$puntosDup;
    }
    // Al término de cada columna en el chip, recupera coordenada Y inicial y avanza en X
    // Aumenta contador de puntos X 
    $archivito->ActualizaCoords(1, -$YPuntos*$YDist,"Retícula"); 
    $archivito->ActualizaCoords(0, $XDist,"Retícula"); 
    $archivito->ReiniciaCoords(2,"Slide","Retícula");
    $a++;
  }
  // Por último, deja limpios y secos los pines
  $archivito->BVac(1);
  for($i=0; $i<$cicLav; $i++){
    $archivito->LugarD("Lavado","Lugar");
    $archivito->Lavado($oscLav);
    $archivito->LugarD("Vacío","Lugar");
    if($i == $cicLav-1)
      $archivito->Espera($utvac);
    else
      $archivito->Espera($tvac);
  }
  $archivito->BVac(0);
  // Termina rutina yendo a origen
  $archivito->LugarD("Origen","Lugar");
  $archivito->FinCodigoG();
  unset($archivito);
?>
