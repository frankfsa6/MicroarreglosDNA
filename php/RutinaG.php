<?php
// Rutina para generar código G
  set_time_limit(0);
  include("ArchG.php");
  //Variable del cambio de placa/vidrio
  $cambioPlaca = [0,0];
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
  $origLimp = 3;
  // Asigna variable pines
  $pines = getDBdata("pines");
  if( $pines != null ){
    $pinesX = $pines["PinesX"];
  }
  // Asigna las variables de lavado
  $lavado = getDBdata("lavado");
  if( $lavado != null ){
    $cicLav = $lavado["ciclos"];
    $osc = $lavado["oscilaciones"];
    $tvac = $lavado["vacio"];
    $utvac = $lavado["uvacio"];
    $toques = $lavado["toques"];
    $tmuestra = $lavado["tmuestra"];
  }
  // Asigna las variables de slide
  $slide = getDBdata("slide");
  if( $slide != null ){
    $columnasPlaca = $slide["columnasplaca"];
    $filasPlaca = $slide["filasplaca"];
  }
  //Asigna las variables de retícula
  $reticula = getDBdata("reticula");
  if ( $reticula != null){
    $XCoords = $reticula["XCoords"];
    $YCoords = $reticula["YCoords"];
    $XSpace = $reticula["XSpace"]/1000;
    $YSpace = $reticula["YSpace"]/1000;
    $XDots = $reticula["XDots"];
    $YDots = $reticula["YDots"];
    $DupDots = $reticula["DuplicateDots"];
    $TotalPlates = $reticula["TotalPlates"];
  }
  // Obtiene el nombre de la rutina
  $rutina = getDBdata("rutinas");
  if( $rutina != null ){
    $nombreRutina = $rutina["nombreRutina"];
  }
  // Datos para dividir vidrio de limpieza y muestra
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
  $archivito = new ArchG($nombreRutina);
  $archivito->SensarOrigen();
  // Mueve retícula a slide el valor dado de coordenadas y limpieza a 3mmXY de esquina
  $archivito->ActualizaCoords(0, $XCoords,"Retícula");
  $archivito->ActualizaCoords(1, $YCoords,"Retícula");
  $archivito->ReiniciaCoords(2, "Slide", "Retícula");
  $archivito->ActualizaCoords(0, $origLimp,"Limpieza");
  $archivito->ActualizaCoords(1, $origLimp,"Limpieza");
  $archivito->ReiniciaCoords(2,"Toque de limpieza","Limpieza");
  //Inicia la rutina asignada
  for($a=1; $a<=$XDots; $a++){
    for($b=1; $b<=$YDots; $b++){
      if ($TotalPlates != 0){
        // Primera vez inicia humedeciendo los pines y deja en pausa según usuario
        // Luego seca en vacío y sube para una segunda pausa
        if( $a==1 && $b==1 ){
          $archivito->LugarD("Lavado",$vxy,$vz,"Lugar");
          $archivito->ActualizaPausa($cambioPlaca[0],$cambioPlaca[1]);
          $cambioPlaca = [1,1];
          $archivito->BVac(1);
          $archivito->LugarD("Vacío",$vxy,$vz,"Lugar");
          $archivito->Espera($tvac);
          $archivito->BVac(0);
        }
        // Demás veces hace limpieza y vacío con tiempo predeterminado
        else{
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
        // Crea pausas cuando se debe cambiar la placa o el vidrio de limpieza
        if ($cambioPlaca[0] != 0 || $cambioPlaca[1] != 0){
          $archivito->PinSB(0,"Cambio");
          $archivito->ActualizaPausa($cambioPlaca[0],$cambioPlaca[1]);
          $cambioPlaca = [0,0];
        }
        // Toma de muestra y actualiza coordenadas del siguiente lugar de la toma
        // Al llegar a fin de X, regresa a inicio X además de avanzar Y
        $archivito->LugarD( "Toma de muestra",$vxy,$vz,"Lugar", " ".(1+$Muestra[0])." x ".(1+$Muestra[1]) );
        $archivito->Espera($tmuestra);
        $Muestra[0]++;
        if ($Muestra[0]==$XMuestra){
          $Muestra[0]=0;
          $Muestra[1]++;
          $archivito->ReiniciaCoords(0,"Toma de muestra","Muestra");
          if ($Muestra[1]==$YMuestra){
            $Muestra[1] = 0;
            $archivito->ReiniciaCoords(1,"Toma de muestra","Muestra");
            $cambioPlaca[0]++;
            if( $TotalPlates>=1 )
              $TotalPlates--;
          }
          else
            $archivito->ActualizaCoords(1,$YMuestraDistance,"Toma de muestra");
        }
        else
            $archivito->ActualizaCoords(0,$XMuestraDistance,"Toma de muestra");
        // Hace la limpieza de pines y actualiza coordenadas de los siguientes toques
        // Cada vidrio alcanza para dos placas muestra, donde en la segunda avanza en Y
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
        // Inserción en slides de la retícula completa
        // Al terminar avanza YPuntosDup o si acabó columna, regresa a Ycoord y avanza X espaciado
        $archivito->InsertarPuntosSlides($columnasPlaca,$filasPlaca,$vxy,$vz,$DupDots,$YSpace,$YSlideDistance,$XSlideDistance);
        $archivito->ActualizaCoords(1, $YSpace*$DupDots,"Retícula");
        if( $b==$YDots ){
          $archivito->ActualizaCoords(1, -$YSpace*$DupDots*$YDots,"Retícula");
          $archivito->ActualizaCoords(0, $XSpace,"Retícula");
        }
        $archivito->ReiniciaCoords(2,"Slide","Retícula");
      }
    }
  }
  // Por último, deja limpios y secos los pines
  $archivito->BVac(1);
  for($i=0; $i<$cicLav; $i++){
    $archivito->LugarD("Lavado",$vxy,$vz,"Lugar");
    $archivito->Lavado($osc);
    $archivito->LugarD("Vacío",$vxy,$vz,"Lugar");
    if($i == $cicLav-1)
      $archivito->Espera($utvac);
    else
      $archivito->Espera($tvac);
  }
  $archivito->BVac(0);
  // Termina rutina yendo a origen
  $archivito->LugarD("Origen",$vxy,$vz,"Lugar");
  $archivito->FinCodigoG();
  unset($archivito);
?>
