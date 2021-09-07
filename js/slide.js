// Inicia documento y carga página completa
$(document).ready( function(){
	$("select").ready(inicio);
	$("select").on("change",inicio);
	$(".update-db-submit").on("click",cargar);

	function inicio(jQuery){
		var x = $("#ejeX").val();
		var y = $("#ejeY").val();
		var fila = $("#casillas>tbody").children().first();

	  $("#NoSlides").val(x*y);


		for(var i=1; i<=10; i++){
			celda = fila.children().first();
			celda.css("background-color", "#ffffff");
			for(var j=1; j<=5; j++){
				celda = celda.next().css("background-color", "#ffffff");
			}
			fila = fila.next();
		}

		fila = $("#casillas>tbody").children().first();
		for(var i=1; i<=y; i++){
			celda = fila.children().first();
			celda.css("background-color", "#c3c3c3");
			for(var j=2; j<=x; j++){
				celda = celda.next().css("background-color", "#c3c3c3");
			}
			fila = fila.next();
		}
	}

	//Funcion para cargar datos a base
	function cargar(jQuery){
		var xx = $("#ejeX").val();
		var yy = $("#ejeY").val();

		var Valores= xx + ',' + yy;
  	$.ajax({
  		type:'POST',
  		url:'php/slide.php',
  		data:{
  			DatosRejilla : Valores
  		}
  	})
	}

});
