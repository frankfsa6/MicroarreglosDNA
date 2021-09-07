// Variable para guardar valor anterior
var anteVal;
// Valida coordenadas 
function validaCoords(){
	var temp = parseFloat($(this).val());
	// Manda error si no cumple características
	if(temp>25 || temp<3 || !$.isNumeric(temp)){
	  $("#error").text("Sólo se aceptan coordenadas numéricas entre 3 y 25 mm").show();
	  $(this).val(anteVal);
	  document.documentElement.scrollTop = 0;
	}
	else{
	  $("#error").empty().hide();
	  $(this).val( temp.toFixed(3) );
	  coordTabla();
	}
}
// Actualiza coordenadas predeterminadas
function actualizaCoords(){
	if( $("#seccionNum").val() == 0 )
		$("#coordXNums").val("4.500");
	else
		$("#coordXNums").val("22.640");
	$("#coordYNums").val("6.500");
	coordTabla();
}
// Dibuja 6 pines en canvas del cabezal
function pinTabla (){
	var xx = 30;
	var yy = 30;
	var radius = 7;
	var startAngle = 0;
	var endAngle = Math.PI * 2;
	var anticlockwise = false;
	// Dibuja cabezal de pines
	var tab = "<p align='center'><font size='3' class='text-muted'>Pin 1";
	for (i=0;i<100;i++)
		tab += "&nbsp";
	tab += "X</font></p> <center><canvas id='FigPines' width='500' height='180' style='border:1px solid grey; background:rgb(255,230,0);'></canvas></center> <p align='left'><font size='3' class='text-muted'>&nbsp&nbsp&nbsp Y</font>";
	$("#tablaPines").html(tab);
	// Marca rellenos y contornos de los círculos
	var canvas = document.getElementById('FigPines');
	if (canvas.getContext) {
		var ctx = canvas.getContext('2d');
		ctx.fillStyle = 'rgb(161,161,161)';
		ctx.strokeStyle = 'rgb(200,200,200)';
		for (var i=0;i<4;i++){
			for (var j=0;j<12;j++){
				ctx.beginPath();
				ctx.arc(xx, yy, radius, startAngle, endAngle, anticlockwise);
				ctx.stroke();
				// Decide rellenar sólo 6
				if( i == 0 && j%2 == 0 && j<=10 )
					ctx.fill();
				xx += 40;
			}
			xx = 30;
			yy += 40;
		}
	}
}
// Dibuja recuadro para slide
function coordTabla(){
	var XCoord = 10/3*2*parseFloat($("#coordXNums").val());
	var YCoord = 10/3*2*parseFloat($("#coordYNums").val());
	var secNum = $("#seccionNum").val();
	var tamCuadrito = 20;
	// Contorno del slide
	var tab = "<p align='center'><font size='3' class='text-muted'>Coordenada inicial";
	for (i=0;i<80;i++)
		tab += "&nbsp";
	tab += "X (75 mm)</font></p> <center><canvas id='figCoordenadas' width='500' height='166' style='border:1px solid grey; background:rgb(255,255,255);'></canvas></center> <p align='left'><font size='3' class='text-muted'>&nbsp&nbsp&nbsp Y (25 mm)</font>";
	$("#tablaCoordenadas").html(tab);
	// Dibuja recuadros
	var canvas = document.getElementById('figCoordenadas');
	if (canvas.getContext) {
		var ctx = canvas.getContext('2d');
		ctx.fillStyle = 'rgb(161,161,161)';
		// Primera fila de cuadritos
		for(var i=0; i<6; i++){
			ctx.fillRect(XCoord, YCoord, tamCuadrito, tamCuadrito);
			XCoord += 60;
		}
		// Dibuja segunda fila si se necesita
		if(secNum == 1){
			ctx.font = "20px Georgia";
			ctx.fillText("Girado 180°", 20, 90);
			XCoord = 150;
			YCoord = 103;
			for(var i=0; i<6; i++){
				ctx.strokeRect(XCoord, YCoord, tamCuadrito, tamCuadrito);
				XCoord += 60;
			}
		}
	}
}
// Dibuja cuadritos de la retícula
function slideTabla(){
	var x = $("#xSlidesNumeros").val();
	var y = $("#ySlidesNumeros").val();
	var fila = $("#casillas>tbody").children().first();
	$("#slidesTot").val(x*y);
	// Contorno de slides
	for(var i=1; i<=10; i++){
		celda = fila.children().first();
		celda.css("background-color", "#ffffff");
		for(var j=1; j<=5; j++)
			celda = celda.next().css("background-color", "#ffffff");
		fila = fila.next();
	}
	// Colorea slides
	fila = $("#casillas>tbody").children().first();
	for(var i=1; i<=y; i++){
		celda = fila.children().first();
		celda.css("background-color", "#c3c3c3");
		for(var j=2; j<=x; j++)
			celda = celda.next().css("background-color", "#c3c3c3");
		fila = fila.next();
	}
}
// Al iniciar página
$(document).ready( function(){
	$("#info").empty().hide();
	$("#xSlidesNumeros, #ySlidesNumeros").on("change", slideTabla);
	$("#seccionNum").on("change", actualizaCoords);
	$(".coordsXY").on("change", validaCoords);
	$(".coordsXY").on("click", function(){
		anteVal = $(this).val();
	});
	pinTabla();
	slideTabla();
	coordTabla();
});
