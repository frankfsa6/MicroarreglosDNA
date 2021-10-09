<script type="text/javascript" src="js/chips.js?v=14" ></script>
<link rel="stylesheet" href="css/chips.css?v=5">
<?php
  echo "
    <h2 style='text-align:center'> Rutina específica para chips múltiples </h2></br>
    <h4>Configuración predeterminada de pines</h4><hr>
    <div class='container'>
      <div class='row'>
        <div class='col-5'>
          <div class='input-group mb-3'>
            <label for='TipoPin' class='input-group-text'>Tipo de pin</label>
            <select class='custom-select' id='TipoPin'>
              <option value='2' selected>Acero</option>
            <option value='1'>Cerámico</option>
            </select>
          </div>
          <div class='input-group mb-3'>
            <label  class='input-group-text'>¿Duplicar serie? </label>
            <select class='custom-select' id='PinesXDobles'>
              <option value='0' selected>No</option>
              <option value='1'>Sí</option>
            </select>
          </div> 
          <div class='input-group mb-3'>
            <label  class='input-group-text'>Número de pines en X </label>
            <input class='form-control' type='text' value='4' id='PinesNumeros_ejeX' disabled>
            </div>
            <div class='input-group mb-3'>
            <label  class='input-group-text'>Número de pines en Y </label>
            <input class='form-control' type='text' value='2' id='PinesNumeros_ejeY' disabled>
          </div>
          </br>
          <div class='input-group mb-3'>
            <label  class='input-group-text'>Total de pines (simulados)</label>
            <input class='form-control' type='text' value='8' id='PinesNumeros_totales' disabled>
          </div>
        </div>
        <div id='tablaPines' class='col-7'> </div>
      </div>
    </div></br>";
  echo  "
      <h4>Diseño para impresión de chips</h4><hr>
      <div class='container'>
        <div class='row'>
          <div class='col-5'>
            <div class='input-group mb-3'>
              <label for='xCoords' class='input-group-text'>Coordenadas de rejilla en eje X (mm)</label>
              <input type='text' maxlength='4' class='form-control mx-sm-3' id='XCoords' value='5'>
            </div>
            <div class='input-group mb-3'>
              <label for='YCoords' class='input-group-text'>Coordenadas de rejilla en eje Y (mm)</label>
              <input type='text' maxlength='4' class='form-control mx-sm-3' id='YCoords' value='5'>
            </div>
            <hr>
            <div class='input-group mb-3'>
              <label for='XDotSpace' class='input-group-text'>Espaciado de puntos en eje X (&mu;m)</label>
              <input type='text' maxlength='4' class='form-control mx-sm-3' id='XDotsSpace' value='130'>
            </div>
            <div class='input-group mb-3'>
              <label for='YDotSpace' class='input-group-text'>Espaciado de puntos en eje Y (&mu;m)</label>
              <input type='text' maxlength='4' class='form-control mx-sm-3' id='YDotsSpace' value='130'>
            </div>
            <div class='input-group mb-3'>
              <label for='XDots' class='input-group-text'>Número de puntos por rejilla en eje X</label>
              <input type='text' maxlength='3' class='form-control mx-sm-3' id='XDots' value='25'>
            </div>
            <div class='input-group mb-3'>
              <label for='YDots' class='input-group-text'>Número de puntos por rejilla en eje Y</label>
              <input type='text' maxlength='3' class='form-control mx-sm-3' id='YDots' value='25'>
            </div>
            <hr>
            <div class='input-group mb-3'>
              <label for='DuplicateDotsY' class='input-group-text'>Puntos Y duplicados por rejilla</label>
              <select class='form-control mx-sm-3' id='DuplicateDotsY'></select>
            </div>
  					<div class='custom-control custom-switch'>
         			<input type='checkbox' class='custom-control-input' id='PlateState' checked>
  				    <label class='custom-control-label' for='PlateState'>¿Utilizar placas completas?</label>
  				  </div> </br>
            <div class='input-group mb-3'>
              <label for='NoPlates' class='input-group-text'>Total de placas a realizar</label>
              <input type='text' class='form-control' id='NoPlates' readonly>
            </div>
  			    <hr>
            <label><b>Dirección de impresión de placas:</b> izquierda-derecha y arriba-abajo</label></br>
            <label><b>Dirección de impresión de slides:</b> arriba-abajo e izquierda-derecha</label>
            </div>
            <div class='col'>
              <center><div class='btn-group btn-group-toggle' data-toggle='buttons'>
              <label class='btn btn-outline-primary'>
                <input type='radio' name='Show' id='ShowSlides' autocomplete='off'>Mostrar diseño de sólo un pin</label>
              <label class='btn btn-outline-primary active'>
                <input type='radio' name='Show' id='ShowPin' autocomplete='off' checked>Mostrar diseño del slide completo</label>
              </div></center>
              <br/>
              <div id='Figura'> </div>
            </div>
          </div>
        </div>
      </div>";

  echo "</br>     
      <h4>Número de slides a utilizar</h4><hr>
      <div class='container'>
        <div class='row'>
          <div class='col-5'>
            <div class='input-group mb-3'>
            <label  class='input-group-text'>Número de slides en X </label>
            <select class='custom-select' id='xSlidesNumeros'>";
            for($i = 1; $i <6; $i ++)
              echo "<option value = '".$i."'>".$i."</option>";

  echo "    </select>
            </div>
            <div class='input-group mb-3'>
            <label  class='input-group-text'>Número de slides en Y </label>
            <select class='custom-select' id='ySlidesNumeros'>";
              for($i = 1; $i <11; $i ++){
                if($i == 5)
                  echo "<option value = '".$i."' selected>".$i."</option>";
                else
                  echo "<option value = '".$i."'>".$i."</option>";
              }

  echo "   </select>
          </div>
          </br>
          <div class='input-group mb-3'>
            <label  class='input-group-text'>Total de slides</label>
            <input class='form-control' type='text' value='5' id='SlidesNumeros_totales' disabled>";

  echo "</div>
        </div>
        <div class='col-7'>
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
        </div>";
?>
