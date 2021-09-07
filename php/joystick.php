<link rel="stylesheet" href="css/joystick.css?v=3">
<script type="text/javascript" src="js/joystick.js?v=3" ></script>
<?php
  // Escribe joystick de imágenes
  echo "<div class='container'><div class='row'><div class='col'>
  <table><tbody><tr>
    <td class='parofin' title='Paro total'><img id='parofin' src='img/paro.svg'></td>
    <td>&nbsp</td>
    <td class='algo d' title='Arriba'><img id='z0' src='img/dir.svg'></td>
    <td>&nbsp</td>
    <td class='algo d' title='Abajo'><img id='z1' src='img/dir.svg'></td>
  </tr><tr height='30px'></tr><tr>
    <td class='vel c' title='Velocidad rápida'><img id='v1' src='img/v1.svg'></td>
    <td>&nbsp</td>
    <td class='algo2 d' title='Diagonal izquierda atrás'><img id='d00' src='img/dir0.svg'></td>
    <td class='algo d' title='Atrás'><img id='y0' src='img/dir0.svg'></td>
    <td class='algo2 d' title='Diagonal derecha atrás'><img id='d10' src='img/dir0.svg'></td>
  </tr><tr>
    <td class='vel u' title='Velocidad media'><img id='v2' src='img/v2.svg'></td>
    <td>&nbsp</td>
    <td class='algo d' title='Izquierda'><img id='x0' src='img/dir0.svg'></td>
    <td class='d' id='o' title='Sensar origen'><img src='img/casa.svg'></td>
    <td class='algo d' title='Derecha'><img id='x1' src='img/dir0.svg'></td>
  </tr><tr>
    <td class='vel d dd' title='Velocidad lenta'><img id='v3' src='img/v3.svg'></td>
    <td>&nbsp</td>
    <td class='algo2 d' title='Diagonal izquierda adelante'><img id='d01' src='img/dir0.svg'></td>
    <td class='algo d' title='Adelante'><img id='y1' src='img/dir0.svg'></td>
    <td class='algo2 d' title='Diagonal derecha adelante'><img id='d11' src='img/dir0.svg'></td>
  </tr></tbody></table></div>";
  // Escribe campos de texto con valores
  echo "<div class='col'>
    <label for='pasosx'>Pasos y distancia en eje X</label>
    <div class='input-group mb-2'>
      <input class='form-control' id='pasosx' type='text' value='0' disabled>
      <div class='input-group-prepend'><div class='input-group-text'>
        <span id='mmx'>0</span> &nbsp; mm &nbsp;( &nbsp;
        <span id='xpasosmm'></span> &nbsp; pasos / mm ) 
      </div></div>
    </div>
    <label for='pasosy'>Pasos y distancia en eje Y</label>
    <div class='input-group mb-2'>
      <input class='form-control' id='pasosy' type='text' value='0' disabled>
      <div class='input-group-prepend'><div class='input-group-text'>
        <span id='mmy'>0</span> &nbsp; mm &nbsp;( &nbsp;
        <span id='ypasosmm'></span> &nbsp; pasos / mm ) 
      </div></div>
    </div>
    <label for='pasosz'>Pasos y distancia en eje Z</label>
    <div class='input-group mb-2'>
      <input class='form-control' id='pasosz' type='text' value='0' disabled>
      <div class='input-group-prepend'><div class='input-group-text'>
        <span id='mmz'>0</span> &nbsp; mm &nbsp;( &nbsp;
        <span id='zpasosmm'></span> &nbsp; pasos / mm ) 
      </div></div>
    </div>";
    // Escribe lugares de prueba
    echo "<label for='prueba'>Prueba de conexiones en el sistema</label></br>
      <button type='button' class='btn btn-outline-secondary prueba'>Sensor X</button>
      <button type='button' class='btn btn-outline-secondary prueba'>Sensor Y</button>
      <button type='button' class='btn btn-outline-secondary prueba'>Sensor Z</button>
      <button type='button' class='btn btn-outline-secondary prueba'>Botón de emergencia</button>
      <button type='button' class='btn btn-outline-secondary prueba'>Bomba de agua</button>
      <button type='button' class='btn btn-outline-secondary prueba'>Bomba de vacío</button>
	  </br></br>";
    // Escribe botones de lugares
    echo "<label for='lugares'>Movimiento a lugares predeterminados</label></br>
      <button type='button' id='definir' class='btn btn-dark'>Definir lugar predeterminado</button>
      <button type='button' class='btn btn-outline-dark lugares'>Usuario</button>
      <button type='button' class='btn btn-outline-dark lugares'>Vacío</button>
      <button type='button' class='btn btn-outline-dark lugares'>Lavado</button>
      <button type='button' class='btn btn-outline-dark lugares'>Limpieza</button>
      <button type='button' class='btn btn-outline-dark lugares'>Muestra</button>
      <button type='button' class='btn btn-outline-dark lugares'>Retícula</button>
	  </br></br>
   </div></div></div>";
?>
