<?php
  $password = "Fisio";
  $wusuario = "root";
  $wcontras = "";
  //$wcontras = "1234";
  $lusuario = "raspberry";
  $lcontras = "microarreglosdna";
  // Conecta en linux-windows
  function ConectarBD(){
    global $lusuario, $lcontras, $wusuario, $wcontras;
    $conexion = false;
    // En caso de detectar windows (modo prueba), no lleva contraseña
    if( strtoupper(substr(PHP_OS, 0, 3) ) == "WIN" )
      $conexion = @mysqli_connect("localhost", $wusuario, $wcontras, "dbrobot");
    // Al detectar LINUX, debe tomar datos definidos por PHPMYADMIN
    elseif( strtoupper(substr(PHP_OS, 0, 3) ) == "LIN" )
      $conexion = @mysqli_connect("localhost", $lusuario, $lcontras, "dbrobot");      
    return $conexion;
  }
  //Archivo que descarga los datos de la base para generar las rutinas
  function getDBdata(string $tabla){
    session_name("IFCLab");
    session_start();
    $conexion = ConectarBD();
    if($conexion != false){
      $datos = null;
      mysqli_set_charset($conexion,"utf8");
      $sql = "SELECT * FROM ".$tabla." WHERE ID='".$_SESSION['ID']."'";
      if ( mysqli_query($conexion, $sql) -> num_rows !=0 ) {
        $res = mysqli_query($conexion, $sql);
        //Obtiene los datos de la base en un array asociativo
        $datos = mysqli_fetch_assoc($res);
      }
      mysqli_free_result($res);
      mysqli_close($conexion);
    }
    else
      echo "Error al conectar el sistema";
    session_write_close();
    //Regresa el arreglo con los datos obtenidos
    return $datos;
  }
  // Compila todos los archivos C básicos para movimientos de joystick y rutinas
  function compilaArchC(){
    $nombre = ["o","z","xy","yz","xj","yj","zj","xyj","prueba"];
	  for($i = 0; $i<sizeof($nombre); $i++){
      // Elimina objetos anteriores
      $str = "sudo rm ../C/$nombre[$i] -f 2>&1";
      exec($str);
      // Compila y crea nuevos objetos ejecutables
      $str = "sudo gcc -Wall -pthread -o ../C/$nombre[$i] ../C/$nombre[$i].c -lpigpio -lm 2>&1";
      exec($str);
  	}
    // Asigna permisos a la carpeta
    $str = "sudo chmod -R -f 0707 ../../Rasp";
    exec($str);
		$str = "sudo chown -R pi:pi ../../Rasp";
    exec($str);
  }
  // Primera vez que checa la base de datos o la crea completa
  if( isset($_POST['inicia']) ){
    global $lusuario, $lcontras, $wusuario, $wcontras;
    $existe = false;
    $conexion = false;
    // En caso de detectar windows (modo prueba), no lleva contrase?a
    if( strtoupper(substr(PHP_OS, 0, 3) ) == "WIN" )
      $conexion = mysqli_connect("localhost", $wusuario, $wcontras);
    // Al detectar LINUX, debe tomar datos definidos por PHPMYADMIN
    elseif( strtoupper(substr(PHP_OS, 0, 3) ) == "LIN" )
      $conexion = mysqli_connect("localhost", $lusuario, $lcontras); 
    // Compila los archivos con gcc
    compilaArchC();
    // Verifica que sea pedido por principal
    if( $_POST['inicia'] == 2 && $conexion != false ){
      // Crea base de datos si no existe
      $sql = "CREATE DATABASE IF NOT EXISTS dbrobot COLLATE 'utf8_spanish2_ci' ";
      mysqli_query($conexion, $sql);
      // Verifica si existe base con datos
      $conexion = ConectarBD();
      mysqli_set_charset($conexion,"utf8");
      $sql = "SHOW TABLES";
      $res = mysqli_query($conexion, $sql);
      // Crea base de datos con archivo externo SQL
      if( $res->num_rows == 0 ){
        $sql = "";
        $archSQL = file("../sql/dbrobot.sql");
        // Comienza a recorrer el archivo
        foreach($archSQL as $linea){
          $linea = trim($linea);
          // No manda si es comentario o en blanco
          if( $linea != "" && substr($linea, 0, 2) != "--" && substr($linea, 0, 2) != "/*" ){
            $fin = substr($linea, -1, 1);  
            $sql .= $linea;
            // Ejecuta si encuentra un ;
            if( $fin == ";" ){
              mysqli_query($conexion, $sql);
              $sql = "";
            }
          }
        }
      }
      // Manda todo bien
      $existe = true;
    }
    // Manda valor final a principal
    echo $existe;
  }
  //Regresa el nombre de la rutina
  if( isset($_POST['nombreRutina']) ){
    $rutina = getDBdata("rutinas");
    if( $rutina != null )
      $nombreRutina = $rutina["nombreRutina"];
      echo $nombreRutina;
  }

?>
