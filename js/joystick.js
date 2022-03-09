// Velocidad, pasosMM, ejes, paro activado, evento pingu y botón presionado
var vel, pasosMM, ejes = ["x","y","z"], joysActivo = 0, pingus, bot, parof = 0;
// Función que habilita o deshabilita funcionalidad de joystick completo
function jAlgoFunciona(func){
  // Habilita todo
  if( func ){
    // Velocidad y botones de lugares
    $(".vel").on("click", jVel);
    $(".lugares").on("click", jLugares);
    $(".lugares").prop("disabled", false);
    // Botones de prueba de electrónica
    $(".prueba").on("click", jPruebas);
    $(".prueba").prop("disabled", false);
    // Ventana emergente para definir y casita
    $("#definir").on("click", popCalibra);
    $("#o").on("click", jCalibra);
    $("#calibrar").on("click","#oo", jCalibra);
    // Presionar y soltar botón de ratón en flechas
    $(".algo").on("mousedown", jBotones);
    $(".algo2").on("mousedown", jBotonesDual);
    $(".algo, .algo2").on("mouseup", paroFin);
  }
  // Deshabilita todo
  else{
    $(".vel, .lugares, .prueba, .algo, algo2, #definir, #calibrar, #o").off();
    $(".lugares, .prueba").prop("disabled", true);
  }
}
// Establece o quita función intermitente pingu
function pinguParpadea(ini){
  if(ini == 1){
    pingus = setInterval(function(){
      // Al llegar a 2 segundos (4 pingus), reinicia cuenta
      if( $("#pingu").children().length >= 4)
        $("#pingu").empty();
      $("#pingu").append("<img src='img/pingu.svg' width=25px'>");
    },500);
  }
  // Apaga intermitente
  else
    clearInterval(pingus);
}
// Función que cambia velocidad y colores asociados
function jVel(){
  // Actualiza valor de velocidad y quita colores
  vel = $(this).children().attr("id").replace("v","");
  $(".vel").removeClass("cc uu dd");
  // Asigna nuevos colores
  var color = $(this).attr("class").replace("vel ","");
  $(".algo, .algo2, #o").removeClass("c u d").addClass(color);
  $(this).addClass(color+color);
  // Mensaje en informativo
  color = $(this).attr("title");
  $("#info").html("<b>"+color+"</b> seleccionada (no aplica para sensar origen ni lugares conocidos)").show();
}
// Finaliza cualquier movimiento pendiente
function paroFin(){
  // Entra única vez
  if(parof == 0){
    parof = 1;
    jAlgoFunciona(false);
    $.ajax({
      type: 'POST',
      url:"php/joysFlechas.php",
      data:{"fin":true}
    }).done(function(){
      // Habilita sistema y desocupa joystick
      $("#info").html("Movimientos y acciones pendientes finalizadas").show();
      jAlgoFunciona(true);  
      joysActivo = 0;
      parof = 0;
    }).fail(function(){
      // En caso de no ejecutar el fin de movimientos
      $("#info").html("No se pudieron finalizar los procesos pendientes").show();  
      $("#error").html("Por seguridad, desconecte la energía de los motores").show();
      jAlgoFunciona(false);  
      parof = 0;
    });
  }
}
// Mueve joystick según dirección dada
function jBotones(){
  // Obtiene botón presionado (eje-dir-mmActual)
  bot = $(this).children().attr("id").split("");
  // Checa que no vaya más allá del origen cuando dirección es cero
  if(bot[1] == "0" && parseFloat( $("#mm"+bot[0]).text() ) <= 0 ){
    bot = false;
    $("#info").html("No se pueden dar más pasos en dirección al origen"); 
  }  
  else
    bot = bot[0]+","+bot[1]+","+$("#mm"+bot[0]).text();
  // Evita ajax si no es posible mover motores o está ocupado
  if( bot != false && joysActivo == 0 ){
    $("#info").html("<b> Eje "+bot[0]+"</b> en movimiento");
    joysActivo = 1;
    console.log("Botoncito:"+bot+"/Vel:"+vel);
    // Llamada ajax comienza motores
      $.ajax({
        type: 'POST',
        url:"php/joysFlechas.php",
        data: {"bot":bot, "vel":vel}
      }).done(function(mmAct){
      // Coordenadas finales en ejes
        $("#pasos"+bot[0]).val( Number(mmAct*pasosMM[ejes.indexOf(bot[0])]).toFixed(0) );
        $("#mm"+bot[0]).text(mmAct);
      // Error al conectar ajax desactiva todo
      }).fail(function(){
        $("#error").text("Ocurrió un error conectando a motores").show();
        jAlgoFunciona(false);
      });
  }
}
// Mueve joystick en diagonal según dirección dada
function jBotonesDual(){
  // Obtiene botón presionado (eje-X-Y-mmActualX-mmActualY)
  bot = $(this).children().attr("id").split("");
  // Checa que no vaya más allá del origen cuando dirección es cero
  if( (bot[1] == "0" && parseFloat($("#mmx").text()) <= 0) || (bot[2] == "0" && parseFloat($("#mmy").text()) <= 0)  ){
    bot = false;
    $("#info").html("No se pueden dar más pasos en dirección al origen"); 
  }  
  else
    bot = bot[1]+","+bot[2]+","+$("#mmx").text()+","+$("#mmy").text();
  // Evita ajax si no es posible mover motores o está ocupado
  if( bot != false && joysActivo == 0 ){
    $("#info").html("<b>Ejes xy</b> en movimiento diagonal");
    joysActivo = 1;
    console.log("Diagonal:"+bot+"/Vel:"+vel);
    // Llamada ajax comienza motores diagonal
    $.ajax({
      type: 'POST',
      url:"php/joysFlechas.php",
      data: {"diag":bot, "vel":vel}
    }).done(function(mmAct){
    // Coordenadas finales en ejes XY
      mmAct = mmAct.split(",");
      $("#pasosx").val( Number(mmAct[0]*pasosMM[0]).toFixed(0) );
      $("#mmx").text(mmAct[0]);
      $("#pasosy").val( Number(mmAct[1]*pasosMM[1]).toFixed(0) );
      $("#mmy").text(mmAct[1]);
    // Error al conectar ajax desactiva todo
    }).fail(function(){
      $("#error").text("Ocurrió un error conectando a motores").show();
      jAlgoFunciona(false);
    });
  }
}
// Usa botones para ir a lugares específicos
function jLugares(){  
  // Obtiene lugar y posición actual de motores en mm
  var lug = $(this).text();
  for(var i=0; i<3; i++)
    lug += ","+ $("#mm"+ejes[i]).text();
  if(joysActivo == 0 && $("#error").html() == "" ){
    // Apaga eventos
    jAlgoFunciona(false);
    // Llama php con datos obtenidos:"lugar,X,Y,Z"
    $("#info").html("Posicionando sistema en lugar solicitado &nbsp;<span id='pingu'></span>").show();
    pinguParpadea(1);
    joysActivo = 1;
    document.documentElement.scrollTop = 0;
    console.log("Lugar:"+lug);
    $.ajax({
      type: 'POST',
      url:"php/joysMotores.php",
      data: {"pasoslug":lug}
    }).done(function(datos){
      // Recibe nuevas posiciones de motores (mm) en campos de texto
      datos = datos.split(",");
      for(i=0; i<3; i++){
        $("#pasos"+ejes[i]).val( Number(datos[i+1]*pasosMM[i]).toFixed(0) );
        $("#mm"+ejes[i]).text( datos[i+1] );
      }
      // Actualiza informativos, finaliza pingus y habilita todo
      $("#info").html("Ubicación del sistema: <b>"+datos[0]+"</b>").show();
      jAlgoFunciona(true);
      pinguParpadea(0);
      joysActivo = 0;
    }).fail(function(){
      // Manda error y deja deshabilitado
      $("#error").text("Ocurrió un error conectando con los lugares predeterminados").show();
      jAlgoFunciona(false);  
    });
  }
}
// Usa botones para probar electrónica
function jPruebas(){  
  // Apaga eventos y genera pingus
  jAlgoFunciona(false);
  var prueba = $(this).text();
  // Llama ajax si no está realizando otra cosa
  if(joysActivo == 0){
    // Manda mensaje correcto
    if( prueba.includes("Bomba") )
      $("#info").html("<b>"+prueba+"</b>: se activará 2 minutos en el sistema para verificar su conexión &nbsp;<span id='pingu'></span>").show();
    else
      $("#info").html("<b>"+prueba+"</b>: genere una interrupción en dicho elemento para probar su funcionamiento &nbsp;<span id='pingu'></span>").show();
    $("#info").append("</br> Para finalizar cualquier acción, presione <img src='img/paro.svg' alt='Paro total' width=25px'>.");
    document.documentElement.scrollTop = 0;
    // Deshabilita todo
    joysActivo = 1;
    jAlgoFunciona(false);
    pinguParpadea(1);
    console.log("Prueba:"+prueba);
    $.ajax({
      type: 'POST',
      url:"php/joysMotores.php",
      data: {"prueba":prueba}
    }).done(function(resp){
      // Actualiza informativo, finaliza pingus y habilita todo
      $("#info").html(resp).show();
      jAlgoFunciona(true);
      pinguParpadea(0);
      joysActivo = 0;
    }).fail(function(){
      // Manda error y deja deshabilitado
      $("#error").text("Ocurrió un error al probar las conexiones").show();
      jAlgoFunciona(false);  
    });
  }
}
//Botón que inicia la calibración o lleva a origen
function jCalibra(){
  // Apaga eventos y cierra ventana de calibración
  jAlgoFunciona(false);
  $("#cerrarPopup").hide();
  // Recopila datos para enviar con casita/origen
  if( $(this).attr("id") == "o" ){
    bot = "o";
    $("#info").html("Sensando origen &nbsp;<span id='pingu'></span>");
    // Devuelve pasos perdidos y pide mmXYZ actuales
    if( $("#error").text() == "" ){
      for(var i = 0; i<3; i++)
        bot += ","+$("#mm"+ejes[i]).text();
    }
  }
  // Recopila datos para calibrar lugar seleccionado
  else{
    $("#oo").html("Calibrando sistema &nbsp;<span id='pingu'></span>").attr("disabled",true);
    // Depura y arma datos a enviar
    var contJ = $("#contJoys").val();    
    var regex = /[$()!&%#[\]+/={\"}-]/g;
    contJ = contJ.replace(regex, '');
    // Evita mandar campo vacío
    if(contJ.length == 0)
      contJ = 0;
    bot = "oo,"+contJ+","+$("#calJoys").val();
  }
  if(joysActivo == 0){
    // Limpia error, manda pingus y manda ajax
    $("#error").empty().hide();
    document.documentElement.scrollTop = 0;
    pinguParpadea(1);
    joysActivo = 1;
    console.log("Calibra/origen:"+bot);
    $.ajax({
      type: 'POST',
      url:"php/joysMotores.php",
      data: {"bot":bot}
    }).done(function(datos){
      // Inicializa campos de texto
      $("#pasosx, #pasosy, #pasosz").val("0");
      $("#mmx, #mmy, #mmz").text("0");
      $("#info").html("Ubicación del sistema: Origen").show();
      // Manda detalles de sensado en origen
      $("#cargadoDeRutina, #ventanaProceso, #subeRutina, #calibrar").empty();
      $("#overlay, #popup").addClass("active");
      datos = datos.split(",");
      $("#popupjoys").html("<b>"+datos[0]+"</b></br></br>Eje X : "+datos[1]+"</br>Eje Y : "+datos[2]+"</br>Eje Z : "+datos[3]);
      // Finaliza pingus y habilita botones
      pinguParpadea(0);
      $("#cerrarPopup").show();
      jAlgoFunciona(true);
      joysActivo = 0;
    }).fail(function(){
      // Manda info de error
      $("#error").text("Ocurrió un error en el sistema").show();
      jAlgoFunciona(false);
    });
  }
}
// Carga ventana emergente para calibrar
function popCalibra(){
  $("#cargadoDeRutina, #ventanaProceso, #popupjoys, #subeRutina").empty();
  $("#overlay, #popup").addClass("active");
  $("#calibrar").html("<h3>Aviso importante</h3></br>Esta función es utilizada para definir la distancia entre el origen y los lugares predeterminados listados a continuación. Se requiere ingresar la contraseña para guardar las nuevas coordenadas XYZ en la base de datos.");
  $("#calibrar").append("</br></br> <div class='input-group flex-nowrap'><div class='input-group-prepend'><span class='input-group-text' id='addon-wrapping'>Contraseña</span></div><input id='contJoys' type='password'  maxlength='20' class='form-control' placeholder='Contraseña' aria-label='Username' aria-describedby='addon-wrapping'></div>");
  $("#calibrar").append("</br><div class='input-group mb-3'><div class='input-group-prepend'><label class='input-group-text' for='calJoys'> Lugar predeterminado </label> </div><select class='custom-select' id='calJoys'><option value='Usuario' selected>Usuario</option> <option value='Vacío'>Vacío</option> <option value='Lavado'>Lavado</option> <option value='Limpieza'>Limpieza</option> <option value='Muestra'>Muestra</option> <option value='Retícula'>Retícula</option></select></div>");
  $("#calibrar").append("</br><button type='button' id='oo' class='btn btn-dark btn-lg btn-block'>Calibrar lugar </button>");
}
// Inicia con recursos al cargar página
$(document).ready( function(){
  $("#botones, #info, #error").empty().hide();
  // Sólo se activa cuando no es Windows
  if( !(navigator.userAgent.indexOf("Windows") != -1) ){
    // Pone velo y desactiva mientras carga elementos
    $("#espera").css({"width":"100%","height":"100%","cursor":"wait"});
    jAlgoFunciona(false);
    // Pide ajax para referencia pasos por mm (únicamente una vez)
    $.ajax({
      type: 'POST',
      url:"php/joysMotores.php",
      data: {"pasoslug":"pasosmm"}
    }).done(function(datos){
      // Recibe nuevas posiciones de motores (mm) en campos de texto
      datos = datos.split(",");
      pasosMM = [ datos[0], datos[1], datos[2] ];
      for(var i=0; i<3; i++)
        $("#"+ejes[i]+"pasosmm").text(pasosMM[i]);
      // Habilita joystick, botón de emergencia y asigna velocidad lenta
      jAlgoFunciona(true);
      $("#parofin").on("click", paroFin);
      vel = 3;
      // Escribe mensajes y quita velo
      $("#info").html("<b>Velocidad lenta</b> seleccionada (no aplica para sensar origen ni lugares conocidos)").show();
      $("#error").html("Antes de realizar movimientos, pruebe el funcionamiento de los sensores y el botón de emergencia. Posteriormente, presione <img src='img/casa.svg' alt='Origen' width=25px'> para inicializar correctamente los pasos y lugares predeterminados").show();  
      $("#espera").css({"width":"0%","height":"0%","cursor":"auto"});
    }).fail(function(){
      // Evita que se ejecuten funciones si no hay valores
      jAlgoFunciona(false);
      pasosMM = [0,0,0];
      $("#xpasosmm, #ypasosmm, #zpasosmm").text(0);
      $("#info").html("Sin información disponible").show();
      $("#error").html("No se pudieron obtener los valores de medición para el joystick").show();  
    });
  }
  else
    $("#info").html("Sólo es posible acceder a los movimientos usando una Raspberry Pi").show();  
});
