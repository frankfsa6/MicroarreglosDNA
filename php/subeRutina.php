<?php
  session_name("IFCLab");
  session_start();
  //Adquiere el nombre de rutina mandado desde AJAX
  $nombreRutina = $_POST['rutina'];
  $reRutina = $_POST['re'];
  //Define que hará la página
  //$accion = $_POST['accion'];
  include ("bd.php");
  $conexion = ConectarBD();
  //echo $reRutina;
  //Inicializacion de variables
  $varRetorno = '';
  if($conexion != false) {
    //Se conecta con la base de datos y guarda el nombre y ID de la rutina que se guarda
    //Verifica si está activa la sessión
    if(isset($_SESSION['ID']))
    {
      if($reRutina == "1")
      {
        $resDB = null;
        $cadena = "SELECT nombreRutina FROM rutinas WHERE ID != '".$_SESSION['ID']."'";
        $query = mysqli_query($conexion, $cadena);
        while ($row = mysqli_fetch_row($query))
          $resDB[]=$row;
        if($resDB != null)
        {
        //recorre todos los nombres de las bases de datos para buscar que no se repita el nombre
          for($indice=0 ; $indice < count($resDB) ; $indice++)
          {
            //Verifica que no se haya encontrado el nombre de la rutina
            if( ( $resDB[$indice][0] == $nombreRutina ) && $varRetorno == '')
            {
              //Si encuentra al usuario manda un error y no sube nada a la base
              //Un cero en la cadena de retorno indica que el nombre ya existe
              $varRetorno = '0';
              $_SESSION['nombreRutina'] = $nombreRutina;
            }

          }
        }
        if($varRetorno == '')
        {
          $cadena = "UPDATE rutinas SET nombreRutina='".$nombreRutina."', Temporal=0 WHERE ID='".$_SESSION['ID']."'";
          $query = mysqli_query($conexion,$cadena);
          // Un dos en la variable de retorno significa que el nombre en la base de datos se establecio de manera correcta
          $varRetorno = '2';
        }
      }
      //Se sobreescribe el nombre de la rutina
      else{
        //Obtiene el ID de la rutina vieja
        $cadena = "SELECT ID FROM rutinas WHERE nombreRutina='".$_SESSION['nombreRutina']."'";
        $query = mysqli_query($conexion, $cadena);
        while ($row = mysqli_fetch_row($query))
          $resDB[]=$row;
        //$resDB[0][0] contiene el ID viejo
        //echo $resDB[0][0];
        $tablaDB = array("rutinas","rejillas","pines","placa","lavado");
        foreach($tablaDB as $campo){
          $cadena = "DELETE FROM ".$campo." WHERE ID = '".$resDB[0][0]."'";
          $query = mysqli_query($conexion, $cadena);
        }
        //Ya esta borrada la vieja rutinas
        $cadena = "UPDATE rutinas SET nombreRutina='".$_SESSION['nombreRutina']."', Temporal=0 WHERE ID='".$_SESSION['ID']."'";
        $query = mysqli_query($conexion,$cadena);
        unset($_SESSION['nombreRutina']);
      }
    }
    mysqli_close($conexion);
  }
  else
  {
    //Un uno en la cadena de retorno indica que hubo un error al conectar con la base de datos
    $varRetorno = '1';
  }
  echo $varRetorno;
  session_write_close();
?>
