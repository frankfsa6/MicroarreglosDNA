<?php
// Rutina principal configurable
  set_time_limit(0);
  include("ArchC.php");
  //Variables de velocidad
  $vxy = 120;
  $vz = 100;
  $pasosPerdidos = 0;
  //Variable del cambio de placa/vidrio
  $cambioPlaca = [0, 1];
  //Distancias en mm (dadas por la estructura de la máquina)
  $XSlideDistance = 80.9;
  $YSlideDistance = 30.9;
  $PinDist = 4.5;
  $YMuestraDistance = $PinDist*4;
  $YMuestra = 4;
  $Muestra = [0,0];
  $YLimpiezaDistance = $YMuestraDistance+2;
  $YLimpieza = 2;
  $Limpieza = [0,0,0];
  //Obtiene los datos de la rutina de la base de datos y asigna variable pines
  $pines = getDBdata("pines");
  if( $pines != null ){
    $pinesX = $pines["PinesX"];
  }
  $lavado = getDBdata("lavado");
  if( $lavado != null ){
    //Asigna las variables de lavado
    $cicLav = $lavado["ciclos"];
    $osc = $lavado["oscilaciones"];
    $tvac = $lavado["vacio"];
    $utvac = $lavado["uvacio"];
    $toques = $lavado["toques"];
    $tmuestra = $lavado["tmuestra"];
  }
  $slide = getDBdata("slide");
  if( $slide != null ){
    //Asigna las variables de slide
    $columnasPlaca = $slide["columnasplaca"];
    $filasPlaca = $slide["filasplaca"];
  }
  $reticula = getDBdata("reticula");
  if ( $reticula != null){
    //Asigna las variables de reticula
    $XCoords = $reticula["XCoords"];
    $YCoords = $reticula["YCoords"];
    $XSpace = $reticula["XSpace"]/1000;
    $YSpace = $reticula["YSpace"]/1000;
    $XDots = $reticula["XDots"];
    $YDots = $reticula["YDots"];
    $DupDots = $reticula["DuplicateDots"];
    $TotalPlates = $reticula["TotalPlates"];
  }
  $YCoordsInicial = $YCoords;
  $YDots = $YDots/$DupDots;
  $XMuestraDistance = $PinDist*$pinesX;
  $XLimpiezaDistance = $XMuestraDistance+2;
  $XMuestra = 24/$pinesX;
  if ($pinesX == 4 || $pinesX == 6)
    $XLimpieza = 2;
  elseif ($pinesX == 8 || $pinesX == 12)
    $XLimpieza = 1;
  if ($pinesX == 4 || $pinesX == 8)
    $LimpiezaDots = 6;
  elseif ($pinesX == 6 || $pinesX == 12)
    $LimpiezaDots = 8;
  // Obtiene datos DB y comienza a ejecutar
  $archivito = new ArchC();
  $archivito->SensarOrigen();
  //Inicia la rutina asignada
  for($a=1; $a<=$XDots; $a++){    
    for($b=1; $b<=$YDots; $b++){
      if ($TotalPlates != 0){
        // Primera vez inicia humedeciendo los pines, espera según usuario y luego seca en vacío
        if( $a==1 && $b==1 ){
          $archivito->LugarD("Lavado",$vxy,$vz,"Lugar");
          $archivito->ActualizaPausa($cambioPlaca[0],$cambioPlaca[1]);
          $archivito->BVac(1);
          $archivito->LugarD("Vacío",$vxy,$vz,"Lugar");
          $archivito->Espera($tvac);
          $archivito->BVac(0);
          $archivito->PinSB(0,"Cambio");
          $archivito->ActualizaPausa($cambioPlaca[0],$cambioPlaca[1]);
          $cambioPlaca = [0,0];
        }
        // Demás veces hace limpieza y vacío con tiempo predeterminado
        else{
          // Enciende bomba y hace tantos ciclos de lavado se necesiten
          $archivito->BVac(1);
          for($i=0; $i<$cicLav; $i++){
            $archivito->LugarD("Lavado",$vxy,$vz,"Lugar");
            $archivito->Lavado($osc);
            $archivito->LugarD("Vacío",$vxy,$vz,"Lugar");
            // Último lavado cambia tiempo 
            if($i == $cicLav-1)
              $archivito->Espera($utvac);
            else
              $archivito->Espera($tvac);
          }
          $archivito->BVac(0);
        }
        //Toma de muestra y actualiza coordenadas del siguiente lugar de la toma
        $archivito->LugarD("Toma de muestra",$vxy,$vz,"Lugar");
        $archivito->Espera($tmuestra);
        $Muestra[0]++;
        if ($Muestra[0]==$XMuestra){
          $Muestra[0]=0;
          $Muestra[1]++;
          $archivito->ReiniciaCoords(0,"Toma de muestra","Muestra");
          if ($Muestra[1]==$YMuestra){
            $Muestra[1]=0;
            $archivito->ReiniciaCoords(1,"Toma de muestra","Muestra");
            $cambioPlaca[0]++;
          }
          else
            $archivito->ActualizaCoords(1,$YMuestraDistance,"Toma de muestra");
        }
        else
            $archivito->ActualizaCoords(0,$XMuestraDistance,"Toma de muestra");
        // Hace la limpieza de pines y actualiza coordenadas de los siguientes toques
        $archivito->LugarD("Toque de limpieza",$vxy,$vz,"Lugar");
        $archivito->ToquesLimpieza($toques);
        $Limpieza[0]++;
        if ($Limpieza[0]==$LimpiezaDots){
          $Limpieza[0]=0;
          $archivito->ReiniciaCoords(0,"Toque de limpieza","Limpieza");
          $Limpieza[1]++;
          if ($Limpieza[1]==$YLimpieza){
            $Limpieza[1]=0;
            $archivito->ReiniciaCoords(1,"Toque de limpieza","Limpieza");
            $Limpieza[2]++;
            if ($Limpieza[2]==$XLimpieza){
              $Limpieza[2]=0;
              $cambioPlaca[1]++;
            }
          }
          else
            $archivito->ActualizaCoords(1,$YLimpiezaDistance,"Toque de limpieza");
          if ($Limpieza[2]==1)
              $archivito->ActualizaCoords(0,$XLimpiezaDistance,"Toque de limpieza");
        }
        else
          $archivito->ActualizaCoords(0,0.5,"Toque de limpieza");
        // Inserción de puntos en Slides
        $archivito->ActualizaCoords(0,$XCoords,"Slide");
        $archivito->ActualizaCoords(1,$YCoords,"Slide");
        $archivito->InsertarPuntosSlides($columnasPlaca,$filasPlaca,$vxy,$vz,$DupDots,$YSpace,$YSlideDistance,$XSlideDistance);
        //Termino de puntos en y
        $archivito->ReiniciaCoords(2,"Slide","Retícula");
        $YCoords+=($YSpace*$DupDots);
        if ($b==$YDots)
          $YCoords=$YCoordsInicial;
        // Crea pausas cuando se debe cambiar la placa o el vidrio de limpieza
        if ($cambioPlaca[0] != 0 || $cambioPlaca[1] != 0){
          $archivito->PinSB(0,"Cambio");
          if ($TotalPlates > 1)
            $archivito->ActualizaPausa($cambioPlaca[0],$cambioPlaca[1]);
          $cambioPlaca[0]=0;
          $cambioPlaca[1]=0;
          $TotalPlates --;	
        }
        /*if ($cambioPlaca[0] != 0 || $cambioPlaca[1] != 0 || ($b==1 && $a==1) ){
          $archivito->LugarD("Origen",$vxy,$vz,"Lugar");
          $archivito->SensarOrigen();
        }
        else {
          $archivito->SumaPasosPerdidos($pasosPerdidos);
        }*/
      }
    }
    //Término de puntos en x
     $XCoords+=$XSpace;   
  }
  // Termina en origen y finaliza rutina
  $archivito->LugarD("Origen",$vxy,$vz,"Lugar");
  unset($archivito);
  header_remove('Set-Cookie');
?>
