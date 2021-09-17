<?php
  include("ArchC.php");
  $joystick = new ArchC();
  // Finaliza código pendiente al ejecutar bash con GPIO1 para fin de código Joystick
  if( isset($_POST['fin']) ){
    $str = "sudo bash ../C/pinesExt.sh 1";
    exec($str);
    // Detiene por medio de la sesión
    session_name("IFCLab");
    session_start();
    $_SESSION['Fin'] = 1;
    session_write_close();
    // Detiene bombas
    $joystick->BAgua(0);
    $joystick->BVac(0);  
  }
  // Mueve motores con flechas acorde con velocidad 1,2,3
  else{
    $vel = $_POST['vel'];
    // Mueve motores simples: "eje, dir, mmActual"
    if( isset($_POST['bot']) ){
      $datos = explode(",",$_POST['bot']);
      $str = $joystick->JoysMot( $datos[0], $datos[1], floatval($datos[2]) , $vel );
    }
    // Mueve en diagonales: "dirX, dirY, mmActualX, mmActualY"
    else{
      $datos = explode(",",$_POST['diag']);
      $str = $joystick->JoysMotD( $datos[0], $datos[1], floatval($datos[2]), floatval($datos[3]) , $vel );
    }
  }
  // Devuelve datos conseguidos
  echo $str;
  unset($joystick, $str);
?>
