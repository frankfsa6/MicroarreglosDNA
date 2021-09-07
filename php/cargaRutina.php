<?php
  session_name("IFCLab");
  session_start();
  include ("bd.php");
  $conexion = ConectarBD();
  if(isset($_POST['cargaok'])){
    $_SESSION['ID'] = $_SESSION['IDTemp'];
  }
  else{
    if(isset($_POST['rutinaCarga'])){
      //Valida si la rutina ya será borrada o no
      if($_POST['rutinaCarga'] == "borrarRutina")
      {
        $tablaDB = array("rutinas","slide","pines","reticula","lavado");
        foreach($tablaDB as $campo){
          $cadena = "DELETE FROM ".$campo." WHERE ID = '".$_SESSION['Rutina']."'";
          $query = mysqli_query($conexion, $cadena);
        }
        //Si la rutina que se borro es la misma que se habia cargado, se borra la sesión
  			if($_SESSION['Rutina'] == $_SESSION['ID']){
        unset($_SESSION['ID']);
        }
      }
      else
      {
        unset($_SESSION['Rutina']);
        //Explode para realizar la acción del botón
        $valorRecibido=explode(";",$_POST['rutinaCarga']);
        //En la primera parte del vector se tiene la acción y en la segunda el // ID
        $accion = $valorRecibido[0];
        $nombreRutina =  $valorRecibido[1];

        $cadena = "SELECT nombreRutina,ID FROM rutinas WHERE nombreRutina = '".$nombreRutina."'";
        $query = mysqli_query($conexion, $cadena);
        while ($row = mysqli_fetch_row($query))
          $resDB[]=$row;
        //En el primer campo se encuentra el nombre y en el segundo se encuentra el ID
        $ID = $resDB[0][1];
        //echo $ID;
        //Una vez obtenido el ID se ejecuta la acción
        if($accion == 'carga'){
          //carga el valor del ID a la sesión
          echo "carga";
          $_SESSION['IDTemp'] = $ID;
        }
        else if($accion == 'borra'){
          echo "borrar";
          //Se carga el ID a otra sesion para que, en caso de cancelar, no se sobreescriban los datos
          $_SESSION['Rutina']= $ID;
        }
      }
    }
    else
    {
      if($conexion != false)
      {
        //Se da formato a la tabla que se mostrara en pantalla
        $resDB = null;
        $cadena = "SELECT nombreRutina, ID FROM rutinas WHERE Temporal = 0";
        $query = mysqli_query($conexion, $cadena);
        while ($row = mysqli_fetch_row($query))
          $resDB[]=$row;
        //en la localidad 0 se encuentra el nombre y en la localidad [1] se encuentra el ID
        if($resDB != null)
        {
          //Genera la tabla que contiene las bases de datos
          echo "<table class='table'> <thead> <tr>";
          echo " <th> </th> <th scope='col'> Nombre de la rutina </th> <th scope='col'>ID</th> <th> </th> </thead> <tbody>";
        //recorre todos los nombres de las bases de datos para buscar que no se repita el nombre
          for($indice=0 ; $indice < count($resDB) ; $indice++)
          {
            echo "<tr>";
            //Genera una nueva entrada de la tabla por cada valor del índice
            echo "<td> <button type='button' class='btn btn-outline-success' id='carga;".$resDB[$indice][0]."'></button> </td>";
            echo "<td> ".$resDB[$indice][0]." </td>";
            echo "<td> ".$resDB[$indice][1]." </td>";
  	        echo "<td> <button type='button' class='btn btn-outline-danger' id='borra;".$resDB[$indice][0]."'>Borrar</button> </td>";
            echo "</tr>";
          }
          echo "</tbody> </table>";
        }
        else{
          echo "<h3>Advertencia: </h3><div class='alert alert-warning' role='alert'>
          </br><strong>No hay ninguna rutina en la base de datos</strong>
          ";

        }
        mysqli_close($conexion);
      }
      else
      {
        //Un uno en la cadena de retorno indica que hubo un error al conectar con la base de datos
        echo "</br></br><div class='alert alert-danger' role='alert'> Hubo un error al conectar con la base de datos </div>";
      }
    }
  }
  session_write_close();
?>
