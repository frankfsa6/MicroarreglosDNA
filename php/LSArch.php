<?php
  //Archivo contenedor de todas las funciones
  //Actualizacion de base de datos, sesiones, etc
  session_name("IFCLab");
  session_start();
  include("bd.php");
  $conexion = ConectarBD();
  //Contiene todas las funciones
  if($conexion != false){
    //1.- Actualizar datos pines
    if(isset( $_POST['actualizaPines'] )){
      $codigo = "// Pines para fin de código, pulso, dirección, sensores, bomba de agua, vacío y botón de emergencia\n";
      // Escribe pin especial (no ubicado en base de datos)
      $codigo .= "#define finCodC 1\n";
      //Obtiene los datos de la base
      mysqli_set_charset($conexion,"utf8");
      $sql = "SELECT id, valor FROM raspberry WHERE tipo='gpio'";
      // Guarda nombre de pin y valor
      if ( mysqli_query($conexion, $sql)->num_rows !=0 ) {
        $res = mysqli_query($conexion, $sql);
        while ($row = mysqli_fetch_row($res))
          $pines[$row[0]]=$row[1];
      }
      // Escribe cadena completa de datos con pines
      mysqli_free_result($res);
      foreach($pines as $pinAsoc=>$valAsoc){
        $codigo .= "#define ".$pinAsoc." ".$valAsoc."\n";
      }
      // Modifica archivo de pines y demás
      $fh = fopen("../C/pinesRasp.h", 'w+');
      $codigo .= "// Valores predefinidos\n#define pasosRet 500\n#define velOrig 400\n";
      fwrite($fh, $codigo);
      fclose($fh);
      // Compila los archivos con gcc
      compilaArchC();
    }

    //2.- BorraTemporal
    if(isset( $_POST['borraTemp'] )){
      $tablas = array ('pines','reticula','slide','lavado','rutinas');
  		$query = mysqli_query($conexion,"SELECT ID FROM rutinas WHERE Temporal = 1");
  		while ($row = mysqli_fetch_row($query))
  			$resDB[]=$row;
  		//Devuelve el id de todas las rutinas que no hayan sido guardadas
  		for($i = 0; $i < count($resDB); $i++){
  			foreach($tablas as $indice)
  			$query = mysqli_query($conexion,"DELETE FROM ".$indice." WHERE ID='".$resDB[$i][0]."'");
  		}
  		echo "0";
      // Borra sesión actual
      session_destroy();
    }

    //3.- Confirmar la sesion
    if(isset( $_POST['checkSesion'])){
      if(isset($_SESSION['ID'])){
        echo '1';
      }
    }

    //4.-confirmLavado
    if(isset( $_POST['confirmLavado'] )){
      //Verifica si la rutina en la que se está trabajando tiene definido el campo de lavado
      if(isset($_SESSION['ID'])){
        $rutina = $_SESSION['ID'];
        //Se solicita el ID de la base de datos en la tabla de lavado
        $query = mysqli_query($conexion,"SELECT ID FROM lavado WHERE ID='".$rutina."'");
        $resDB=null;
        while ($row = mysqli_fetch_row($query))
          $resDB[]=$row;
        //Si esta definido el valor muestra el boton de iniciar proceso
        if($resDB != null)
          echo "0";
        else
          echo "1";
      }
      else
        echo "1";
    }

    //5.- Temporal
    if(isset( $_POST['temporal'] )){
      //Verifica si la rutina en la que se está trabajando está guardada
  		if(isset($_SESSION['ID'])){
  			$rutina = $_SESSION['ID'];
  			//Solo se solicita el temporal y se procesa en JQuery
  			$query = mysqli_query($conexion,"SELECT Temporal FROM rutinas WHERE ID='".$rutina."'");
  			while ($row = mysqli_fetch_row($query))
  				$resDB[]=$row;
  			//Devuelve el valor obtenido del temporal de la base de datos 0  es guardado y 1 es no guardado
  			//Se asegura que el valor este dentro de los posibles
  			if(isset($resDB)) {
  				if($resDB[0][0] == "0")
  					echo "0";
  				else{
  					if($resDB[0][0] == "1")
  						echo "1";
  					}
  				}
  				else{
  					echo "2";
  					unset($_SESSION['ID']);
  				}
  		}
  		else
  			echo "0";
    }

    //6.-ActualizarDBconfig
    if(isset( $_POST['uDBConfig'] )){
      $config = $_POST["config"];
      $raspberry = $_POST["raspberry"];
      // Establece codificación
      mysqli_set_charset($conexion,"utf8");
      // Guarda datos de tabla configuración
      for($i=0; $i<sizeof($config); $i++){
        $exp = explode(";",$config[$i]);
        $sql = "UPDATE config SET x='".$exp[1]."',y='".$exp[2]."',z='".$exp[3]."' WHERE nombre='".$exp[0]."'";
        mysqli_query($conexion, $sql);
      }
      // Guarda datos de tabla de pines raspberry
      for($i=0; $i<sizeof($raspberry); $i++){
        $exp = explode(";",$raspberry[$i]);
        $sql = "UPDATE raspberry SET valor='".$exp[1]."' WHERE id='".$exp[0]."'";
        mysqli_query($conexion, $sql);
      }
    }

    //7.-Unset Login
    if(isset( $_POST['unsetLogin'] )){
      unset($_SESSION['login']);
    }

    //8.-Confirmar login
    if(isset( $_POST['confirmLogin'] )){
    	$pass=$_POST['Contrasena'];
    	$cadenaRetorno='';
    	if(!empty($pass))
    	{
    		if($pass == $password)
    		{
    			$cadenaRetorno = "0";
    			$_SESSION['login'] = 1;
    		}
    		else
    			$cadenaRetorno = "La contraseña ingresado no es la correcta";
    	}
    	else
    		$cadenaRetorno = "No se ha ingresado una contraseña";
    	echo $cadenaRetorno;
    }

    //9.- Verifica si la rutina ya fue iniciada
    if(isset( $_POST['rutinaIniciada'] )){
      if(isset($_SESSION['ID'])){
  			//Solo se solicita el temporal y se procesa en JQuery
  			$query = mysqli_query($conexion,"SELECT rutinaIniciada FROM rutinas WHERE ID='".$_SESSION['ID']."'");
  			while ($row = mysqli_fetch_row($query))
  				$resDB[]=$row;
        if(isset($resDB))
            echo $resDB[0][0];
        }
      }

    //10.- Actuakiza la base cambiando el valor cuando se presiona el boton de iniciar rutina
    if(isset( $_POST['actualizaRutina'] )){
      if(isset($_SESSION['ID'])){
        //Solo se solicita el temporal y se procesa en JQuery
        $query = mysqli_query($conexion,"UPDATE rutinas SET rutinaIniciada=0 WHERE ID='".$_SESSION['ID']."'");
      }
    }

    mysqli_close($conexion);
  }
  else{
    echo "2";
  }
  session_write_close();
?>
