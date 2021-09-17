<script type="text/javascript" src="js/lavado.js?v=3" ></script>
<?php
	session_name("IFCLab");
	session_start();
	//Inicializacion de variables
	$Ciclo = null;
	$Lavado = null;
	$Toque = null;
	$Vacio = null;
	$Uvacio = null;
	$Tmuestra = null;

	include("bd.php");
	$conexion = ConectarBD();
	if($conexion==false)
		echo "No se pudo establecer la conexión a la base de datos.";
	else
	{
		mysqli_set_charset($conexion,"utf8");
		$sql = "SELECT * FROM lavado";
		// Ya tiene datos
		if ( mysqli_query($conexion, $sql) -> num_rows !=0 )
		{
			$res = mysqli_query($conexion, $sql);
			while ( $dato = mysqli_fetch_assoc($res))
			{
				if ($dato["ID"] == $_SESSION['ID'])
				{
					$Ciclo = $dato["ciclos"];
					$Lavado =  $dato["oscilaciones"];
					$Toque = $dato["toques"];
					$Vacio = $dato["vacio"];
					$Uvacio = $dato["uvacio"];
					$Tmuestra = $dato["tmuestra"];
				}
			}
	    mysqli_free_result($res);
    }
		// Inserta al estar vacía
		if( $Ciclo == null ){
			$Ciclo = 3;
			$Lavado = 4;
			$Toque = 6;
			$Vacio = 2;
			$Uvacio = 3;
			$Tmuestra = 1;
			mysqli_query($conexion, "INSERT INTO lavado (ID, ciclos, oscilaciones, toques, vacio, uvacio, tmuestra) VALUES ('".$_SESSION['ID']."','".$Ciclo."','".$Lavado."','".$Toque."','".$Vacio."','".$Uvacio."','".$Tmuestra."')");
		}
		mysqli_close($conexion);

		// Mandas a llamar página sin guardar datos
		echo "<h4>Opciones de configuración de lavado</h4><hr>
		<div class='row'>
			<div class='col'>
				<form id='lavados'>
					<fieldset class='border-0 p-1'>
					<br/>
						<div class='input-group mb-3'>
						<label for='CicloLavado' class='input-group-text'>Ciclos de lavado</label>
						<input type='text' maxlength='1' class='form-control mx-sm-3' id='CicloLavado' value='$Ciclo'>
						</div>

						<div class='input-group mb-3'>
						<label for='OscilacionesLavado' class='input-group-text'>Oscilaciones de lavado por ciclo</label>
						<input type='text' maxlength='1' class='form-control mx-sm-3' id='OscilacionesLavado' value='$Lavado'>
						</div>

						<div class='input-group mb-3'>
						<label for='LimpiezaPines' class='input-group-text'>Limpieza de pines (toques)</label>
						<input type='text' maxlength='1' class='form-control mx-sm-3' id='LimpiezaPines' value='$Toque'>
						</div>

					</fieldset>
			</div>
			<div class='col'>
				<form id='lavados'>
					<fieldset class='border-0 p-1'>
					<br/>
						<div class='input-group mb-3'>

						<div class='input-group mb-3'>
							<label for='TiempoMuestra' class='input-group-text'>Tiempo de toma muestra (s)</label>
							<input type='text' maxlength='1' class='form-control mx-sm-3' id='TiempoMuestra' value='$Tmuestra'>
						</div>

						<div class='input-group mb-3'>
							<label for='TiempoVacio' class='input-group-text'>Tiempo de vacío (s)</label>
							<input type='text' maxlength='1' class='form-control mx-sm-3' id='TiempoVacio' value='$Vacio'>
						</div>

						<div class='input-group mb-3'>
							<label for='UltimoVacio' class='input-group-text'>Tiempo de último vacío (s)</label>
							<input type='text' maxlength='1' class='form-control mx-sm-3' id='UltimoVacio' value='$Uvacio'>
						</div>

				</fieldset>
			</div>
			</form>
		</div>";
		echo "<button class='update-db-submit' id='guardalavado' hidden> Submit </button>";

	// Actualizar datos
	if( isset( $_POST['DatosLavado'] ) )
	{
		$conexion = ConectarBD();
		if($conexion == false)
			echo "No se pudo establecer la conexión a la base de datos.";
		else
		{
			$Val=explode(",",$_POST['DatosLavado']);
	    // Establece codificación
	    mysqli_set_charset($conexion,"utf8");
			$Cicloss = $Val[0];
			$Lavadoss = $Val[1];
			$Toquess = $Val[2];
			$Vacioss = $Val[3];
			$Uvacioss = $Val[4];
			$Tmuestrass = $Val[5];
			$sql = "UPDATE lavado SET ciclos='".$Cicloss."',oscilaciones='".$Lavadoss."',toques='".$Toquess."',vacio='".$Vacioss."',uvacio='".$Uvacioss."',tmuestra='".$Tmuestrass."' WHERE ID = '".$_SESSION['ID']."'";
	    	$res = mysqli_query($conexion, $sql);
			}
			mysqli_close($conexion);
		}
	}
	session_write_close();
?>
