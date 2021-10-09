<script type="text/javascript" src="js/reticula.js?v=3" ></script>
<link rel="stylesheet" href="css/reticula.css?v=3">
<?php
  include("bd.php");
  session_name("IFCLab");
  session_start();
  $ID=$_SESSION['ID'];
  $conexion = ConectarBD();
  if($conexion == false)
    echo "No se pudo establecer la conexión a la base de datos.";
  else
  {
  	mysqli_set_charset($conexion,"utf8");
  	$sql = "SELECT * FROM pines";
    if ( mysqli_query($conexion, $sql)->num_rows !=0 )
    {
      $res = mysqli_query($conexion, $sql);
      while ( $dato = mysqli_fetch_assoc($res) )
      {
    	 	if ($dato["ID"]==$ID)
        {
    			$Pines["X"] = [$dato["PinesX"]];
    			$Pines["Y"]=  [$dato["PinesY"]];
    		}
	     }
      mysqli_free_result($res);
      //UPDATE `pines` SET `Valor`= 4 WHERE Eje = 'X'
    }
    else
    {
      $Pines = [$ID,12];
      mysqli_query($conexion, "INSERT INTO pines (ID, PinesX, PinesY) VALUES ('".$Pines[0]."','".$Pines[1]."',4)");
      $Pines = ["X"=>[12], "Y"=>[4]];
    }
  	$Valores=null;
  	$sql = "SELECT * FROM reticula";
  	if ( mysqli_query($conexion, $sql)->num_rows !=0 )
    {
      $res = mysqli_query($conexion, $sql);
  	  while ( $dato = mysqli_fetch_assoc($res) )
      {
    	 	if ($dato["ID"]==$ID)
        {
    			$Valores[0] = $dato["YCoords"];
    			$Valores[1] = $dato["XCoords"];
    			$Valores[2] = $dato["YSpace"];
    			$Valores[3] = $dato["XSpace"];
    			$Valores[4] = $dato["YDots"];
    			$Valores[5] = $dato["XDots"];
    			$Valores[6] = $dato["DuplicateDots"];
    			$Valores[7] = $dato["PlateState"];
         		$Valores[8] = $dato["TotalPlates"];
    	}
  	  }
      mysqli_free_result($res);
	   }
     if ($Valores==null)
     {
       for ($i=0;$i<2;$i++) $Valores[$i]=5;
       for ($i=2;$i<4;$i++) $Valores[$i]=300;
       for ($i=4;$i<6;$i++) $Valores[$i]=15;
       $Valores[6]=1;
       $Valores[7]=1;
       $Valores[8]=28;
       $sql = "INSERT INTO reticula (ID, YCoords, XCoords, YSpace, XSpace, YDots, XDots, DuplicateDots, PlateState, TotalPlates) VALUES ('".$ID."','".$Valores[0]."','".$Valores[1]."','".$Valores[2]."','".$Valores[3]."','".$Valores[4]."','".$Valores[5]."','".$Valores[6]."','".$Valores[7]."','".$Valores[8]."')";
       mysqli_query($conexion, $sql);
     }
     mysqli_close($conexion);
     // Configuración de Rejillas
     echo "<h4 id=".implode(",",$Pines['X']).implode(",",$Pines['Y']).">Opciones de configuración de retícula</h4><hr><br/>
      <hidden id=".$Valores[6]."></hidden>
      <div class='row'>
    		<div class='col'>
    			<form id='placas'>
  					<div class='input-group mb-3'>
						<label for='xCoords' class='input-group-text'>Coordenadas de rejilla en eje X (mm)</label>
						<input type='text' maxlength='4' class='form-control mx-sm-3' id='XCoords' value='$Valores[1]'>
  					</div>
					<div class='input-group mb-3'>
						<label for='YCoords' class='input-group-text'>Coordenadas de rejilla en eje Y (mm)</label>
						<input type='text' maxlength='4' class='form-control mx-sm-3' id='YCoords' value='$Valores[0]'>
  					</div>
				<hr>
  					<div class='input-group mb-3'>
						<label for='XDotSpace' class='input-group-text'>Espaciado de puntos en eje X (&mu;m)</label>
						<input type='text' maxlength='4' class='form-control mx-sm-3' id='XDotsSpace' value='$Valores[3]'>
  					</div>
					<div class='input-group mb-3'>
						<label for='YDotSpace' class='input-group-text'>Espaciado de puntos en eje Y (&mu;m)</label>
						<input type='text' maxlength='4' class='form-control mx-sm-3' id='YDotsSpace' value='$Valores[2]'>
  					</div>
  					<div class='input-group mb-3'>
						<label for='XDots' class='input-group-text'>Número de puntos por rejilla en eje X</label>
						<input type='text' maxlength='3' class='form-control mx-sm-3' id='XDots' value='$Valores[5]'>
  					</div>
					<div class='input-group mb-3'>
					  <label for='YDots' class='input-group-text'>Número de puntos por rejilla en eje Y</label>
					  <input type='text' maxlength='3' class='form-control mx-sm-3' id='YDots' value='$Valores[4]'>
					</div>
  				<hr>
  					<div class='input-group mb-3'>
						<label for='DuplicateDotsY' class='input-group-text'>Puntos Y duplicados por rejilla</label>
						<select class='form-control mx-sm-3' id='DuplicateDotsY'></select>
  					</div>
  					<div class='custom-control custom-switch'>";
          				$Check=($Valores[7]=='1')?'checked':'';
         				 echo"<input type='checkbox' class='custom-control-input' id='PlateState' ".$Check.">
  				    		<label class='custom-control-label' for='PlateState'>¿Utilizar placas completas?</label>
  				    </div> </br>
					<div class='input-group mb-3'>
					  <label for='NoPlates' class='input-group-text'>Total de placas a realizar</label>
					  <input type='text' class='form-control' id='NoPlates' readonly>
					</div>
  			    <hr>
				<label><b>Dirección de impresión de placas:</b> izquierda-derecha y arriba-abajo</label>
				<label><b>Dirección de impresión de slides:</b> arriba-abajo e izquierda-derecha</label>
    		</div>
    		<div class='col'>
    			<center><div class='btn-group btn-group-toggle' data-toggle='buttons'>
    			<label class='btn btn-outline-primary active'>
    				<input type='radio' name='Show' id='ShowSlides' autocomplete='off' checked>Mostrar diseño de sólo un pin</label>
    			<label class='btn btn-outline-primary'>
    				<input type='radio' name='Show' id='ShowPin' autocomplete='off'>Mostrar diseño del slide completo</label>
    			</div></center>
    			<br/>
    			<div id='Figura'> </div>
    		</div>
    		</form>
    	</div>";
  	////////// Botón de Prueba
  	echo "<input class='update-db-submit' type='submit' id='guardaplaca' value='Submit' hidden></br>";
  	//////////  Fin de Botón de Prueba
  	if(isset($_POST['DatosPlaca']))
    {
  		$conexion = ConectarBD();
    	if($conexion != false)
    	{
    		$DP=$_POST['DatosPlaca'];
    		$Val=explode(",",$DP);
    		//$Update= "INSERT INTO pines (PinesX, PinesY, ID) VALUES ('.$PX.',4,1) ON DUPLICATE KEY UPDATE PinesX='.$PX.',PinesY=4";
    		$Update= "UPDATE reticula SET YCoords='".$Val[0]."',XCoords='".$Val[1]."',YSpace='".$Val[2]."',XSpace='".$Val[3]."' WHERE ID = '".$_SESSION['ID']."' ";
    		//where ID='$ID'
    		$query = mysqli_query($conexion, $Update);
    		$Update= "UPDATE reticula SET YDots='".$Val[4]."',XDots='".$Val[5]."',DuplicateDots='".$Val[6]."',PlateState='".$Val[7]."',TotalPlates='".$Val[8]."' WHERE ID = '".$_SESSION['ID']."'";
    		// where ID='$ID'
    		$query = mysqli_query($conexion, $Update);
        mysqli_close($conexion);
    	}
    		//DELETE from pines
    }
  }
  session_write_close();
?>
