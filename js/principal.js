// Variable que guarda la posicion en la que se encuentra y tipo de rutina
var actual = null, navpag = "principal", tipoRut = "", rep;
// Se crea un vector que controla el movimiento de las páginas
var pages = ["pines", "reticula", "slide", "lavado"];
// Variable que controla si se carga una rutina o se inicia una nueva
var tempNC = null;
// Variable que toma tiempo para refrescar posición obtenida (0.5s=100)
var interm = 1500;
// Variable que permite modificar en las distintas pestañas una vez guardado el programa
var pageKey = false;
// Lienzo [0-largo, 1-ancho, 2-multipix, 3-divX, 4-divY, 5-cuentaX, 6-cuentaY, 7-placaRealizadas, 8-placasTotales]
var canTam = [24, 16, 30, 0, 0, 0, 1, 0, 0];
// Tamaño de la placa de muestra [X, X+tamX, Y, Y+tamY, entrada/salida]
var muestraTam = [0, 0, 0, 0, 0];
// Dibuja el canvas limpio y botones para animación de progreso
function dibCanv () {
	//El div a modificar es ventanaProceso
	$("#popup").css("width", "85%");
	$("#overlay, #popup").addClass("active");
	$("#cerrarPopup").hide();
	//Borra el contenido de los otros DIVS dentro del popup
	$("#cargadoDeRutina, #subeRutina, #popupjoys, #calibrar").empty();
	//Realiza el contenedor del canvas con título
	if (tipoRut == "rut")
		$("#ventanaProceso").html("<h3> " + $("#rutAct").text() + "</h3> </br>");
	else if (tipoRut == "num")
		$("#ventanaProceso").html("<h3 id='titNum'> Rutina de numeración: </h3> </br>");
	else
		$("#ventanaProceso").html("<h3 id='titChips'> Rutina de chips múltiples: </h3> </br>");
	$("#ventanaProceso").append("<div class='alert alert-info' id='infoProceso'> </div>");
	$("#infoProceso").hide();
	$("#ventanaProceso").append("<canvas id='canvas1' width=" + canTam[0] * canTam[2] + " height=" + canTam[1] * canTam[2] + "></canvas>");
	// Datos de placa conforme avanza rutina principal
	if (tipoRut == "rut" || tipoRut == "chips")
		$("#ventanaProceso").append("<b>Placas de muestra faltantes: </b><b id='cuentaF'></b></br><b>Placas a realizar: </b><b id='cuentaT'></b></p><p id='posiciones'></p></br>").show();
	else if (tipoRut == "num")
		$("#ventanaProceso").append("<b>Número en impresión: </b><b id='numNum'></b></br><b>Serie numérica en cuestión: </b><b id='numSerie'></b><b> / 4</b></p><p id='posiciones'></p></br>").show();
	// Pone botones de pausa y paro
	$("#ventanaProceso").append("<table style='width:30%' id='tablaBotonesProceso' align='center'>").show();
	$("#tablaBotonesProceso").append("<tr><th><button type='button' class='btn btn-secondary btn-lg btn-block' id='bomAgua' style='margin-bottom:5%'>Encender <u>b</u>omba de agua</button></th></tr>");
	$("#tablaBotonesProceso").append("<tr><th><button type='button' class='btn btn-primary btn-lg btn-block' id='pausaRutina' style='margin-bottom:5%'><u>P</u>ausa</button></th></tr>");
	$("#tablaBotonesProceso").append("<tr><th><button type='button' class='btn btn-outline-success btn-lg btn-block' id='continuaRutina' style='margin-bottom:5%' disabled><u>R</u>eanudar proceso</button></th></tr>");
	$("#tablaBotonesProceso").append("<tr><th><button type='button' class='btn btn-danger btn-lg btn-block' id='paroTotal' style='margin-bottom:5%'>Paro <u>t</u>otal</button></th></tr>");
	$("#ventanaProceso").append("</table>");
	$("#navs").hide();
	// Crea líneas que dividen la placa muestra o escribe números en slide
	linCanv();
}
// Colorea un área de las muestras en lienzo
function colCanv () {
	// Finaliza de llenar lienzo completo
	if (canTam[5] * canTam[6] == canTam[3] * canTam[4]) {
		// Reinicia secciones y limpia lienzo
		canTam[5] = 1;
		canTam[6] = 1;
		linCanv();
	}
	// Al terminar la columna "X derecha" de muestras
	else if (canTam[5] == canTam[3]) {
		canTam[6]++;
		canTam[5] = 1;
		// Disminuye valor si es numeración
		if (tipoRut == "num") {
			$("#numNum").text(Number($("#numNum").text()) - 1);
			// Corrige valor en primer columna
			if (canTam[5] == 1 && canTam[6] != 1 && $("#numNum").text() == "8")
				$("#numNum").text(Number($("#numNum").text()) + 1);
		}
	}
	// Actualiza valor cuando llega a la penúltima sección de la muestra
	else if (canTam[6] == canTam[4] && canTam[5] == canTam[3] - 1) {
		canTam[5]++;
		canTam[7]++;
		// Disminuye valor si es numeración
		if (tipoRut == "num")
			$("#numNum").text(Number($("#numNum").text()) - 1);
	}
	// Siempre aumenta en columna "X derecha" los movimientos
	else {
		// Disminuye valor si es numeración
		if (tipoRut == "num") {
			$("#numNum").text(Number($("#numNum").text()) - 1);
			// Corrige valor por primera vez
			if (canTam[5] == 0)
				$("#numNum").text(Number($("#numNum").text()) + 1);
		}
		canTam[5]++;
	}
	// Contexto para inciar el lienzo
	var anima = document.getElementById("canvas1").getContext("2d");
	anima.fillStyle = "#ffd043";
	// Colorea los círculos en lienzo
	for (i = 1; i <= canTam[0] / canTam[3]; i++) {
		var offx = canTam[0] / canTam[3] * (canTam[5] - 1) * canTam[2];
		for (j = 1; j <= canTam[1] / canTam[4]; j++) {
			var offy = canTam[1] / canTam[4] * (canTam[6] - 1) * canTam[2];
			anima.beginPath();
			anima.arc(i * canTam[2] + offx - canTam[2] / 2, j * canTam[2] + offy - canTam[2] / 2, 10, 0, 2 * Math.PI);
			anima.fill();
		}
	}
}
// Dibuja las líneas a dividir el lienzo y sus círculos iniciales
function linCanv () {
	// Usa datos del resumen para rutina principal
	if (tipoRut == "rut") {
		// Encuentra los pines puestos, sus divisores y los bloques contados
		var cadPin = $("#pags table tbody").children("tr").first().children("td").last().text().split(" ");
		canTam[3] = canTam[0] / cadPin[0];
		canTam[4] = canTam[1] / cadPin[1];
		// Encuentra el total de placas, en el resumen en la página
		cadPin = $("#pags table tbody").children("tr").first().next().children("td").last().text().split(" ");
		canTam[8] = cadPin[cadPin.length - 1];
		// Valores iniciales de texto
		$("#cuentaF").text(canTam[8] - canTam[7] - 1);
		$("#cuentaT").text(canTam[8]);
	}
	// Toma los valores de numeración
	else if (tipoRut == "num") {
		// Divisores en XY del lienzo
		canTam[3] = 2;
		canTam[4] = 16;
		// Valores iniciales de texto
		$("#numNum").text("9");
		$("#numSerie").text("0");
	}
	// Usa datos del resumen para rutina principal
	if (tipoRut == "chips") {
		// Divisores en XY del lienzo
		canTam[3] = 3;
		canTam[4] = 4;
		// Valores iniciales de texto
		$("#cuentaF").text(canTam[8] - canTam[7] - 1);
		$("#cuentaT").text(canTam[8]);
	}
	// Obtiene contexto para iniciar el lienzo
	var anima = document.getElementById("canvas1").getContext("2d");
	anima.clearRect(0, 0, document.getElementById("canvas1").width, document.getElementById("canvas1").height);
	anima.lineWidth = 2;
	anima.strokeStyle = "#a6a6a6";
	// Dibuja las líneas primordiales X
	for (var i = 1; i <= canTam[3]; i++) {
		anima.beginPath();
		anima.moveTo(i * canTam[0] * canTam[2] / canTam[3], 0);
		anima.lineTo(i * canTam[0] * canTam[2] / canTam[3], canTam[1] * canTam[2]);
		anima.stroke();
	}
	// Dibuja las líneas primordiales Y
	for (i = 1; i <= canTam[4]; i++) {
		anima.beginPath();
		anima.moveTo(0, i * canTam[1] * canTam[2] / canTam[4]);
		anima.lineTo(canTam[0] * canTam[2], i * canTam[1] * canTam[2] / canTam[4]);
		anima.stroke();
	}
	// Dibuja los círculos en lienzo
	for (i = 1; i <= canTam[0]; i++)
		for (var j = 1; j <= canTam[1]; j++) {
			anima.beginPath();
			anima.arc(i * canTam[2] - canTam[2] / 2, j * canTam[2] - canTam[2] / 2, 10, 0, 2 * Math.PI);
			anima.stroke();
		}
}
// Crea los botones
function creaBotones (pags) {
	//	Las secciones de login, joystick y numeros contienen sus propios botones por lo que no deben agregarse a esta condicional
	if (pags != 'login' && pags != 'joystick' && pags != 'nums' && pags != 'chips') {
		// Genera una tabla para el acomodo de los botones 
		$("#botones").html("<table style='width:100%' id='tablaBotonesRutina'><tr>").show();
		// Si la pagina está en pines, no genera el botón de anterior
		if (pags == 'pines') {
			$("#tablaBotonesRutina").append("<th><button type='button' class='btn btn-outline-light btn-lg btn-block' disabled>boton</button></th>");
			$("#tablaBotonesRutina").append("<th><button type='button' class='btn btn-outline-light btn-lg btn-block' disabled>boton</button></th>");
			$("#tablaBotonesRutina").append("<th><button type='button' class='btn btn-primary btn-lg btn-block' id='Siguiente'><u>S</u>iguiente</button></th>");
		}
		else {
			// Si la pagina está en lavado, no genera el botón de siguiente y lo cambia por iniciar rutina
			if (pags == 'lavado') {
				$("#tablaBotonesRutina").append("<th><button type='button' class='btn btn-primary btn-lg btn-block' id='Anterior'><u>A</u>nterior</button></th>");
				$("#tablaBotonesRutina").append("<th><button type='button' class='btn btn-outline-light btn-lg btn-block' disabled>boton</button></th>");
				$("#tablaBotonesRutina").append("<th><button type='button' class='btn btn-success btn-lg btn-block' id='retornoPP'><u>R</u>utina lista</button></th>");
			}
			// Genera ambos botones, en el caso de estar en la página intermedia
			else {
				$("#tablaBotonesRutina").append("<th><button type='button' class='btn btn-primary btn-lg btn-block' id='Anterior'><u>A</u>nterior</button></th>");
				$("#tablaBotonesRutina").append("<th><button type='button' class='btn btn-outline-light btn-lg btn-block' disabled>boton</button></th>");
				$("#tablaBotonesRutina").append("<th><button type='button' class='btn btn-primary btn-lg btn-block' id='Siguiente'><u>S</u>iguiente</button></th>");
			}
		}
		// Cierra la tabla 
		$("#botones").append("</tr></table> </br>");
	}
	// Genera los botones de la sección de numeros 
	if (pags == 'nums' || pags == 'chips') {
		$("#botones").html("<table style='width:100%; margin-left: auto; margin-right: auto;' id='tablaBotonesRutina'><tr>").show();
		if (navigator.userAgent.indexOf("Linux") != -1){
			if (pags == 'nums')
				$("#tablaBotonesRutina").append("<th><button type='button' style='width:30vw; margin-left: auto; margin-right: auto;' class='btn btn-success btn-lg btn-block' id='inicioNumeracion' ><u>I</u>niciar numeración</button><th>");
			else
				$("#tablaBotonesRutina").append("<th><button type='button' style='width:30vw; margin-left: auto; margin-right: auto;' class='btn btn-success btn-lg btn-block' id='inicioChips' ><u>I</u>niciar chips múltiples</button><th>");
		}
			// Boton de códigoG
		$("#tablaBotonesRutina").append("<th><button type='button' style='width:30vw; margin-left: auto; margin-right: auto;' class='btn btn-info btn-lg btn-block' id='codigoG' name='" + pags + "'>Código <u>G</u></button>");
		$("#tablaBotonesRutina").append("<a href='./G/" + pags + ".nc' download><button type='button' class='btn btn-md btn-light' id='codigoGlink' hidden></button></a><th>");
		//$("#tablaBotonesRutina").append("<th><button type='button' class='btn btn-outline-light btn-lg btn-block' disabled>boton</button><th>");
		$("#botones").append("</tr></table> </br>");
	}
}
// Carga página principal.php: "v" indica si info será visible, info mostrará el mensaje
function cargaPrincipal () {
	$("#espera").css({ "width": "100%", "height": "100%", "cursor": "wait" });
	// Comienza a crear menú inicial
	$.post("php/principal.php", function (datos) {
		$("#info").empty().hide();
		$("#error").empty().hide();
		$("#botones").empty().show();
		$("#pags").html(datos);
		//Crea los botones de guardado y nueva rutina
		$("#botones").html("<table style='width:100%' id='tablaBotonesRutina'><tbody>");
		$("#tablaBotonesRutina").append("<td><button type='button' class='btn btn-outline-danger btn-lg btn-block' id='nuevaRutina'><u>N</u>ueva rutina</button><span> </span></td>");
		$("#tablaBotonesRutina").append("<td> </td>");
		$("#tablaBotonesRutina").append("<td><button type='button' class='btn btn-outline-warning btn-lg btn-block' id='cargaRutina'><u>C</u>argar rutina</button><span> </span></td>");
		$("#tablaBotonesRutina").append("<td> </td>");
		//Verifica si está definida la sesión y muestra el botón de guardar
		$.post("php/LSArch.php",{checkSesion:true}, function(datos){
			if(datos == '1'){
				//Cambia el contenido del botón dependiendo si la rutina ya fue guardada
				$.post("php/LSArch.php",{temporal:true}, function(resp){
					if(resp == '0') //la rutina ya está guardada
						$("#tablaBotonesRutina").append("<td style='width:18%'><button type='button' class='btn btn-outline-primary btn-lg btn-block' id='guardaRutina' >R<u>e</u>nombrar rutina</button><span> </span></td>");
					else if(resp == '1')
						$("#tablaBotonesRutina").append("<td style='width:18%'><button type='button' class='btn btn-outline-primary btn-lg btn-block' id='guardaRutina' ><u>G</u>uardar rutina</button><span> </span></td>");
					$.post("php/LSArch.php",{rutinaIniciada:true}, function(datos){
						if(datos == '0'){
							pageKey = true;
							$("#tablaBotonesRutina").append("<td style='width:2%'></td>");
							//Detecta el sistema operativo
							if (navigator.userAgent.indexOf("Linux") != -1)
								$("#tablaBotonesRutina").append("<td style='width:22%'><button type='button' class='btn btn-success btn-lg btn-block' id='inicioProceso'><u>I</u>niciar proceso</button>");
							if(!$("#codigoG").length){
								$("#tablaBotonesRutina").append("<td></td>");
								$("#tablaBotonesRutina").append("<td style='width:22%'><button type='button' class='btn btn-info btn-lg btn-block' id='codigoG' name='principal'>Código <u>G</u>&nbsp;&nbsp;</button></td></tr>");
								$.ajax({
									type:'POST',
									url:'php/bd.php',
									data:{nombreRutina : 1},
									success: function(elemento){
										$("#tablaBotonesRutina").append("<a href='./G/"+elemento+".nc' download><button type='button' class='btn btn-md btn-light' id='codigoGlink' hidden></button></a>");
									}
								});
							}
						}
						else if(datos == '1')
							pageKey = false;
					});
				});
			}
			else {
				pageKey = false;
				$("li").removeClass("active");
			}
		});
		$("#botones").append("</tbody></table> </br>");
		// Desaparece oscuridad
		$("#espera").css({ "width": "0%", "height": "0%", "cursor": "default" });
	}).fail(function (datos) {
		$("#info, #pags, #botones").empty();
		$("#info").hide();
		$("#error").text("No se pudo iniciar el servicio").show();
		console.log("Error ajax por petición de página principal:" + JSON.stringify(datos));
	});
}
// Crea la nueva rutina y manda a llamar a pines.php
function cargaNuevaRutina () {
	actual = 'pines';
	pageKey = false;
	//la peticion ajax envia un valor que activa una funcion en pines.php y crea un nuevo ID
	$.ajax({
		type: 'POST',
		url: 'php/pines.php',
		data: { creaID: true },
		success: function () {
			pags = 'pines';
			$("li").removeClass("active");
			//Se busca la clase que esta activa en la NavBar para que cambie el resaltado dependiendo de que boton se presiona
			$("li").find("a#" + pags).parent().addClass("active");
			$.post("php/" + pags + ".php", function (datos) {
				var indice = buscaIndice(pags);
				$("#info").text("Paso: " + indice + " de 4").show();
				$("#error").empty().hide();
				$("#pags").html(datos);
				//Botones de cada página
				creaBotones(pags);
			}).fail(function (datos) {
				$("#info, #pags, #botones").empty();
				$("#info").hide();
				$("#error").text("No se pudo iniciar el servicio").show();
				console.log("Error ajax por petición de página " + pags + ":" + JSON.stringify(datos));
			});
		}
	});
}
// Verifica pestaña de ubicación
function buscaIndice (pags) {
	var i = 0, indice = -1;
	for (i = 0; i < pages.length; i++) {
		if (pages[i] == pags)
			indice = i;
	}
	return ++indice;
}
// (0,1): pone pausa, (0,0):quita pausa, (1,1):paro total
function pausaTodo (tipo, val) {
	$.ajax({
		type: 'POST',
		url: "php/pausa.php",
		data: { "pausa": val, "tipo": tipo }
	}).always(function (datos) {
		console.log(datos);
	});
}
// Pide movimiento actual a través de coordenadas XYZ y pausas
function revisaRut () {
	$.ajax({
		type: 'POST',
		url: "php/pausa.php",
		data: { "consul": 1 }
	}).done(function (datos) {
		// Si hay datos, separa coordenadas y pausas
		console.log(datos);
		datos = datos.split("-");
		// Obtiene posiciones y tamaño de placa muestra (X:148mm, Y:105mm) la primera vez
		if (datos.length > 5)
			muestraTam = [Number(datos[4]), Number(datos[4]) + 148, Number(datos[5]), Number(datos[5]) + 105, 0];
		// Imprime posiciones en pantalla
		$("#posiciones").html("Posición XYZ (mm): " + datos[0] + "</br> Subproceso: " + datos[1]);
		// Ocurre pausa y detiene animación
		if (datos[2] != "0" || datos[3] != "0") {
			$("#pausaRutina").removeAttr("disabled").trigger("click");
			// Cambio de muestra (1,0)
			if (datos[2] == "1" && datos[3] == "0") {
				$("#infoProceso").html("Se debe cambiar la placa de <b>muestras</b>. Al finalizar, presione <b>Reanudar proceso</b> para retomar la rutina").show();
				// Al ser rutina de numeración, no limpia lienzo sino cambia números solamente
				if (tipoRut == "num") {
					$("#numNum").text("9");
					$("#numSerie").text(1 + Number($("#numSerie").text()));
				}
				// Limpia lienzo en rutina principal
				else
					linCanv();
			}
			// Cambio de vidrio y muestra (1,1)
			else if (datos[2] == "1" && datos[3] == "1") {
				$("#infoProceso").html("Se debe cambiar la placa de <b>muestras</b> y el vidrio de <b>limpieza</b>. Al finalizar, presione <b>Reanudar proceso</b> para retomar la rutina").show();
				// Al ser rutina de numeración, no limpia lienzo sino cambia números solamente
				if (tipoRut == "num") {
					$("#numNum").text("9");
					$("#numSerie").text(1 + Number($("#numSerie").text()));
				}
				// Limpia lienzo en rutina principal
				else
					linCanv();
			}
			// Pausa interna del programa (0,1)
			else
				$("#infoProceso").html("Pausa de seguridad generada por el programa interno, presione <b>Reanudar proceso</b> para retomar la rutina").show();
		}
		// Únicamente colorea lienzo cuando entra a muestra
		if (datos[1] == "Toma de muestra") {
			// Colorea sólo una vez nada al entrar
			if (muestraTam[4] == 0)
				colCanv();
			muestraTam[4] = 1;
		}
		// Cambia valor al salir de muestra
		else
			muestraTam[4] = 0;
	}).fail(function (datos) {
		console.log("Error en javascript al revisar rutina: " + JSON.stringify(datos));
	});
}
// Función para activar o desactivar pantalla completa
function pantComp () {
	// Activa o desactiva
	if (!document.fullscreenElement)
		document.documentElement.requestFullscreen();
	else
		document.exitFullscreen();
}
// Teclas rápidas para eventos del navegador cuando no hay campo de texto
function tecRap (event) {
	// Teclas libres del footer
	if ( $("#popup").attr("class") != "active" && $(":password").length == 0 ){
		switch (event.keyCode) {
			// L: cambia ventana completa
			case 76:
				$("#pantcomp").trigger("click");
				break;
			// M: abre documentación
			case 77:
				$("#docref").trigger("click");
				break;
			// O: abre créditos
			case 79:
				if ($("#popup").attr("class") != "active")
					$("#creds").trigger("click");
				break;
		}
	}
	// Teclas condicionales según campos de texto o pestañas activas
	if ( !( $(":text").length > 0 && $("#popup").attr("class") == "active" && navpag != "nums" && navpag != "chips") && navpag != "login" && navpag != "joystick") {
		switch (event.keyCode) {
			// A: botón de anterior
			case 65:
				if ($("#Anterior").length > 0 && $("#popup").attr("class") != "active")
					$("#Anterior").trigger("click");
				break;
			// B: activa bomba de lavado en popup
			case 66:
				if ($("#bomAgua").length > 0)
					$("#bomAgua").trigger("click");
				break;
			// C: carga rutina
			case 67:
				if ($("#cargaRutina").length > 0 && $("#popup").attr("class") != "active")
					$("#cargaRutina").trigger("click");
				break;
			// E: renombra rutina
			case 69:
				if ($("#guardaRutina").length > 0 && $("#popup").attr("class") != "active")
					$("#guardaRutina").trigger("click");
				break;
			// G: código G
			case 71:
				if ($("#codigoG").length > 0 && $("#popup").attr("class") != "active")
					$("#codigoG").trigger("click");
				break;
			// I: inicia procesos según el caso
			case 73:
				if ($("#inicioProceso").length > 0 && $("#popup").attr("class") != "active")
					$("#inicioProceso").trigger("click");
				else if ($("#inicioNumeracion").length > 0 && $("#popup").attr("class") != "active")
					$("#inicioNumeracion").trigger("click");
				else if ($("#inicioChips").length > 0 && $("#popup").attr("class") != "active")
					$("#inicioChips").trigger("click");
				break;
			// N: nueva rutina
			case 78:
				if ($("#nuevaRutina").length > 0 && $("#popup").attr("class") != "active")
					$("#nuevaRutina").trigger("click");
				break;
			// P: pausa proceso o recarga página
			case 80:
				if ($("#pausaRutina").length > 0)
					$("#pausaRutina").trigger("click");
				else if ($("#recPag").length > 0)
					$("#recPag").trigger("click");
				break;
			// R: rutina lista o reanudar proceso en pausa
			case 82:
				if ($("#retornoPP").length > 0 && $("#popup").attr("class") != "active")
					$("#retornoPP").trigger("click");
				else if ($("#continuaRutina").length > 0)
					$("#continuaRutina").trigger("click");
				break;
			// S: botón de siguiente
			case 83:
				if ($("#Siguiente").length > 0 && $("#popup").attr("class") != "active")
					$("#Siguiente").trigger("click");
				break;
			// T: paro total
			case 84:
				if ($("#paroTotal").length > 0)
					$("#paroTotal").trigger("click");
				break;
			// U: guarda rutina
			case 85:
				if ($("#guardaRutina").length > 0 && $("#popup").attr("class") != "active")
					$("#guardaRutina").trigger("click");
				break;
		}
	}
}
// Activado cada vez que se presiona la barra de navegación
function barraNav () {
	navpag = $(this).attr("id");
	// Mientras se presione pestaña que no sea rutina
	if (navpag != "principal" && navpag != "logo" && (navpag == actual || (navpag == "joystick" && navigator.userAgent.indexOf("Linux") != -1) || navpag == "login" || navpag == "nums" || navpag == "chips" || (pageKey && navpag != "joystick"))) {
		$("#espera").css({ "width": "100%", "height": "100%", "cursor": "wait" });
		//Comprueba que la clase de joystick sea verdadera para eliminar la clase active
		$("#joystick, #login, #nums, #chips").parent().removeClass("active");
		//Remueve los atributos de active cuando se puede desplazar en cualquier pestaña
		if (pageKey)
			$("li").removeClass("active");
		$(this).parent().addClass("active");
		// Genera animación para llevar al principio de la página
		var arriba = $("#info").parent().offset().top;
		$("HTML, BODY").animate({ scrollTop: arriba }, 1000);
		// Solicita página respectiva y escribe datos recibidos
		setTimeout(function () {
			$.post("php/" + navpag + ".php", function (datos) {
				var indice = buscaIndice(navpag);
				$("#info").text("Paso: " + indice + " de 4").show();
				$("#error").empty().hide();
				$("#pags").html(datos);
				//Botones de cada página
				creaBotones(navpag);
				setTimeout(function () {
					if (actual == "reticula") {
						$(".update-db-submit").trigger("click");
					}
				}, 50);
				// Desaparece oscuridad
				$("#espera").css({ "width": "0%", "height": "0%", "cursor": "default" });
			}).fail(function (datos) {
				$("#info, #pags, #botones").empty()
				$("#info").hide();
				$("#error").text("No se completó la petición de página").show();
				console.log("Error ajax por petición de página " + navpag + ":" + JSON.stringify(datos));
			});
		}, 50);
	}
	// Recarga página del índice
	else {
		//Remueve el atributo de active del joystick o config
		var joyconf = $("ul").find("li.nav-item.active").children().attr("id");
		if (joyconf == "joystick" || joyconf == "login" || joyconf == "chips" || joyconf == "nums")
			$("li").removeClass("active");
		if (navpag == "principal" || navpag == "logo")
			cargaPrincipal();
	}
}
// Inicia documento y verifica base de datos
$(document).ready(function () {
	// Oculta secciones y checa si existe la base de datos para funcionar todo
	$("#espera").css({ "width": "100%", "height": "100%", "cursor": "wait" });
	$("#credsTXT, #error").hide();
	$("#info").text("Cargando base de datos, espere por favor").show();
	$.ajax({
		type: 'POST',
		url: 'php/bd.php',
		data: { 'inicia': 2 }
	}).done(function (existe) {
		// Carga si toda la base está bien
		if (existe) {
			// Botones de créditos y link a pdf de documentación
			$(document).on("keydown", tecRap);
			$("#pantcomp").on("click", pantComp);
			$("#credsTXT").hide();
			$("#creds").on("click", function () { $("#credsTXT").toggle("slow") });
			$("#docref").on("click", function () { window.open("Manual de usuario.pdf") });
			// Carga página, barra de navegación y eventos delegados (no pueden ir con funciones externas)
			cargaPrincipal();
			$("a").on("click", barraNav);
			// Movimiento entre páginas siguiente
			$("#botones").on("click", "#Siguiente", function () {
				// Genera animación para llevar al principio de la página
				var arriba = $("#info").parent().offset().top;
				$("HTML, BODY").animate({ scrollTop: arriba }, 1000);
				// Obtiene la pagina actual
				var actualPage = $("ul").find("li.nav-item.active").children().attr("id")
				// Obtiene la página siguiente 
				var nextPage = $.inArray(actualPage, pages) + 1;
				pags = pages[nextPage];
				actual = pags;
				//Trigger del boton que se manda a llamar para subir a la base de datos
				$(".update-db-submit").trigger("click");
				$("li").removeClass("active");
				//Se realiza la actualizacion de la base de datos
				//Es importante que guardar las variables en sessiones para poder subirlas a BD
				//Remueve la clase active para quitar el remarcado
				$("li").removeClass("active");
				$("li").find("a#" + pages[nextPage]).parent().addClass("active");
				setTimeout(function () {
					// Carga la página con un ligero delay para subir los datos a la BD
					$.post("php/" + pags + ".php", function (datos) {
						var indice = buscaIndice(pags);
						$("#info").text("Paso: " + indice + " de 4").show();
						$("#error").empty().hide();
						$("#pags").html(datos);
						//Botones de cada página
						creaBotones(pags);
						setTimeout(function () {
							if (actual == "reticula") {
								$(".update-db-submit").trigger("click");
							}
						}, 100);
					}).fail(function (datos) {
						$("#info, #pags, #botones").empty()
						$("#info").hide();
						$("#error").text("No se completó la petición de página").show();
						console.log("Error ajax por petición de página " + pags + ":" + JSON.stringify(datos));
					});
				}, 100);
			});
			// Movimiento entre páginas anterior
			$("#botones").on("click", "#Anterior", function () {
				// Genera animación para llevar al principio de la página
				var arriba = $("#info").parent().offset().top;
				$("HTML, BODY").animate({ scrollTop: arriba }, 1000);
				// Verifica la página en la que se enceuntra y carga la siguiente
				var actualPage = $("ul").find("li.nav-item.active").children().attr("id")
				var nextPage = $.inArray(actualPage, pages) - 1;
				pags = pages[nextPage];
				actual = pags;
				// Trigger opara subir datos a la BD
				$(".update-db-submit").trigger("click");
				// Remueve el atributo active para colocarlo a la página a cargar
				$("li").removeClass("active");
				$("li").find("a#" + pages[nextPage]).parent().addClass("active");
				// Genera un ligero delay para poder subir los datos a la BD
				setTimeout(function () {
					// Carga la página anterior
					$.post("php/" + pags + ".php", function (datos) {
						var indice = buscaIndice(pags);
						$("#info").text("Paso: " + indice + " de 4").show();
						$("#error").empty().hide();
						$("#pags").html(datos);
						//Botones de cada página
						creaBotones(pags);
						setTimeout(function () {
							if (actual == "reticula") {
								$(".update-db-submit").trigger("click");
							}
						}, 50);
					}).fail(function (datos) {
						$("#info, #pags, #botones").empty()
						$("#info").hide();
						$("#error").text("No se completó la petición de página").show();
						console.log("Error ajax por petición de página " + pags + ":" + JSON.stringify(datos));
					});
				}, 50);
			});
			// LLeva a la pestaña de pines y crea el ID, manda un popup preguntando si se desea continuar sin guardar
			$("#botones").on("click", "#nuevaRutina", function () {
				$.post("php/LSArch.php", { temporal: true }, function (datos) {
					//Contiene los datos dados por el usuario
					var accion = datos;
					//Carga la nueva rutina en caso de no haber problema 
					if (accion == "0")
						cargaNuevaRutina();
					else {
						if (accion == "1") {
							tempNC = "nuevo";
							//Se abre el popup que preguntará al usuario si quiere continuar
							//si continua, se borra todos los datos en la base
							$("#overlay, #popup").addClass("active");
							// Vacía los demás divs del popup para insertar la información
							$("#cargadoDeRutina, #ventanaProceso, #popupjoys, #calibrar").empty();
							// Genera botones para continuar con guardado o no de la rutina actual
							$("#subeRutina").html("<h3>Aviso importante</h3> </br> <div class='alert alert-danger' role='alert'> Se eliminarán los datos actuales, ¿Desea continuar?</div>");
							$("#subeRutina").append("<center><table style='width:60%' id='botonesBorrarTemporal'>");
							$("#botonesBorrarTemporal").append("<th><button type='button' class='btn btn-danger btn-lg btn-block' id='borraTemp'>Continuar sin guardar</button></th>");
							$("#botonesBorrarTemporal").append("<th><button type='button' class='btn btn-info btn-lg btn-block' id='guardaTemp'>Guardar la rutina</button></th>");
							$("#subeRutina").append("</table><center>");
						}
						else
							cargaPrincipal();
						//$("#error").text("No se pudo iniciar el servicio").show();
					}
				});
			});
			// Popup de guardado de páginas
			$("#botones").on("click", "#guardaRutina", function () {
				//Se agrega la clase active para que se pueda mostrar en pantalla
				$("#overlay, #popup").addClass("active");
				// Vacía los demás divs del popup para insertar la información
				$("#cargadoDeRutina, #ventanaProceso, #popupjoys, #calibrar").empty();
				// Genera botones y cuadro de texto para guardar con nombre la rutina
				$("#subeRutina").html("</br><h5> Nombre de la nueva rutina</h5>");
				$("#subeRutina").append("<div role='alert' id='errorSubeRutina'></br></br></div>");
				$("#subeRutina").append("<div class='input-group'>");
				$("#subeRutina").append("<input type='text' class='form-control' id='nombreDB'>");
				$("#subeRutina").append("</br><button type='button' class='btn btn-info' id='enviarNombreDB'>Enviar</button>");
				$("#subeRutina").append("</div>");
			});
			// El botón de enviar cierra el popup y manda a llamar un archivo para que guarde la base de datos
			$("#overlay").on("click", "#enviarNombreDB", function (e) {
				e.preventDefault();
				// Validación para guardar la rutina con nombre
				if ($("#nombreDB").val() == '') {
					$("#errorSubeRutina").addClass('alert alert-danger');
					$("#errorSubeRutina").html("El nombre de la rutina debe contener al menos un caracter válido").removeAttr("hidden");
				}
				else {
					// Cierra popup
					$("#overlay, #popup").removeClass("active");
					// Genera animación para llevar al principio de la página
					var arriba = $("#info").parent().offset().top;
					$("HTML, BODY").animate({ scrollTop: arriba }, 1000);
					//Verifica si esta definido el campo de lavado, si no, se manda una alerta al usuario
					$.ajax({
						type: 'POST',
						url: 'php/LSArch.php',
						data: { confirmLavado: true },
						success: function (datos) {
							if (datos == '0') {
								$.ajax({
									type: 'POST',
									url: 'php/subeRutina.php',
									data: {
										rutina: $("#nombreDB").val(),
										re: "1"
									},
									success: function (data) {
										accion = String(data);
										if (accion == '0') {
											setTimeout(function () {
												// Abre el popup y vacía otros contenedores para mostrar informacion
												$("#overlay, #popup").addClass("active");
												$("#cargadoDeRutina, #ventanaProceso, #popupjoys, #calibrar").empty();
												// Crea botones para sobreescritura de rutina
												$("#subeRutina").html("</br> </br> <div class='alert alert-danger' role='alert'> El nombre de la rutina ya existe ¿Desea sobreescribirla? </div>");
												$("#subeRutina").append("<center><table style='width:60%' id='reescribirRutina'>");
												$("#reescribirRutina").append("<th></br><button type='button' class='btn btn-danger btn-lg btn-block' id='reNo'>No</button></th>");
												$("#reescribirRutina").append("<th><button type='button' class='btn btn-outline-light btn-lg btn-block' disabled>boton</button></th>");
												$("#reescribirRutina").append("<th></br><button type='button' class='btn btn-info btn-lg btn-block' id='reSi'>Sí</button></th>");
												$("#subeRutina").append("</table> </br><center>");
											}, 500);
										}
										else {
											// Mensaje de error en pantalla en caso de existir un problema por conectar
											if (accion == '1') {
												$("#error").html("Existió un problema con al intentar conectar con la base de datos");
											}
											// La rutina se guardo correctamente 
											else {
												if (accion == '2') {
													$("#info").html("La rutina fue guardada con éxito").show();
													$("#rutinaGuardadaTexto").html("SÍ");
													$("#guardaRutina").html("R<u>e</u>nombrar rutina");
													$("h4").text("Rutina actual: " + $("#nombreDB").val());
													$("#error").empty().hide();
													// Muestra mensaje de que se guardó la rutina por un segundo
													setTimeout(function () {
														$("#info").empty().hide();
													}, 1000);
												}
											}
										}
									}
								});
							}
							else {
								//Se indica al usuario que no se han completado todos los datos
								$("#error").html("Para guardar la rutina es necesario definir todos los campos").show();
							}
						}
					});
				}
			});
			// Carga los nombres de la base de datos
			$("#botones").on("click", "#cargaRutina", function () {
				// Abre popup
				$("#overlay, #popup").addClass("active");
				// Limpia contenedores para poder mostrar información
				$("#subeRutina, #ventanaProceso, #popupjoys, #calibrar").empty();
				$.post("php/cargaRutina.php", function (datos) {
					$("#cargadoDeRutina").html(datos);
				});
			});
			// Selecciona la base de datos de acuerdo a un boton y recarga con un nuevo id
			$("#cargadoDeRutina").on("click", "button", function (e) {
				//Se borra la clase active para quitar el popup
				e.preventDefault();
				$("#overlay, #popup").removeClass("active");
				//Genera una animación para cuando se cierre el popup
				var arriba = $("#info").parent().offset().top;
				$("HTML, BODY").animate({ scrollTop: arriba }, 1000);
				//Se debe generar un explode para quitar el carga
				//-----------Importante------------
				//Se hace en el archivo de cargaRutina.php
				$.ajax({
					type: 'POST',
					url: 'php/cargaRutina.php',
					data: {
						rutinaCarga: this.id,
					},
					success: function (data) {
						//Recarga la pagina para actualizar con el nuevo ID
						if (data == "borrar") {
							setTimeout(function () {
								// Abre popup
								$("#overlay, #popup").addClass("active");
								// Genera botones para borrar la rutina 
								$("#cargadoDeRutina").html("<h3>Advertencia: </h3> <strong>Está a punto de borrar una rutina, ¿Desea continuar?</strong>");
								$("#cargadoDeRutina").append("<center><table style='width:60%' id='botonesBorraRutina'>");
								$("#botonesBorraRutina").append("<th></br><button type='button' class='btn btn-danger btn-lg btn-block' id='cacelarRutina'>Cancelar</button></th>");
								$("#botonesBorraRutina").append("<th></br><button type='button' class='btn btn-info btn-lg btn-block' id='borrarRutina'>Continuar</button></th>");
								$("#cargadoDeRutina").append("</table> </br><center>");
							}, 500);
							actual = '';
						}
						else {
							if (data == "carga") {
								$.post("php/LSArch.php", { temporal: true }, function (datos) {
									//Contiene los datos de temporal
									var accion = datos;
									if (accion == "0") {
										$.ajax({
											type: 'POST',
											url: 'php/cargaRutina.php',
											data: {
												cargaok: true,
											},
											success: function (data) {
												cargaPrincipal();
											}
										});
									}
									else {
										if (accion == "1") {
											tempNC = "carga";
											//Se abre el popup que preguntará al usuario si quiere continuar
											//si continua, se borra todos los datos en la base
											$("#overlay, #popup").addClass("active");
											$("#cargadoDeRutina, #ventanaProceso, #popupjoys, #calibrar").empty();
											$("#subeRutina").html("<h3>Aviso importante</h3> </br> <div class='alert alert-danger' role='alert'> Se eliminarán los datos actuales, ¿Desea continuar? </div>");
											$("#subeRutina").append("<center><table style='width:60%' id='botonesBorrarTemporal'>");
											$("#botonesBorrarTemporal").append("<th><button type='button' class='btn btn-danger btn-lg btn-block' id='borraTemp'>Descartar y continuar</button></th>");
											$("#botonesBorrarTemporal").append("<th><button type='button' class='btn btn-info btn-lg btn-block' id='guardaTemp'>Guardar la rutina actual</button></th>");
											$("#subeRutina").append("</table><center>");
										}
										else
											$("#error").text("No se pudo iniciar el servicio").show();
									}
								});
							}
							else {
								//recarga la ventana principal en caso de haber borrado
								cargaPrincipal();
								//pageKey = false;
							}
						}
					}
				});
			});
			// Botones varios de los popup emergentes
			$("#subeRutina").on("click", "button", function () {
				if ($(this).attr("id") != 'enviarNombreDB') {
					// Abre el popup 
					$("#overlay, #popup").removeClass("active");
					// Borra rutinas temporales
					if ($(this).attr("id") == "borraTemp") {
						$.post("php/LSArch.php", { borraTemp: true }, function (datos) {
							var accion = datos;
							if (accion == "0") {
								// Carga una nueva rutina
								if (tempNC == "nuevo")
									cargaNuevaRutina();
								else {
									$.ajax({
										type: 'POST',
										url: 'php/cargaRutina.php',
										data: {
											cargaok: true
										},
										success: function () {
											cargaPrincipal();
										}
									});
								}
							}
							else
								$("#error").text("Hubo un error al conectar con la base de datos").show();
						});
					}
					else {
						// Recarga el popup
						if ($(this).attr("id") == "guardaTemp" || $(this).attr("id") == "reNo") {
							setTimeout(function () {
								$('#guardaRutina').eq(0).click()
							}, 500);
						}
						else {
							// Guarda la rutina
							if ($(this).attr("id") == "reSi") {
								$.ajax({
									type: 'POST',
									url: 'php/subeRutina.php',
									data: {
										rutina: " ",
										re: "2"
									},
									success: function () {
										$("#info").html("La rutina fue guardada con éxito").show();
										$("#rutinaGuardadaTexto").html("SÍ");
										$("#guardaRutina").html("R<u>e</u>nombrar rutina");
										$("#error").empty().hide();
										setTimeout(function () {
											$("#info").empty().hide();
										}, 1000);
									}
								});
							}
						}
					}
				}
			});
			// Cerrar popup
			$("#overlay").on("click", "#cerrarPopup", function (e) {
				e.preventDefault();
				$("#overlay, #popup").removeClass("active");
				$("#popup").css("width", "600px");
			});
			// Recarga la pagina para llevarlo a la pagina principal
			$("#botones").on("click", "#retornoPP", function () {
				$(".update-db-submit").trigger("click");
				actual = 'lavado';
				$.post("php/LSArch.php", { actualizaRutina: true });
				cargaPrincipal();
			});
			// Inicia rutina de numeración 
			$("#botones").on("click", "#inicioNumeracion", function () {
				// Junta datos de formulario para numeración
				var datNums = $("#coordXNums").val() + "," + $("#coordYNums").val();
				datNums += "," + $("#xSlidesNumeros").val() + "," + $("#ySlidesNumeros").val();
				//Se agrega el lienzo en pantalla y función intermitente que devuelve movimiento actual
				tipoRut = "num";
				dibCanv();
				$("#titNum").append($("#seccionNum option:selected").text());
				$("#cerrarPopup").remove();
				rep = setInterval(function () { revisaRut(); }, interm);
				//Manda promesa de ajax para código principal
				var proceso = new Promise(function (resolve, reject) {
					$.ajax({
						type: 'POST',
						url: "php/RutinaCnums.php",
						data: { "datnum": datNums }
					}).done(function () {
						resolve(true);
					}).fail(function (error) {
						reject(error);
					});
				});
				proceso.then(() => {
					clearInterval(rep);
					$("#infoProceso").html("Rutina de numeración finalizada correctamente").show();
					$("#numNum").text("2");
					$("#numSerie").text("4");
					$("#tablaBotonesProceso").empty().append("<tr><th><button type='button' class='btn btn-warning btn-lg btn-block' id='recPag' onclick='location.reload()'>Regresar a ventana <u>p</u>rincipal</button></th></tr>");
				});
				proceso.catch((error) => {
					clearInterval(rep);
					$("#infoProceso").removeClass("alert-info").addClass("alert-danger").html("Error al ejecutar rutina de numeración").show();
					$("#tablaBotonesProceso").empty().append("<tr><th><button type='button' class='btn btn-warning btn-lg btn-block' id='recPag' onclick='location.reload()'>Regresar a ventana <u>p</u>rincipal</button></th></tr>");
					console.log("Error: " + JSON.stringify(error));
				});
			});
			// Inicia rutina de chips múltiples 
			$("#botones").on("click", "#inicioChips", function () {
				// Junta datos de formulario para numeración
				var datChips = $("#PinesXDobles").val() + "," + $("#XCoords").val() + "," + $("#YCoords").val();
				datChips += "," + $("#XDotsSpace").val() + "," + $("#YDotsSpace").val();
				datChips += "," + $("#XDots").val() + "," + $("#YDots").val() + "," + $("#DuplicateDotsY").val() + "," + $("#NoPlates").val();
				datChips += "," + $("#xSlidesNumeros").val() + "," + $("#ySlidesNumeros").val();
				canTam[8] = $("#NoPlates").val();
				//Se agrega el lienzo en pantalla y función intermitente que devuelve movimiento actual
				tipoRut = "chips";
				dibCanv();
				$("#titChips").append($("#PinesNumeros_totales").val() + " arreglos");
				$("#cerrarPopup").remove();
				rep = setInterval(function () { revisaRut(); }, interm);
				//Manda promesa de ajax para código principal
				var proceso = new Promise(function (resolve, reject) {
					$.ajax({
						type: 'POST',
						url: "php/RutinaCchips.php",
						data: { "datchips": datChips }
					}).done(function () {
						resolve(true);
					}).fail(function (error) {
						reject(error);
					});
				});
				proceso.then(() => {
					clearInterval(rep);
					$("#infoProceso").html("Rutina de chips múltiples finalizada correctamente").show();
					$("#cuentaF").text("0");
					$("#tablaBotonesProceso").empty().append("<tr><th><button type='button' class='btn btn-warning btn-lg btn-block' id='recPag' onclick='location.reload()'>Regresar a ventana <u>p</u>rincipal</button></th></tr>");
				});
				proceso.catch((error) => {
					clearInterval(rep);
					$("#infoProceso").removeClass("alert-info").addClass("alert-danger").html("Error al ejecutar rutina de chips múltiples").show();
					$("#tablaBotonesProceso").empty().append("<tr><th><button type='button' class='btn btn-warning btn-lg btn-block' id='recPag' onclick='location.reload()'>Regresar a ventana <u>p</u>rincipal</button></th></tr>");
					console.log("Error: " + JSON.stringify(error));
				});
			});
			// Abre popup para iniciar proceso de rutina principal
			$("#botones").on("click", "#inicioProceso", function () {
				// Ventana emergente del proceso y función intermitente que devuelve movimiento actual
				tipoRut = "rut";
				dibCanv();
				$("#cerrarPopup").remove();
				rep = setInterval(function () { revisaRut(); }, interm);
				//Manda promesa de ajax para código principal
				var proceso = new Promise(function (resolve, reject) {
					$.ajax({
						type: 'POST',
						url: "php/RutinaC.php"
					}).done(function () {
						resolve(true);
					}).fail(function (error) {
						reject(error);
					});
				});
				proceso.then(() => {
					clearInterval(rep);
					$.post("php/LSArch.php", { borraTemp: true });
					$("#infoProceso").html("Rutina principal finalizada correctamente").show();
					$("#cuentaF").text("0");
					$("#tablaBotonesProceso").empty().append("<tr><th><button type='button' class='btn btn-warning btn-lg btn-block' id='recPag' onclick='location.reload()'>Regresar a ventana <u>p</u>rincipal</button></th></tr>");
				});
				proceso.catch((error) => {
					clearInterval(rep);
					$.post("php/LSArch.php", { borraTemp: true });
					$("#infoProceso").removeClass("alert-info").addClass("alert-danger").html("Error al ejecutar rutina de chips múltiples").show();
					$("#tablaBotonesProceso").empty().append("<tr><th><button type='button' class='btn btn-warning btn-lg btn-block' id='recPag' onclick='location.reload()'>Regresar a ventana <u>p</u>rincipal</button></th></tr>");
					console.log("Error: " + JSON.stringify(error));
				});
			});
			// Lanza código G
			$("#botones").on("click", "#codigoG", function () {
				// Genera animación para llevar al principio de la página
				var arriba = $("#info").parent().offset().top;
				$("HTML, BODY").animate({ scrollTop: arriba }, 1000);
				$("#espera").css({ "width": "100%", "height": "100%", "cursor": "wait" });
				$("#info").text("Generando código G, espere por favor").show();
				//this.name tiene el nombre de la página donde se pulso el boton de codigo g
				if (this.name == "principal") {
					$.post("php/RutinaG.php", function () {
						$("#codigoGlink").trigger("click");
					}).always(function () {
						$("#espera").css({ "width": "0%", "height": "0%", "cursor": "default" });
						$("#info").empty().hide();
					});
				}
				else {
					if (this.name == "nums") {
						// Junta datos de formulario para numeración
						var datNums = $("#coordXNums").val() + "," + $("#coordYNums").val();
						datNums += "," + $("#xSlidesNumeros").val() + "," + $("#ySlidesNumeros").val();
						// Crea archivo G
						$.post("php/RutinaGnums.php", { "datnum": datNums }, function () {
							$("#codigoGlink").trigger("click");
						}).always(function () {
							$("#espera").css({ "width": "0%", "height": "0%", "cursor": "default" });
							$("#info").empty().hide();
						});
					}
					else {
						// Junta datos de formulario para chips múltiples
						var datNums = $("#coordXNums").val() + "," + $("#coordYNums").val();
						datNums += "," + $("#xSlidesNumeros").val() + "," + $("#ySlidesNumeros").val();
						// Crea archivo G
						$.post("php/RutinaGchips.php", { "datnum": datNums }, function () {
							$("#codigoGlink").trigger("click");
						}).always(function () {
							$("#espera").css({ "width": "0%", "height": "0%", "cursor": "default" });
							$("#info").empty().hide();
						});
					}
				}
			});
			// Botón de pausa de la ventana de proceso, botoncito azul
			$("#ventanaProceso").on("click", "#pausaRutina", function () {
				pausaTodo(0, 1);
				// Alerta al usuario 
				$("#infoProceso").html("El sistema fue pausado por el usuario").show();
				// Habilita botón de continuar proceso y deshabilita el de pausa
				$("#continuaRutina").removeAttr("disabled").removeClass("btn-outline-success").addClass("btn-success");
				$("#pausaRutina").attr("disabled", true).addClass("btn-outline-primary");
			});
			// Continua la rutina actual al presionar botoncito verde
			$("#ventanaProceso").on("click", "#continuaRutina", function () {
				pausaTodo(0, 0);
				// Quita alerta
				$("#infoProceso").empty().hide();
				// Habilita botón de pausa mientras deshabilita el de continuar
				$("#pausaRutina").removeAttr("disabled").removeClass("btn-outline-primary").addClass("btn-primary");
				$("#continuaRutina").attr("disabled", true).addClass("btn-outline-success");
			});
			// Botón de paro total
			$("#ventanaProceso").on("click", "#paroTotal", function () {
				$("#infoProceso").html("Se realizó un paro total del sistema, finalizando rutinas pendientes").show();
				$("#continuaRutina, #pausaRutina, #paroTotal").attr("disabled", true);
				clearInterval(rep);
				pausaTodo(1, 1);
			});
			// Botón de bomba de agua
			$("#ventanaProceso").on("click", "#bomAgua", function () {
				var tipoBom = $(this).text();
				// Define acción a tomar
				if (tipoBom == "Encender bomba de agua") {
					$(this).html("Apagar <u>b</u>omba de agua").removeClass("btn-secondary").addClass("btn-dark");
					tipoBom = 1;
				}
				else {
					$(this).html("Encender <u>b</u>omba de agua").removeClass("btn-dark").addClass("btn-secondary");
					tipoBom = 0;
				}
				// Acciona bomba
				$.post("php/joysMotores.php", { "popup": tipoBom });
			});
		}
		else {
			$("#info").empty().hide();
			$("#error").text("No se puede conectar a la base de datos del programa").show();
		}
	}).fail(function () {
		$("#info").empty().hide();
		$("#error").text("Error interno con el servidor. Verifique su funcionamiento").show();
	});
});
