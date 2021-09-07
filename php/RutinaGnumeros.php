<?php
// Rutina para generar código G
  set_time_limit(0);
  include("ArchG.php");
  ini_set('display_errors', 1);
  //Variables de velocidad
  $vxy = 70;
  $vz = 70;
  //Variable del cambio de placa/vidrio
  $cambioPlaca = array (0,0);
  //Distancias en mm (dadas por la estructura de la máquina)
  $XSlideDistance = 80.9;
  $YSlideDistance = 30.9;
  $PinDist = 4.5;
  $YMuestraDistance = $PinDist*4;
  $YMuestra = 4;
  $Muestra = array(0,0);
  $YLimpiezaDistance = $YMuestraDistance+2;
  $YLimpieza = 2;
  $Limpieza = array(0,0,0);
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
    $XSpace = $reticula["XSpace"];
    $YSpace = $reticula["YSpace"];
    $XDots = $reticula["XDots"];
    $YDots = $reticula["YDots"];
    $DupDots = $reticula["DuplicateDots"];
    $TotalPlates = $reticula["TotalPlates"];
  }
  $YCoordsInical = $YCoords;
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
  $archivito = new ArchG("numeros");
  $archivito->SensarOrigen();
  //Inicia la rutina asignada
  for($a=1; $a<=$XDots; $a++){
    for($b=1; $b<=$YDots; $b++){
      //if ($TotalPlates != 0 && $archivito->FinRutina(0) == 0){
        if ($TotalPlates != 0){
        // Lavado y vacío
        $archivito->BVac(1);
        for($i=0; $i<$cicLav; $i++){
          $archivito->LugarD("Lavado",$vxy,$vz,"Lugar");
          $archivito->Lavado($osc);
          $archivito->LugarD("Vacío",$vxy,$vz,"Lugar");
          // Último lavado cambia tiempo y enciende la bomba de vacío
          if($i == $cicLav-1)
            $archivito->Espera($utvac);
          else
            $archivito->Espera($tvac);
        }
        $archivito->BVac(0);
       //Toma de muestra y actualiza coordenadas del siguiente lugar de la toma
        $archivito->LugarD("TomaMuestra",$vxy,$vz,"Lugar");
        $archivito->Espera($tmuestra);
        $Muestra[0]++;
        if ($Muestra[0]==$XMuestra){
          $Muestra[0]=0;
          $Muestra[1]++;
          $archivito->ReiniciaCoords(0,"TomaMuestra","Muestra");
          if ($Muestra[1]==$YMuestra){
            $Muestra[1]=0;
            $archivito->ReiniciaCoords(1,"TomaMuestra","Muestra");
            $cambioPlaca[0]++;
          }
          else
            $archivito->ActualizaCoords(1,$YMuestraDistance,"TomaMuestra");
        }
        else
            $archivito->ActualizaCoords(0,$XMuestraDistance,"TomaMuestra");
        // Hace la limpieza de pines y actualiza coordenadas de los siguientes toques
        $archivito->LugarD("ToqueLimpieza",$vxy,$vz,"Lugar");
        for($i=0; $i<$toques; $i++)
          $archivito->InsertarPunto($toques-$i,0.5);
        $Limpieza[0]++;
        if ($Limpieza[0]==$LimpiezaDots){
          $Limpieza[0]=0;
          $archivito->ReiniciaCoords(0,"ToqueLimpieza","Limpieza");
          $Limpieza[1]++;
          if ($Limpieza[1]==$YLimpieza){
            $Limpieza[1]=0;
            $archivito->ReiniciaCoords(1,"ToqueLimpieza","Limpieza");
            $Limpieza[2]++;
            if ($Limpieza[2]==$XLimpieza){
              $Limpieza[2]=0;
              $cambioPlaca[1]++;
            }
          }
          else
            $archivito->ActualizaCoords(1,$YLimpiezaDistance,"ToqueLimpieza");
          if ($Limpieza[2]==1)
            $archivito->ActualizaCoords(0,$XLimpiezaDistance,"ToqueLimpieza");
        }
        else
          $archivito->ActualizaCoords(0,0.5,"ToqueLimpieza");
        // Inserción de puntos en Slides
        $archivito->ActualizaCoords(0,$XCoords,"Slide");
        $archivito->ActualizaCoords(1,$YCoords,"Slide");
        for ($i=1; $i<=$columnasPlaca; $i++){
          for ($j=1; $j<=$filasPlaca; $j++){
            $archivito->LugarD("Slide",$vxy,$vz,"Slide");
            $archivito->PinSB(1,"zslide");
            for($k=0; $k<$DupDots; $k++)
              $archivito->InsertarPunto($DupDots-$k,$YSpace/1000);
            if ($i%2 == 1 && $j!=$filasPlaca)
              $archivito->ActualizaCoords(1,$YSlideDistance,"Slide");
            elseif ($i%2 == 0 && $j!=$filasPlaca)
              $archivito->ActualizaCoords(1,-$YSlideDistance,"Slide");
          }
          $archivito->ActualizaCoords(0,$XSlideDistance,"Slide");
        }
        //Termino de puntos en y
        $archivito->ReiniciaCoords(2,"Slide","Retícula");
        $YCoords+=($YSpace/1000*$DupDots);
        if ($b==$YDots)
          $YCoords=$YCoordsInical;
        // Crea pausas cuando se debe cambiar la placa o el vidrio de limpieza
        if ($cambioPlaca[0] != 0 || $cambioPlaca[1] != 0){
          $archivito->PinSB(0,"zslide");
          if ($TotalPlates > 1)
            $archivito->ActualizaPausa($cambioPlaca[0],$cambioPlaca[1]);
          $cambioPlaca[0]=0;
          $cambioPlaca[1]=0;
          $TotalPlates --;
        }
      }
    }
    //Término de puntos en x
     $XCoords+=($XSpace/1000);
  }
  // Termina en origen y finaliza rutina
  $archivito->LugarD("Origen",$vxy,$vz,"Lugar");
  /*if ($archivito->FinRutina(0) == 1)
    echo "Fin Rutina 2";
  else
    echo "Fin Rutina ".$archivito->FinRutina(2);*/
  $archivito->FinCodigoG();
  //echo get_current_user();
  unset($archivito);
?>
