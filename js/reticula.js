$(document).ready( function(){

$(".form-control").keyup(OnlyNumVal);
$("#YCoords").ready(MaxYCoords);
$("#XCoords").ready(MaxXCoords);
$("#YCoords").on("change",MaxYCoords);
$("#XCoords").on("change",MaxXCoords);
$("#YDotsSpace").ready(DotsSpace);
$("#XDotsSpace").ready(DotsSpace);
$("#YDotsSpace").on("change",DotsSpace);
$("#XDotsSpace").on("change",DotsSpace);
$("#YDots").ready(NumOfDots);
$("#XDots").ready(NumOfDots);
$("#YDots").on("change",NumOfDots);
$("#XDots").on("change",NumOfDots);
$("#DuplicateDotsY").ready(Placas);
$("#DuplicateDotsY").on("change",Placas);
$("#PlateState").on("change",Placas);
$("#ShowSlides").on("change",PointingTable);
$("#ShowPin").on("change",PointingTable);
/////////Función de Botón de Prueba
$(".update-db-submit").on("click",Datos);
/////////

	function MaxYCoords(){
		var s_temp = $(this).val();
		var f_temp = parseFloat(s_temp);
		if ( f_temp > 10)
			this.value=10;
		else if (s_temp == '')
			this.value=0;
		PointingTable();
	}

	function MaxXCoords(){
		var s_temp = $(this).val();
		var f_temp = parseFloat(s_temp);
		var tipo=$("h4").attr("id");
		if (tipo==44 && f_temp > 60)
			this.value=60;
		if (tipo==64 && f_temp > 50)
			this.value=50;
		if (tipo==84 && f_temp > 40)
			this.value=40;
		if (tipo==124 && f_temp > 20)
			this.value=20;
		else if (s_temp == '')
			this.value=0;
		PointingTable();
	}

	function NumOfDots(){
		if (this.value==0)
			this.value=1;
		var tipo=$(this).attr("id");
		if (tipo == "YDots"){
			var space=$("#YDotsSpace").val()*($(this).val()-1);
			if ((space+200)>4500){
				maxDots=Math.trunc(1+(4300/$("#YDotsSpace").val()));
				this.value=maxDots;
			}
		} else{
			var space=$("#XDotsSpace").val()*($(this).val()-1);
			if ((space+200)>4500){
				maxDots=Math.trunc(1+(4300/$("#XDotsSpace").val()));
				this.value=maxDots;
			}
		}
		var s_temp = $(this).val();
		if (s_temp == '')
			this.value=1;
		DuplicateDots();
		Placas();
	}

    function DotsSpace(){
		var s_temp = $(this).val();
		var f_temp = parseFloat(s_temp);
		if ( f_temp < 15)
			this.value=15;
		else if ( f_temp > 4300)
			this.value=4300;
		else if (s_temp == '')
			this.value=15;

			var tipo=$(this).attr("id");
		if (tipo == "YDotsSpace"){
			var space=$(this).val()*($("#YDots").val()-1);
			if ((space+200)>4500){
				maxDots=Math.trunc(1+(4300/$(this).val()));
				$("#YDots").val(maxDots);
				DuplicateDots();
			}
		}
		else {
			var space=$(this).val()*($("#XDots").val()-1);
			if ((space+200)>4500){
				maxDots=Math.trunc(1+(4300/$(this).val()));
				$("#XDots").val(maxDots);
			}
		}
		Placas();
	}

	function DuplicateDots(){
		$("#DuplicateDotsY").empty();
		var ini=$("hidden").attr("id");
		var temp = parseInt($("#YDots").val());
		for (var i=1;i<=temp;i++){
			var mult=temp%i;
			if (mult==0){
				if (ini!="" && i==ini)
					$option = $("<option selected></option>").attr("value",i).text(i);
				else
				$option = $("<option></option>").attr("value",i).text(i);
				$("#DuplicateDotsY").append($option);
			}
		}

	}

 	function OnlyNumVal(){
		var temp = $(this).val();
		temp = temp.replace(" ","");
		var tipo=$(this).attr("id");
		if (tipo == "YCoords" || tipo == "XCoords"){
			if(!$.isNumeric(temp))
			this.value = this.value.replace(/\D/g,'');
		}else
			this.value = this.value.replace(/\D/g,'');
	}

	function Placas(){
			var div=$("h4").attr("id");
			num=(div-4)/10;
			div=384;
			div=div*($("#DuplicateDotsY").val());
			var num=num*4*parseFloat(($("#XDots").val()))*parseFloat(($("#YDots").val()));
			var res=num/div;
			if ($("#PlateState").prop('checked') || res < 1)
				$("#NoPlates").val(res.toFixed(3));
			else
				$("#NoPlates").val(Math.trunc(res));
			PointingTable ();
	}

	function PointingTable(){
		if ($("#ShowSlides").prop('checked')){
			var temp=1;
			var val= parseInt($("#DuplicateDotsY").val());
			var xx=parseInt($("#XDotsSpace").val())/10;
			var yy=parseInt($("#YDotsSpace").val())/10;
			var x=10;
			var y=10;
			var xdots= parseInt($("#XDots").val());
			var ydots= parseInt($("#YDots").val());
			var radius = 1;
			var startAngle = 0;
			var endAngle = Math.PI * 2;
			var anticlockwise = false;

			var tab = "<p align='center'><font size='3' class='text-muted'>0";
			for (var i=0;i<40;i++)
				tab = tab + "&nbsp";
			tab = tab + "Puntos (" + $("#YDots").val() + "x" + $("#XDots").val() + ")";
			for (i=0;i<35;i++)
				tab = tab + "&nbsp";
			tab = tab + "X (4.5 mm)</font></p>";
			tab = tab + "<center><canvas id='FiguraSlide' width='450' height='450' style='border:1px solid grey;'></canvas></center>"
			tab = tab + "<p align='left'><font size='3' class='text-muted'>&nbsp&nbsp&nbsp&nbsp Y (4.5 mm)</font>"
			$("#Figura").html(tab);

			var canvas = document.getElementById('FiguraSlide');
			if (canvas.getContext) {
				var ctx = canvas.getContext('2d');

				for (var i=0; i<ydots; i++){
					for (var j=0; j<xdots; j++){
						ctx.beginPath();
						if (temp<=val){
							if (j%2==0)
								ctx.fillStyle = 'red';
							else ctx.fillStyle = 'blue';
						}
						else{
							if (j%2==0)
								ctx.fillStyle = 'black';
							else ctx.fillStyle = 'green';
						}
						if (xx<440 || yy<440)
							radius=3;
						if (xx<100 || yy<100)
							radius=2;
						if (xx<10 || yy<10)
							radius=1;
						ctx.arc(x, y, radius, startAngle, endAngle, anticlockwise);
						ctx.fill();
						x=x+xx;
					}
					y=y+yy;
					x=10;
					temp ++;
					if (temp>(val*2))
						temp=1;
				}
			}
		}
		else{
			var pines=$("h4").attr("id");
			pines=(pines-4)/10;
			var x=Math.round(6*$("#XCoords").val());
			var y=Math.round(6*$("#YCoords").val());
			var xn=x;
			var yn=y;
			var max=Math.trunc(1+(4300/parseInt($("#XDotsSpace").val())));
			var xx=(parseInt($("#XDots").val()))/max;
			xx = xx*4.5*6;
			var max=Math.trunc(1+(4300/parseInt($("#YDotsSpace").val())));
			var yy=(parseInt($("#YDots").val()))/max;
			yy = yy*4.5*6;
			var tab = "<p align='left'><font size='3' class='text-muted'>";
			for (var i=0;i<120;i++)
				 tab = tab + "&nbsp";
			tab = tab + "X (75 mm)</font></p>";
			tab = tab + "<center><canvas id='FiguraPines' width='473' height='173' style='border:1px solid grey;'></canvas></center>"
			tab = tab + "<p align='left'><font size='3' class='text-muted'>&nbsp&nbsp&nbsp&nbsp Y (25 mm)</font>"
			$("#Figura").html(tab);
			var canvas = document.getElementById('FiguraPines');
			if (canvas.getContext) {
				var ctx = canvas.getContext('2d');
				ctx.fillStyle = 'rgb(211, 216, 224)';
				for (var i=0;i<4;i++){
					for (var j=0;j<pines;j++){
						ctx.fillRect(xn+1, yn+1, xx, yy);
						xn=xn+(4.5*6)+1;
					}
					xn=x;
					yn=yn+(4.5*6)+1;
				}
			}
		}
	}

	function Datos()
  {
  	if ($("#PlateState").prop('checked'))
  		var Checked=1;
  	else
      var Checked=0;

  	var Valores=$("#YCoords").val()+","+$("#XCoords").val()+","+$("#YDotsSpace").val()+","+$("#XDotsSpace").val();
  	Valores=Valores+","+$("#YDots").val()+","+$("#XDots").val()+","+$("#DuplicateDotsY").val()+","+Checked+","+$("#NoPlates").val();
  	$.ajax({
  		type:'POST',
  		url:'php/reticula.php',
  		data:{
  			DatosPlaca:Valores
  		}
  	})
  }

});
