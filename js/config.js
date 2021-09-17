// Variable que evitar perder datos 
var anteVal;
// Inhabilita opciones del selector en los pines para no repetir en varios campos
function InhabPines(){
  $(".ejes option:disabled").each(function(){
    $(this).prop("disabled", false);
  });
  $(".ejes option:selected").each(function(){
    $(".ejes option[value="+$(this).val()+"]").each(function(){
      $(this).prop("disabled", true);
    });
  });
}
// Manda valores calculados a pasos de motores por mm
function pasosMM(){
  var ejes = ["X","Y","Z"];
  const regex = /[^0-9.]/;
  // Manda error si no cumple características
  if( regex.test($(this).val()) || $(this).val()=='' || $(this).val()<=0 ){
    $("#error").text("Sólo se aceptan formatos numéricos mayores a 0").show();
    $(this).val(anteVal);
    document.documentElement.scrollTop = 0;
  }
  else{
    $("#error").empty().hide();
    for(var i=0; i<3; i++)
      $("#pasosmm"+ejes[i]).val( (Number($("#pasosRev"+ejes[i]).val())/Number($("#tor"+ejes[i]).val())).toFixed(6) );
  }
}
// Valida las coordenadas introducidas
function valNums(){
  var temp = parseFloat($(this).val());
  // Manda error si no cumple características
  if(temp>1000 || !$.isNumeric(temp)){
    $("#error").text("Sólo se aceptan dimensiones menores a 1 metro").show();
    $(this).val(anteVal);
    document.documentElement.scrollTop = 0;
  }
  else{
    $("#error").empty().hide();
    $(this).val( temp );
  }
}
// Actualiza datos y biblioteca de movimientos
function guardarCfg(){
  // Pone velo oscuro
  $("#espera").css({"width":"100%","height":"100%","cursor":"wait"});
  var t1 = new Array(), t2 = new Array(), i = 0;
  var tipoPin = $("#PinButton").children().last().children().attr("id");
  console.log(tipoPin);
  // Obtiene valores de tabla 1 con coordenadas: "Lugar;X;Y;Z"
  $("#t1 tbody").children("tr").each(function(){
    t1[i] = $(this).children("td").first().text();
    var actual = $(this).children("td").first();
    for(var j=0; j<3; j++){
      actual = actual.next();
      t1[i]+=";"+actual.children("input").val();
    }
    i++;
  });
  // Obtiene valores de tabla 2 con pines: "Id;pinGPIO"
  i = 0;
  $(".ejes").each(function(){
    t2[i] = $(this).attr("id")+";"+$(this).find('option:selected').val();
    i++;
  });
  // Obtiene valores de tabla 3 con motores: "Id;valorNuevo"
  $(".selMot").each(function(){
    t2[i] = $(this).attr("id")+";"+$(this).val();
    i++;
  });
  // Manda datos para actualizar base 
  $.ajax({
		type: 'POST',
		url:'php/LSArch.php',
		data: {uDBConfig:true,"tipoPin":tipoPin,"config":t1, "raspberry":t2}
	}).done(function(){
    $("#info").html("Actualizando nueva información en la base de datos").show();
    document.documentElement.scrollTop = 0;
    //Actualiza el archivo pinesRasp.h
    $.ajax({
      type: 'POST',
      url:'php/LSArch.php',
      data: {actualizaPines:true}
    }).done(function(){
      // Termina cerrando sesión y saliendo de configuración
      $("#salirConfig").trigger("click");
    });
  }).fail(function(){
    $("#error").text("Falló actualización de datos").show();
  });
}
// Oprime botón sin guardar datos
function salirCfg(){
  $("#espera").css({"width":"100%","height":"100%","cursor":"wait"});
  // Borra la sesión
  $.ajax({
    type: 'POST',
    url:'php/LSArch.php',
    data: {unsetLogin:true}
  }).done(function(){
    // Pantalla principal para pedir contraseña
    $.ajax({
      type: 'POST',
      url:'php/login.php',
    }).done(function(datos){
      $("#error").empty().hide();
      $("#pags").html(datos);
      $("#espera").css({"width":"0%","height":"0%","cursor":"auto"});
    }).fail(function(){
      $("#info, #pags, #botones").empty()
      $("#info").hide();
      $("#error").text("No se completó la petición de página").show();
    });
  });
}

//Funcion que cambia las coordenadas de acuerdo al tipo de pin
function cambiaCoord(){
  var tipoPin = this.id;
  $.ajax({
    type: 'POST',
    url:'php/config.php',
    data:{ cambiaPin: tipoPin},
  }).done(function(datos){
    $("#info").empty().hide();
    $("#error").empty().hide();
    $("#pags").html(datos);
  }).fail(function(){
    $("#info, #pags, #botones").empty();
    $("#info").hide();
    $("#error").text("No se completó la petición de página").show();
  });
}

// Comienza a cargar funciones principales
$(document).ready( function(){
  // Limpia mensajes y aplica filtro
  $("#error, #botones, #info").empty().hide();
  $("#oriX, #oriY").attr("disabled","true");
  $("#tipoPin").on("click","button",cambiaCoord);
  // Detonadas al iniciar o cambiar su valor
  $(".ejes").ready(InhabPines);
  $(".ejes").on("change",InhabPines);
  $(".selMot").ready(function(){
    var ejes = ["X","Y","Z"];
    for(var i=0; i<3; i++)
      $("#pasosmm"+ejes[i]).val( (Number($("#pasosRev"+ejes[i]).val())/Number($("#tor"+ejes[i]).val())).toFixed(6) );
  });
  $(".selMot").on("change",pasosMM);
  $(".selMot").on("click", function(){
    anteVal = $(this).val();
  });
  // Dispara validación de entrada de datos
  $(".nums").on("change", valNums); 
  $(".nums").on("click", function(){
    anteVal = $(this).val();
  });
  // Sale sin guardar o actualiza datos
  $("#salirConfig").on("click",salirCfg);
  $("#guardaConfig").on("click", guardarCfg);
});
