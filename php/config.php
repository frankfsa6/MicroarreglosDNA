<script type="text/javascript" src="js/config.js?v=3" ></script>
<?php
  include("bd.php");
  // Parámetros iniciales
  $GPIORasp = [2,3,4,5,6,7,8,9,10,11,12,13,16,17,18,19,20,21,22,23,24,25,26,27];
  $diamTorn = [ 2.5, 3, 3.5, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 20, 22, 24, 25, 26, 27, 28, 30];
  $ejes = ["X", "Y", "Z"];
  // Obtiene datos de base o asigna genéricos
  $conexion = ConectarBD();
  if($conexion == false)
    echo "No se pudo establecer la conexión a la base de datos.";
  else{
    // Pide datos de base
    mysqli_set_charset($conexion,"utf8");
    // Pide datos de coordenadas y valores principales
    $sql = "SELECT * FROM config";
    if ( mysqli_query($conexion, $sql)->num_rows !=0 ) {
      $res = mysqli_query($conexion, $sql);
      while ( $dato = mysqli_fetch_assoc($res) )
        $coords[$dato['nombre']] = [$dato["x"], $dato["y"], $dato["z"]];
      mysqli_free_result($res);
    }
    // Pide datos de pines y motores
    $sql = "SELECT * FROM raspberry";
    if ( mysqli_query($conexion, $sql)->num_rows !=0 ) {
      $res = mysqli_query($conexion, $sql);
      while ( $dato = mysqli_fetch_assoc($res) ){
        if( $dato['tipo'] == "gpio" )
          $pinActual[$dato['id']] = [$dato['nombre'], $dato['valor']];
        else
          $motActual[$dato['id']] = [$dato['nombre'], $dato['valor']];
      }
      mysqli_free_result($res);
    }
    mysqli_close($conexion);
    // Obtiene datos de JS mientras no se oprima botón de guardar info
    if( !isset($_POST['guarda']) ){
      // Velocidad y altura
      echo "<h5>Coordenadas y valores asociados.</h5>
        <h5 class='text-muted'>Las unidades están dadas en milímetros (únicamente valores positivos), tomando 
        como referencia absoluta al origen (ubicado a la <b>izquierda</b> en eje X, <b>atrás</b> en eje Y, <b>arriba</b> en eje Z). </h5><br/>";
      echo "<div class='table-responsive'><table class='table table-hover .table-responsive' id='t1'>
        <thead><tr>
          <th class='thead-light'>Lugares principales</th>
          <th class='thead-light'>Coordenada X</th>
          <th class='thead-light'>Coordenada Y</th>
          <th class='thead-light'>Coordenada Z</th>
        </tr></thead><tbody>";
      foreach($coords as $unidad=>$valor){
        echo "<tr><td>".$unidad."</td>";
        for($i=0; $i<3; $i++)
          echo "<td><input type='text' class='nums form-control' id='".strtolower(substr($unidad,0,3)).$ejes[$i]."' value=".round($valor[$i], 3)."></td>";
      }
      echo "</tr></tbody></table>";
      // Manda encabezado de pines
      echo "<h5>Pines configurados en Raspberry.</h5><h5 class='text-muted'>Utilizar con borneras acopladas y conectadas a controladores. Pines disponibles y no repetitivos.</h5><br/>";
      // Inicio de tabla y encabezados de columnas
      echo "<div class='table-responsive'><table class='table table-hover .table-responsive' id='t2'>
        <thead><tr>
          <th class='thead-light'>Dispositivo asociado</th>
          <th class='thead-light'>Señal de activación</th>
          <th class='thead-light'>Señal de dirección</th>
          <th class='thead-light'>Sensor de límite</th>
        </tr></thead><tbody>";
      // Manda info de 3 ejes
      for($i=0; $i<3; $i++){
        // Primera columna con nombres de motores en los ejes
        echo "<tr><td>Motor en eje ".$ejes[$i]."</td>";
        // Segunda columna con lista de pines en pulsos
        echo "<td><div class='input-group mb-3'>
          <div class='input-group-prepend'>
            <label class='input-group-text' for='pul".$ejes[$i]."'> Pin GPIO </label>
          </div>
          <select class='ejes custom-select' id='pul".$ejes[$i]."'>";
        foreach ($GPIORasp as $pin){
          if($pin == $pinActual["pul".$ejes[$i]][1])
            echo "<option value='".$pin."' selected>".$pin."</option>";
          else
            echo "<option value='".$pin."'>".$pin."</option>";
        }
        echo "</select></div></td>";
        // Tercer columna con lista de pines en direcciones
        echo "<td><div class='input-group mb-3'>
          <div class='input-group-prepend'>
            <label class='input-group-text' for='dir".$ejes[$i]."'> Pin GPIO </label>
          </div>
          <select class='ejes custom-select' id='dir".$ejes[$i]."'>";
        foreach ($GPIORasp as $pin){
          if($pin == $pinActual["dir".$ejes[$i]][1])
            echo "<option value='".$pin."' selected>".$pin."</option>";
          else
            echo "<option value='".$pin."'>".$pin."</option>";
        }
        echo "</select></div></td>";
        // Cuarta columna con lista de sensores de límites en direcciones
        echo "<td><div class='input-group mb-3'>
          <div class='input-group-prepend'>
            <label class='input-group-text' for='lim".$ejes[$i]."'> Pin GPIO </label>
          </div>
          <select class='ejes custom-select' id='lim".$ejes[$i]."'>";
        foreach ($GPIORasp as $pin){
          if($pin == $pinActual["lim".$ejes[$i]][1])
            echo "<option value='".$pin."' selected>".$pin."</option>";
          else
            echo "<option value='".$pin."'>".$pin."</option>";
        }
        echo "</select></div></td></tr>";
      }
      // Genera seleccionador para demás campos, separando los pines de pulso y dirección
      foreach($pinActual as $listaGPIO=>$datoGPIO){
        if( substr($listaGPIO,0,3) != "pul" && substr($listaGPIO,0,3) != "dir" && substr($listaGPIO,0,3) != "lim" ){
          // Manda select
          echo "<tr><td>".$datoGPIO[0]."</td><td colspan='3'>
          <div class='input-group mb-3'><div class='input-group-prepend'>
            <label class='input-group-text' for=".$listaGPIO."> Pin GPIO </label>
          </div><select class='ejes custom-select' id=".$listaGPIO.">";
          foreach ($GPIORasp as $pin){
            if($pin == $datoGPIO[1])
              echo "<option value='".$pin."' selected>".$pin."</option>";
            else
              echo "<option value='".$pin."'>".$pin."</option>";
          }
          echo "</select></div></td></tr>";
        }
      }
      echo "</tbody></table></div>";
      // Configuración para micropasos de motores
      echo "<h5>Resolución de pasos para motores.</h5>
        <h5 class='text-muted'>Revisar la configuración física en controladores dada por la hoja de especificaciones del fabricante.</h5><br/>";
        // Inicio de tabla y encabezados de columnas
      echo "<div class='table-responsive'><table class='table table-hover .table-responsive' id='t3'>
        <thead><tr>
          <th class='thead-light'>Motor asociado</th>
          <th class='thead-light'>Pasos configurados</th>
          <th class='thead-light'>Diámetro exterior del tornillo</th>
          <th class='thead-light'>Pasos por milímetro calculados</th>
        </tr></thead><tbody><tr>";
      // Realiza para los tres ejes
      for($i=0; $i<3; $i++){
        // Primera columna
        echo "<td>Motor en eje ".$ejes[$i]."</td>";
        // Segunda columna con lista de pasos
        echo "<td class='pasos'><div class='input-group mb-3'> <div class='input-group-prepend'>
          <label class='input-group-text' for='pasosRev".$ejes[$i]."'> ".$motActual['pasosRev'.$ejes[$i]][0]." </label>
          </div> <input type='text' class='form-control selMot' id='pasosRev".$ejes[$i]."' value='".$motActual['pasosRev'.$ejes[$i]][1]."'/>
        </div></td>";
        // Tercera columna con lista de diámetros de tornillo
        echo "<td class='tornillo'><div class='input-group mb-3'> <div class='input-group-prepend'>
            <label class='input-group-text' for='tor".$ejes[$i]."'> ".$motActual['tor'.$ejes[$i]][0]." </label>
          </div> <input type='text' class='form-control selMot' id='tor".$ejes[$i]."' value='".$motActual['tor'.$ejes[$i]][1]."'/>
        </div></td>";  
        // Cuarta columna con cuenta
        echo "<td><input type='text' class='form-control' id='pasosmm".$ejes[$i]."' disabled></td></tr>";
      }
      echo "</tbody></table></div> </div>";
      echo "<center><button type='button' class='btn btn-danger btn-lg' id='salirConfig'>Salir sin guardar cambios</button>
          <button type='button' class='btn btn-success btn-lg' id='guardaConfig'>Guardar cambios y salir</button></center>";
    }
  }
?>
