$(document).ready( function(){
$("#Pines_ejeX").ready(PinTable);
$("#Pines_ejeX").on("change",PinTable);
$("#NoPines").ready(MultPines);
$("#Pines_ejeX").on("change",MultPines);
/////////Función de Botón de Prueba
$(".update-db-submit").on("click",Datos);
/////////

function MultPines (jQuery){
	var Mult=4*($("#Pines_ejeX").val());
	$("#NoPines").val(Mult);
}
function PinTable (jQuery){
		var XPines= parseInt($("#Pines_ejeX").val());
		var xx=30;
		var yy=30;
		var radius=6;
		var startAngle = 0;
		var endAngle = Math.PI * 2;
		var anticlockwise = false;
		var tab = "<p align='center'><font size='3' class='text-muted'>Pin 1";
		for (i=0;i<100;i++)
				tab = tab + "&nbsp";
			tab= tab + "X</font></p>";
			tab = tab + "<center><canvas id='FigPines' width='500' height='180' style='border:1px solid grey; background:rgb(255,230,0);'></canvas></center>"
			tab = tab + "<p align='left'><font size='3' class='text-muted'>&nbsp&nbsp&nbsp Y</font>";
			$("#Figura").html(tab);
		var canvas = document.getElementById('FigPines');
			if (canvas.getContext) {
				var ctx = canvas.getContext('2d');
				ctx.fillStyle = 'rgb(161,161,161)';
				for (var i=0;i<4;i++){
					for (var j=0;j<XPines;j++){
						ctx.beginPath();
						ctx.arc(xx, yy, radius, startAngle, endAngle, anticlockwise);
						ctx.fill();
						xx=xx+40;
					}
					xx=30;
					yy=yy+40;
				}
			}
		}
function Datos (jQuery)
{
	var NoPines=$("#Pines_ejeX").val();
	$.ajax({
		type:'POST',
		url:'php/pines.php',
		data:{
			DatosPines : NoPines
		}
	})
}
});
