$(document).ready(function(){
$(".form-control").keyup(OnlyNumVal);
//$(".form-control").ready(OnlyNumVal);
$(".form-control").on("change",MinVal);
$(".update-db-submit").on("click",cargar);

	//Error al usar esta funcion (?)
	function OnlyNumVal( jQuery ){
		this.value = this.value.replace(/\D/g,'');
	}

	function MinVal( jQuery ){
		if (this.value == '' || this.value == 0 )
			this.value = 1;
	}

	function cargar(jQuery){
		var ciclos = $("#CicloLavado").val();
		var oscilaciones = $("#OscilacionesLavado").val();
		var toques = $("#LimpiezaPines").val();
		var vacio = $("#TiempoVacio").val();
		var uvacio = $("#UltimoVacio").val();
		var tmuestra = $("#TiempoMuestra").val();
		var Valores= ciclos + ',' +  oscilaciones + ',' + toques + ',' + vacio + ',' + uvacio + ',' + tmuestra;
  	$.ajax({
  		type:'POST',
  		url:'php/lavado.php',
  		data:{
  			DatosLavado:Valores
  		}
  	})
	}
});
