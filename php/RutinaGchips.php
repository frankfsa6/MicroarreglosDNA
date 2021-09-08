<?php
  // Rutina específica de chips múltiples
  set_time_limit(0);
  include("ArchG.php");
  // Chips (0-PinesDob, 1-XCoord, 2-YCoord, 3-XEsp, 4-YEsp, 5-XPunt, 6-YPunt, 7-Duplic, 8-Placas, 9-XSlide, 10-YSlide)
  $datos = explode(",", $_POST['datchips']);
  $vxy = 120;
  $vz = 100;
  //Variable del cambio de placa/vidrio y distancias en mm (dadas por la estructura de la máquina)
  $cambioPlaca = [0, 0];
  $XSlideDist = 80.9;
  $YSlideDist = 30.9;
  // Distancia entre pines de 9 mm para calcular muestras y distancia entre puntitos de números
  $pinDist = 9;
  $tmuestra = 1;
  $YMuestraDist = 2*$pinDist;
  $XMuestraDist = 4*$pinDist;
  $Muestra = [0, 0];
  // Valores para calcular limpieza (vidrio alcanza para 2 placas muestra superior/inferior)
  $limpDist = 0.5;
  $toquesLimp = 6;
  $origLimp = 3;
  // Variables de retícula y pines
  $pinesDobles = $datos[0];
  $columnasPlaca = $datos[9];
  $filasPlaca = $datos[10];
  // Variables de lavado y vacío
  $cicLav = 3;
  $oscLav = 4;
  $tvac = 2;
  $utvac = 3;
  // Variables para generar diseño del chip
  $XCoords = $datos[1];
  $YCoords = $datos[2];
  $XDist = $datos[3]/1000;
  $YDist = $datos[4]/1000;
  $XPuntos = $datos[5];
  $YPuntos = $datos[6];
  $puntosDup = $datos[7];
  $placasTot = $datos[8];
  // Obtiene datos DB y comienza a ejecutar
  $archivito = new ArchG("chips");
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
        $archivito->LugarD("Lavado",$vxy,$vz,"Lugar");
        $archivito->ActualizaPausa($cambioPlaca[0],$cambioPlaca[1]);
        $archivito->BVac(1);
        $archivito->LugarD("Vacío",$vxy,$vz,"Lugar");
        $archivito->Espera($tvac);
        $archivito->BVac(0);
      }
      // Demás veces hace tantos ciclos de lavado se necesiten
      else{
        $archivito->BVac(1);
        for($i=0; $i<$cicLav; $i++){
          $archivito->LugarD("Lavado",$vxy,$vz,"Lugar");
          $archivito->Lavado($oscLav);
          $archivito->LugarD("Vacío",$vxy,$vz,"Lugar");
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
      $archivito->LugarD("Toma de muestra",$vxy,$vz,"Lugar");
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
      $archivito->LugarD("Toque de limpieza",$vxy,$vz,"Lugar");
      $archivito->ToquesLimpieza($toquesLimp);
      $archivito->ActualizaCoords(0, $limpDist,"Toque de limpieza");
      if( $Muestra[0]==0  ){
        $archivito->ReiniciaCoords(2,"Toque de limpieza","Limpieza");
        $archivito->ActualizaCoords(1, 5*$Muestra[1],"Toque de limpieza");
      }
      // Comienza a poner puntos en tantos slides se hayan configurado
      // Al terminar los puntos, avanza en Y para reiniciar slide y aumenta cantidad con los duplicados
      if( $pinesDobles==0 )
        $archivito->InsertarPuntosSlides($columnasPlaca,$filasPlaca,$vxy,$vz,$puntosDup,$YDist,$YSlideDist,$XSlideDist);
      else
        $archivito->InsertarChipsSlides($columnasPlaca,$filasPlaca,$vxy,$vz,$puntosDup,$XMuestraDist,$YDist,$YSlideDist,$XSlideDist);
      $archivito->ActualizaCoords(1, $puntosDup*$YDist,"Retícula");
      $archivito->ReiniciaCoords(2,"Slide","Retícula");
      $b+=$puntosDup;
    }
    // Al término de cada columna en el chip, recupera coordenada Y inicial y agrega en X; aumenta contador de puntos X
    $archivito->ActualizaCoords(1, -$YPuntos*$YDist,"Retícula"); 
    $archivito->ActualizaCoords(0, $XDist,"Retícula"); 
    $archivito->ReiniciaCoords(2,"Slide","Retícula");
    $a++;
  }
  // Termina en origen y finaliza rutina
  $archivito->LugarD("Origen",$vxy,$vz,"Lugar");
  $archivito->FinCodigoG();
  unset($archivito);
  header_remove('Set-Cookie');
?>
