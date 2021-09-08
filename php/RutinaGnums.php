<?php
// Rutina específica de numeración
  set_time_limit(0);
  include("ArchG.php");
  ini_set('display_errors', 1);
  // Variables recibidas
  $datos = explode(",", $_POST['datnum']);
  $XCoords = round((float)$datos[0], 3);
  $YCoords = round((float)$datos[1], 3);
  $columnasPlaca = (int)$datos[2];
  $filasPlaca = (int)$datos[3];
  // Velocidad para diagonales y movimientos principales
  $vxy = 120;
  $vz = 100;
  // Variable del cambio de placa/vidrio y distancias en mm (dadas por la estructura de la máquina)
  $cambioPlaca = [0,0];
  $XSlideDist = 80.9;
  $YSlideDist = 30.9;
  // Distancia entre pines de 9 mm para calcular muestras y distancia entre puntitos de números
  $pinDist = 9;
  $tmuestra = 1;
  $XMuestraDist = 6*$pinDist;
  $numDist = 0.13;
  // Distancia de puntos en limpieza y origen a 3 mmXY
  $limpDist = 0.5;
  $toquesLimp = 6;
  $origLimp = 3;
  // Asigna los valores de lavado, vacío y limpieza de pines (empieza a 3mm XY de la esquina)
  $cicLav = 3;
  $oscLav = 4;
  $tvac = 2;
  $utvac = 3;
  // Obtiene datos DB y comienza a ejecutar
  $archivito = new ArchG("nums", "Numeración,$XCoords,$columnasPlaca,$filasPlaca");
  $archivito->SensarOrigen();
  // Mueve retícula a slide el valor dado de coordenadas y limpieza a 3mmXY de esquina
  $archivito->ActualizaCoords(0, $XCoords,"Retícula");
  $archivito->ActualizaCoords(1, $YCoords,"Retícula");
  $archivito->ReiniciaCoords(2, "Slide", "Retícula");
  $archivito->ActualizaCoords(0, $origLimp,"Limpieza");
  $archivito->ActualizaCoords(1, $origLimp,"Limpieza");
  $archivito->ReiniciaCoords(2,"Toque de limpieza","Limpieza");
  // Rutina completa de 4 series numéricas
  for($i=0; $i<4; $i++){
    // Serie de ocho dígitos, yendo de 9 a 2
    for($j=0; $j<8; $j++){
      // Primera vez inicia humedeciendo los pines, espera según usuario y luego seca en vacío
      if( $i==0 && $j==0 ){
        $archivito->LugarD("Lavado",$vxy,$vz,"Lugar");
        $archivito->ActualizaPausa($cambioPlaca[0],$cambioPlaca[1]);
        $cambioPlaca = [1,0];
        $archivito->BVac(1);
        $archivito->LugarD("Vacío",$vxy,$vz,"Lugar");
        $archivito->Espera($tvac);
        $archivito->BVac(0);
      }
      // Demás veces con tiempo predeterminado
      else{
        // Enciende bomba y hace tantos ciclos de lavado se necesiten
        $archivito->BVac(1);
        for($k=0; $k<$cicLav; $k++){
          $archivito->LugarD("Lavado",$vxy,$vz,"Lugar");
          $archivito->Lavado($oscLav);
          $archivito->LugarD("Vacío",$vxy,$vz,"Lugar");
          // Último lavado cambia tiempo
          if($k == $cicLav-1)
            $archivito->Espera($utvac);
          else
            $archivito->Espera($tvac);
        }
        $archivito->BVac(0);
      }
      // Pide pausa para cambiar placa de muestras al iniciar cada serie 
      if( $j==0 ){
        $archivito->PinSB(0,"Cambio");
        $archivito->ActualizaPausa($cambioPlaca[0],$cambioPlaca[1]);
      }
      // Toma muestra en el siguiente número y espera
      // En caso de ser primera columna, suma mmX para siguiente vez; de lo contrario, quita mmX y suma mmY
      $archivito->LugarD("Toma de muestra",$vxy,$vz,"Lugar");
      $archivito->Espera($tmuestra); 
      if( $j%2==0 )
        $archivito->ActualizaCoords(0,$XMuestraDist,"Toma de muestra");
      else{
        $archivito->ActualizaCoords(0,-$XMuestraDist,"Toma de muestra");
        $archivito->ActualizaCoords(1,$pinDist/2,"Toma de muestra");
      }
      // Hace la limpieza de pines y avanza 0.5 mmX para siguiente vez
      // Si ya terminó la serie, regresa a XMuestra y aumenta 5 mmY
      $archivito->LugarD("Toque de limpieza",$vxy,$vz,"Lugar");
      $archivito->ToquesLimpieza($toquesLimp);
      $archivito->ActualizaCoords(0, $limpDist,"Toque de limpieza");
      if( $j==7 ){
        $archivito->ActualizaCoords(1, 5,"Limpieza");
        $archivito->ReiniciaCoords(2,"Toque de limpieza","Limpieza");
      }
      // Comienza a poner dígitos en tantos slides se hayan configurado
      // En cada número, avanza 3*distPuntitosNum en mmY; al terminar la serie, regresa 8 veces y se mueve 6*distPuntitosNum en mmX
      $archivito->InsertarNumSlides($columnasPlaca, $filasPlaca, $vxy, $vz, $j, $numDist, $YSlideDist, $XSlideDist);
      $archivito->ActualizaCoords(1, 3*$numDist,"Retícula");
      if( $j==7 ){
        $archivito->ActualizaCoords(1, -8*3*$numDist,"Retícula");
        $archivito->ActualizaCoords(0, 6*$numDist,"Retícula");
      }
      $archivito->ReiniciaCoords(2,"Slide","Retícula");
    }
  }
  // Termina en origen y finaliza rutina
  $archivito->LugarD("Origen",$vxy,$vz,"Lugar");
  $archivito->FinCodigoG();
  unset($archivito);
  header_remove('Set-Cookie');
?>
