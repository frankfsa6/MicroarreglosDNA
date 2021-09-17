<?php
  include("ArchC.php");
  $joystick = new ArchC();
  // Devuelve mm/pasos en cada motor
  if( isset($_POST['pasoslug']) ){
    $str = $joystick->JoysLugDB( $_POST['pasoslug'] );
  }
  // Realiza pruebas en las conexiones eléctricas
  elseif( isset($_POST['prueba']) ){
    $str = $joystick->JoysPruebaCon( $_POST['prueba'] );
  }
  // Acciona bomba de agua desde popup de rutina
  elseif( isset($_POST['popup']) ){
    $str = $joystick->BAgua( $_POST['popup'] );
  }
  // Va al origen o calibración
  else{
    $datos = explode(",",$_POST['bot']);
    // Si es primera vez, devuelve ceros
    if( sizeof($datos) == 1){
      $joystick->SensarOrigen();
      $str = "Primera vez al sensar origen (no hay pasos perdidos), 0 pasos = 0 &mu;m, 0 pasos = 0 &mu;m, 0 pasos = 0 &mu;m";
    }
    // Devuelve pasos perdidos
    else{
      // Encabezado al perder pasos
      if( $datos[0]== "o" ){
        $datos = 1;
        $str = "Pasos perdidos al sensar origen";
      }
      // Limpia contraseña y encabezado
      else{
        $regex = "#[\$\(\)\!\&\%\[\]\+\=\/\{\"\}\-]#";
        $datos[1] = preg_replace($regex, "", $datos[1]);
        $str = "Calibración del lugar solicitado: ".$datos[2];
      }
      // Ejecuta y devuelve mensajes de error o mediciones realizadas
      $str .= $joystick->SensarOrigen($datos);
    }
  }
  // Devuelve datos conseguidos
  unset($joystick);
  echo $str;
?>
