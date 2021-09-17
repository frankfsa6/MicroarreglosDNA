<script type="text/javascript" src="js/numeros.js?v=3" ></script>
<link rel="stylesheet" href="css/numeros.css?v=3">
<?php
  echo "<h2 style='text-align:center'> Rutina específica para numeración</h2></br>
          <h4>Configuración predeterminada de pines</h4><hr>
          <div class='container'><div class='row'>
            <div class='col-5'>
              <div class='input-group mb-3'>
                <label for='TipoPin' class='input-group-text'>Tipo de pin</label>
                <select class='custom-select' id='TipoPin'>
                  <option value='2' selected>Acero</option>
                <option value='1'>Cerámico</option>
                </select>
              </div>
              <div class='input-group mb-3'>
                <label  class='input-group-text'>Número de pines en X </label>
                <input class='form-control' type='text' value='6' disabled>
              </div>
              <div class='input-group mb-3'>
                <label  class='input-group-text'>Número de pines en Y </label>
                <input class='form-control' type='text' value='1' disabled>
              </div>
              <div class='input-group mb-3'>
                <label class='input-group-text'>Total de pines / series a realizar </label>
                <input class='form-control' type='text' value='6' disabled>
              </div>
            </div>
            <div id='tablaPines' class='col-7'> </div>
    			</div></div>";
  echo  "<h4>Referencia de la series numéricas</h4><hr>
          <div class='container'><div class='row'>
            <div class='col-5'>            
              <div class='input-group mb-3'>
                <label class='input-group-text'>Sección</label>
                <select class='custom-select' id='seccionNum'>
                  <option value='0'>Superior</option>
                  <option value='1'>Inferior (vidrios / slides girados 180°)</option>
                </select>
              </div>
              <div class='input-group mb-3'>
                <label class='input-group-text'>Coordenada X (mm)</label><input class='form-control coordsXY' type='text' id='coordXNums' value='4.5'>
              </div>
              <div class='input-group mb-3'>
                <label class='input-group-text'>Coordenada Y (mm)</label><input class='form-control coordsXY' type='text' id='coordYNums' value='6.5'>
              </div>
            </div>
            <div id='tablaCoordenadas' class='col-7'> </div> 
          </div></div>";
  echo "<h4>Número de slides a utilizar</h4><hr>
        <div class='container'><div class='row'>
        <div class='col-5'>  
          <div class='input-group mb-3'>
            <label  class='input-group-text'>Número de slides en X</label>
            <select class='custom-select' id='xSlidesNumeros'>";
              for($i = 1; $i <6; $i ++)
                echo "<option value = '".$i."'>".$i."</option>";
  echo "    </select>
          </div><div class='input-group mb-3'>
            <label  class='input-group-text'>Número de slides en Y</label>
            <select class='custom-select' id='ySlidesNumeros'>";
              for($i = 1; $i <11; $i ++){
                if($i == 5)
                  echo "<option value = '".$i."' selected>".$i."</option>";
                else
                  echo "<option value = '".$i."'>".$i."</option>";
              }
  echo "    </select>
          </div><div class='input-group mb-3'>
            <label class='input-group-text'>Total de slides</label><input class='form-control' type='text' id='slidesTot' disabled>
          </div>
        </div>
        <div class='col-7'> 
          <p align='right'> <font class='text-muted'> X </font></p>
            <table style='border:hidden' class='table table-bordered' id='casillas'>";
            for($i=1; $i<=10; $i++){
              echo "<tr>";
              for($j=1; $j<=5; $j++)
                echo "<td></td>";
              echo "</tr>";
            }
            echo "</table> </td>
          <p align='left'> <font class='text-muted'> Y </font></p>
        </div>
      </div></div>";
?>
