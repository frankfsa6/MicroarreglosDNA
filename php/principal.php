<?php
	session_name("IFCLab");
	session_start();
	include ("bd.php");
// Si se encuentra definida la rutina, obtiene datos
	if( isset( $_SESSION['ID'] ) ){
		//se obtiene el ID para poder trabajar con la base de datos
		$rutina = $_SESSION['ID'];
		//Los datos se guardaran en un arreglo
		//Se realiza la peticion a la base de datos para iniciar la comparacion de los usuarios
		$conexion = ConectarBD();
		//Se verifica que la conexion sea exitosa
		if($conexion != false){
			mysqli_error($conexion);
			// Comienza con nombre de rutina
			$query = mysqli_query($conexion,"SELECT Temporal,nombreRutina FROM rutinas WHERE ID='".$rutina."'");
			//Los datos se guardan en una matriz $resDB[][0]
			$resDB=null;
			while ($row = mysqli_fetch_row($query))
				$resDB[]=$row;
			if($resDB == null || $resDB[0][0]=="1")
				$resDB[0][1]='sin nombre asignado';
			//Se da formato que aparecerá en pantalla y crea tabla
			echo "<h4 id='rutAct'>Rutina actual:  ".$resDB[0][1]."</h4>";
			echo "<table class='table table-hover'> <tbody>";
			$resDB=null;
			//Pines-----------------------------------------------
			//Pines regresa 3 columnas PinesX y pinesY
			$query = mysqli_query($conexion,"SELECT pinesx, pinesy FROM pines WHERE ID='".$rutina."'");
			//Los datos se guardan en una matriz $resDB[][0]
			$resDB=null;
			while ($row = mysqli_fetch_row($query))
				$resDB[]=$row;
			if($resDB == null)
			{
				$longDB = 2;
				for($contadorEspacios = 0 ; $contadorEspacios < $longDB ; $contadorEspacios++)
				{
					$resDB[0][$contadorEspacios]='No se ha ingresado información en este campo';
				}
			}
			echo "<tr> <th>Pines</th>";
			echo "<td scope='row'> Dirección en X </br>
				 Dirección en Y </br>
				 Tipo de Pin </td>";
			echo "<td>".$resDB[0][0]." </br>".$resDB[0][1]." </br> ";
			$query = mysqli_query($conexion,"SELECT IDPin FROM pines WHERE ID='".$rutina."'");
			$resDB=null;
			while ($row = mysqli_fetch_row($query))
				$resDB[]=$row;
			if($resDB != null){
				if($resDB[0][0] == 1)
					echo "Cerámico";
				elseif($resDB[0][0] == 2)
					echo "Acero";
			}
			echo "</td></tr>";
			$resDB=null;
			//Reticula----------------------------------------------
			//Se pide en reticula.php XCoords 	YCoords XSpace YSpace XDots YDots DuplicateDots PlateState
			$query = mysqli_query($conexion,"SELECT YCoords,YCoords, XSpace, YSpace, XDots, YDots, DuplicateDots, PlateState, TotalPlates FROM reticula WHERE ID='".$rutina."'");
			while ($row = mysqli_fetch_row($query))
				$resDB[]=$row;
			if($resDB == null)
			{
				$longDB = 9;
				for($contadorEspacios = 0 ; $contadorEspacios <= $longDB ; $contadorEspacios++)
					$resDB[0][$contadorEspacios]='No se ha ingresado información en este campo';
			}
			echo "<tr> <th>Retícula</th>";
			echo "<td scope='row'> Coordenadas en X </br>
				Coordenadas en Y </br>
				Espacio en X </br>
				Espacio en Y </br>
				Puntos en X </br>
				Puntos en Y </br>
				Puntos duplicados </br>
				Placas completas </br>
				Número de placas </br> </td>";
			echo "<td> ".$resDB[0][0]." </br>
				".$resDB[0][1]." </br>
				".$resDB[0][2]." </br>
				".$resDB[0][3]." </br>
				".$resDB[0][4]." </br>
				".$resDB[0][5]." </br>
				".$resDB[0][6]." </br>";
				if($resDB[0][7] == '0')
					echo "No";
				else
					if($resDB[0][7] == '1')
						echo "Sí";
					else
						echo $resDB[0][7];
			echo " </br>".$resDB[0][8]."</br></td></tr>";
			$resDB=null;
			//Slide-----------------------------------------------
			//Se pide en ?.php Columnas Filas
			$query = mysqli_query($conexion,"SELECT columnasplaca, filasplaca FROM slide WHERE ID='".$rutina."'");
			while ($row = mysqli_fetch_row($query))
				$resDB[]=$row;
			if($resDB == null)
			{
				$longDB = 2;
				for($contadorEspacios = 0 ; $contadorEspacios < $longDB ; $contadorEspacios++)
				{
					$resDB[0][$contadorEspacios]='No se ha ingresado información en este campo';
				}
			}
			echo "<tr> <th>Slide</th>";
			echo "<td scope='row'> Número de columnas </br>
				Número de filas </td>";
			echo "<td>".$resDB[0][0]." </br>
				".$resDB[0][1]." </td>";
			echo "</tr>";
			$resDB=null;
			//Lavado----------------------------------------------
			//Se pide en lavado.php ciclos 	oscilaciones toques vacio uvacio tmuestra
			$query=mysqli_query($conexion,"SELECT ciclos, oscilaciones,vacio, uvacio, toques, tmuestra  FROM lavado WHERE ID='".$rutina."'");
			while ($row=mysqli_fetch_row($query))
				$resDB[]=$row;
			if($resDB == null)
			{
				$longDB = 6;
				for($contadorEspacios = 0 ; $contadorEspacios < $longDB ; $contadorEspacios++)
				{
					$resDB[0][$contadorEspacios]='No se ha ingresado información en este campo';
				}
			}
			echo "<tr> <th>Lavado/Limpieza</th>";
			echo "<td scope='row'> Número de ciclos </br>
				Número de oscilaciones </br>
				Tiempo de vacío </br>
				Tiempo de último vacío </br>
				Número de toques de limpieza </br>
				Tiempo de toma de muestra </br></td>";
			echo "<td> ".$resDB[0][0]." </br>
				".$resDB[0][1]." </br>
				".$resDB[0][2]." </br>
				".$resDB[0][3]." </br>
				".$resDB[0][4]." </br>
				".$resDB[0][5]." </br> </td>";
			echo "</tr>";
			$resDB=null;
			//Nombre y estado de la rutina (guardada o no)-------------------------
			//Solo se solicita el temporal y se notifica en pantalla
			$query = mysqli_query($conexion,"SELECT Temporal FROM rutinas WHERE ID='".$rutina."'");
			while ($row = mysqli_fetch_row($query))
				$resDB[]=$row;
			echo "<tr><th>Estado de rutina</th>";
			echo "<td scope='row'> ¿Guardada en base de datos? </td><td id='rutinaGuardadaTexto'>";
			if($resDB[0][0] == "1")
				echo "No";
			else
				echo "Sí";
			echo "</td></tr>";

			unset($resDB);
			//Se termina la lista que agrupa a las diferentes categorias
			echo "</tbody> </table> </center>";
		}
		else
		{
			echo "<div class='Error'>Hubo un error al conectar con la base de datos</div>";
		}
		mysqli_close($conexion);
	}
	//Cuando la sesión no esta activa, envía un mensaje de advertencia
	else
		echo "<div class='alert alert-info' role='alert' style='width:90%; text-align:center; margin:0 auto'> Los datos recopilados se encuentran aquí una vez presionado el botón de <strong>Nueva rutina<strong> </div>";
	session_write_close();
?>
