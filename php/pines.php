<script type="text/javascript" src="js/pines.js?v=3" ></script>
<?php

 include("bd.php");
 $conexion = ConectarBD();
 if($conexion == false)
    echo "No se pudo establecer la conexión a la base de datos.";
 else
 {
    session_name("IFCLab");
    session_start();
    mysqli_set_charset($conexion,"utf8");
  	if(isset($_POST['creaID'])){
  	    $date = getdate();
  		   $_SESSION['ID'] =  $date['year'] . '-' . $date['mon'] . '-' . $date['mday'] . '-' . $date['hours'] . '-' . $date['minutes'] . '-' . $date['seconds'];
  		//Regresa un número aleatorio entre 0 y el valor máximo de rand para generar un nombre al azar de rutina
        $rutinaX = rand();
        //Guarda la rutina temporalmente con el ID recién generado
        $query = "INSERT INTO rutinas (ID, nombreRutina,Temporal, rutinaIniciada) VALUES ('".$_SESSION['ID']."','Rutina".$rutinaX."',1,1)";
        mysqli_query($conexion,$query);
        }
  	$ID = $_SESSION['ID'];
  	$PinesX = null;
  	$sql = "SELECT * FROM pines";
    if ( mysqli_query($conexion, $sql)->num_rows !=0 )
    {
      $res = mysqli_query($conexion, $sql);
      while ( $dato = mysqli_fetch_assoc($res) )
      {
  	 	   if ($dato["ID"] == $ID)
         {
  			      $PinesX = $dato["PinesX"];
  		   }
  	  }
    mysqli_free_result($res);
    //UPDATE `pines` SET `Valor`= 4 WHERE Eje = 'X'
    }
  	if ($PinesX==null)
    {
      $PinesX = 12;
      mysqli_query($conexion, "INSERT INTO pines (ID, PinesX, PinesY) VALUES ('".$ID."','".$PinesX."',4)");
    }
  	mysqli_close($conexion);

    echo "<h4>Opciones de configuración de pines</h4><hr>";
    echo "<form>
  		<div class='row'>
  		<div class='col'>
  			<form id='placas'>
  				<fieldset class='borderless'>
  				<br/><br/>
  					<div class='input-group mb-3'>
  						<label for='Pines_ejeX' class='input-group-text'>Número de pines en eje X</label>
  					    <select class='custom-select' id='Pines_ejeX'>";
  						for($i=4; $i<=12; $i=$i+2){
  							if($i == $PinesX)
  								echo "<option value='$i' selected>$i</option>";
  							else if($i!=10)
  								echo "<option value='$i'>$i</option>";
  							}
      					echo "</select>
  					</div>

  					<div class='input-group mb-3'>
  						<label for='Pines_ejeY' class='input-group-text'>Número de pines en eje Y</label>
  						<select class='custom-select' id='Pines_ejeY' disabled='true' >
  						<option value='4'>4</option>
  						</select>
  					</div>

            <br/><div class='input-group mb-4'>
             <div class='input-group-prepend'>
               <label class='input-group-text' for='NoPines'> Total de pines </label>
             </div>
             <input class='form-control' type='text' id='NoPines' readonly>
           </div>

    			 </form>
    		</div>

    		<div class='col'>
    			<div id='Figura'>

    			</div>
    		</div>
    		</div>
        </fieldset>
     </form></br>";
    ////////// Botón de Prueba
    echo "<input class='update-db-submit' type='submit' id='guardapines' value='Submit' hidden></br>";
    //////////  Fin de Botón de Prueba
    if(isset($_POST['DatosPines']))
    {
    	$conexion = ConectarBD();
    	if($conexion != false)
    	{
    		$PX = $_POST['DatosPines'];
    		//$Update= "INSERT INTO pines (PinesX, PinesY, ID) VALUES ('.$PX.',4,1) ON DUPLICATE KEY UPDATE PinesX='.$PX.',PinesY=4";
    		$Update= "UPDATE pines SET PinesX = '".$PX."' WHERE ID = '".$_SESSION['ID']."'";
    		$query = mysqli_query($conexion, $Update);
            //echo $query;
            mysqli_close($conexion);
    	}
    //DELETE from pines
    }
 }
?>
