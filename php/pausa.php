<?php
  // Verifica la sesión y asigna valor de pausa
  session_name("IFCLab");
  session_start();
  // Consulta proceso actual
  if( isset($_POST['consul']) ){
    // Coordenadas XYZ
    echo $_SESSION['Actual'][0]."-".$_SESSION['Actual'][1]."-";
    // Valores de cambio recibidos y limpiados después para evitar pausas repetidas
    echo $_SESSION['Cambio'][0]."-".$_SESSION['Cambio'][1];
    $_SESSION['Cambio'] = [0,0];
    // Primera vez, manda valores de muestra y elimina campo de sesión
    if( isset($_SESSION['Muestra']) ){
      echo "-".$_SESSION['Muestra'][0]."-".$_SESSION['Muestra'][1];
      unset($_SESSION['Muestra']);
    }
  }
  // Tipo "0" pausa y "1" paro total; pausa "1" activado, pausa "0" desactivado
  else{
    $tipo = $_POST['tipo'];
    $pausa = $_POST['pausa'];
    // Activa o desactiva pausa
    if($tipo == 0)
      $_SESSION['Pausa'] = $pausa;
    // Realiza paro total a través de la sesión e interrupción principal
    else{
      $_SESSION['Fin'] = 1;
      $str = "sudo bash ../C/pinesExt.sh 1";
      exec($str);
    }
    echo "Pausafin:".$_SESSION['Pausa']."-".$_SESSION['Fin'];
  }
  session_write_close();
?>
