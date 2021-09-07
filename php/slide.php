<link rel="stylesheet" href="css/slide.css?v=3">
<script type="text/javascript" src="js/slide.js?v=3" ></script>
<?php

	session_name("IFCLab");
	session_start();
	//Inicialización de variables
	$Ren = null;
	$Col = null;

	include("bd.php");
	$conexion = ConectarBD();
	if($conexion == false)
		echo "No se pudo establecer la conexión a la base de datos.";
	else
	{
		mysqli_set_charset($conexion,"utf8");
		$sql = "SELECT * FROM slide";
		// Ya tiene datos
		if ( mysqli_query($conexion, $sql) -> num_rows !=0 )
		{
			$res = mysqli_query($conexion, $sql);
			while ( $dato = mysqli_fetch_assoc($res))
			{
				if ($dato["ID"] == $_SESSION['ID'])
				{
					$Ren = $dato["filasplaca"];
					$Col =  $dato["columnasplaca"];
				}
			}
    mysqli_free_result($res);
    }
		// Inserta al estar vacía
		if( $Ren == null)
		{
			$Ren = 1;
			$Col = 5;
			mysqli_query($conexion, "INSERT INTO slide (ID, columnasplaca, filasplaca) VALUES ('".$_SESSION['ID']."','".$Col."','".$Ren."')");
		}
		mysqli_close($conexion);

		// Mandas a llamar página sin guardar datos


		echo "<h4>Opciones de configuración de slide</h4><hr><form>
				<div class='form-row'>
					<div class='col'><br><br>
						<div class='input-group mb-4'>
							<div class='input-group-prepend'>
								<label class='input-group-text' for='ejeX'> Número de slides en eje X </label>
							</div>
							<select class='custom-select' id='ejeX'>";
				for($i=1; $i<=5; $i++)
				{
        	if($i == $Col)
						echo "<option value='$i' selected>$i</option>";
		      else
						echo "<option value='$i'>$i</option>";
				}
    					echo "</select>
						</div>
						<div class='input-group mb-4'>
							<div class='input-group-prepend'>
								<label class='input-group-text' for='ejeY'> Número de slides en eje Y </label>
							</div>
							<select class='custom-select' id='ejeY'>";

                for($i=1; $i<=10; $i++)
								{
									if($i == $Ren)
										echo "<option value='$i' selected>$i</option>";
									else
											echo "<option value='$i'>$i</option>";
								}
  							echo "</select>
						   </div>
               <br/><div class='input-group mb-4'>
                <div class='input-group-prepend'>
                  <label class='input-group-text' for='NoSlides'> Total de slides </label>
                </div>
                <input class='form-control' type='text' id='NoSlides' readonly>
              </div>
					  </div>
					  <div class='col'>
						  <p align='right'> <font class='text-muted'> X </font></p>
						    <table style='border:hidden' class='table table-bordered' id='casillas'>";
        				for($i=1; $i<=10; $i++)
								{
        					echo "<tr>";
        					for($j=1; $j<=5; $j++)
									{
        						echo "<td></td>";
        					}
        				echo "</tr>";
							}
        			echo "</table> </td>
					   <p align='left'> <font class='text-muted'> Y </font></p>
					</div>
				</div>
		</form>";
		echo "<button class='update-db-submit' id='guardarejilla' hidden> Submit </button>";

		// Actualizar datos
		if( isset($_POST['DatosRejilla']) )
		{
			$conexion = ConectarBD();
			if($conexion == false)
				echo "No se pudo establecer la conexión a la base de datos.";
			else
			{
				$Val=explode(",",$_POST['DatosRejilla']);
		    // Establece codificación
		    mysqli_set_charset($conexion,"utf8");
				$columnas = $Val[0];
				$filas = $Val[1];
				$sql = "UPDATE slide SET columnasplaca='".$columnas."',filasplaca='".$filas."' WHERE ID = '".$_SESSION['ID']."'";
		    $res = mysqli_query($conexion, $sql);
			mysqli_close($conexion);
			}
		}
	}
	session_write_close();
?>
