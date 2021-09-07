$(document).ready( function(){
	PinTable();
	slideTabla();
	$("#coordenadaX").val("5.000");
	$("#coordenadaY").val("5.000");
	$("#info").empty().hide();
	$("#PinesNumeros_ejeX").on("change", pinesTotales);
	$("#PinesNumeros_ejeY").on("change", pinesTotales);
	$("#xSlidesNumeros").on("change", slidesTotales);
	$("#ySlidesNumeros").on("change", slidesTotales);
	$("#PinesXDobles").on("change",pinesTotales);


	//funciones agregadas
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

	function pinesTotales(){
		var pinesTotales = $("#PinesNumeros_ejeX").val() * $("#PinesNumeros_ejeY").val() * (parseInt($("#PinesXDobles").val())+1);
		$("#PinesNumeros_totales").val(pinesTotales);
		PinTable();
		$("#XCoords").trigger("change");
		PointingTable();
	}

	function slidesTotales(){
		var slidesTotales = $("#xSlidesNumeros").val() * $("#ySlidesNumeros").val();
		$("#SlidesNumeros_totales").val(slidesTotales);
		slideTabla();
	}
	// Dibuja cabezal de pines
	function PinTable(){
		var XPines= parseInt($("#PinesNumeros_ejeX").val());
		var YPines= parseInt($("#PinesNumeros_ejeY").val());
		var xx=30;
		var yy=30;
		var radius=7;
		var startAngle = 0;
		var endAngle = Math.PI * 2;
		var anticlockwise = false;
		var tab = "<p align='center'><font size='3' class='text-muted'>Pin 1";
		for (i=0;i<100;i++)
				tab = tab + "&nbsp";
			tab= tab + "X</font></p>";
			tab = tab + "<center><canvas id='FigPines' width='500' height='180' style='border:1px solid grey; background:rgb(255,230,0);'></canvas></center>"
			tab = tab + "<p align='left'><font size='3' class='text-muted'>&nbsp&nbsp&nbsp Y</font>";
			$("#tablaPines").html(tab);
		var canvas = document.getElementById('FigPines');
			if (canvas.getContext) {
				var ctx = canvas.getContext('2d');
				ctx.fillStyle = 'rgb(161,161,161)';
				ctx.strokeStyle = 'rgb(200,200,200)';
				for (var i = 0 ; i < YPines ; i++){
					for (var j = 0 ; j < XPines ; j++){
						ctx.beginPath();
						ctx.arc(xx, yy, radius, startAngle, endAngle, anticlockwise);
						ctx.fill();
						xx += 80;
					}
					xx = 30;
					yy += 80;
				}
				var xx=30;
				var yy=30;
				for (var i = 0 ; i < 4 ; i++){
					for (var j = 0 ; j < 12 ; j++){
						ctx.beginPath();
						ctx.arc(xx, yy, radius, startAngle, endAngle, anticlockwise);
						ctx.stroke();
						xx += 40;
					}
					xx = 30;
					yy += 40;
				}
			}
		}
	// Colorea cuadritos de slide
	function slideTabla(){
		var x = $("#xSlidesNumeros").val();
		var y = $("#ySlidesNumeros").val();
		var fila = $("#casillas>tbody").children().first();

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
	//Funciones agregadas de reticula
	function MaxYCoords(){
		var s_temp = $(this).val();
		var f_temp = parseFloat(s_temp);

		var max=Math.trunc(1+(6800/parseInt($("#YDotsSpace").val())));
		var space=(parseInt($("#YDots").val()))/max;
		space = Math.ceil(space*7);
		cuadros = 25 - ( 9+space );

		if ( f_temp > cuadros)
			this.value=cuadros;
		else if (s_temp == '')
			this.value=0;
		PointingTable();
	}
	
	function MaxXCoords(){
		var s_temp = $(this).val();
		var f_temp = parseFloat(s_temp);
		var tipo = parseInt($("#PinesNumeros_ejeX").val()*(parseInt($("#PinesXDobles").val())+1));
		
		//Realiza una equivalencia del tamaño de los cuadros en el slide
		var max=Math.trunc(1+(6800/parseInt($("#XDotsSpace").val())));
		var space=(parseInt($("#XDots").val()))/max;
		space = Math.ceil(space*7);

		if (tipo == 4){
			cuadros = 75 - (27 + space);
			if(f_temp > cuadros)
				this.value = cuadros;
		}
		if (tipo==8){
			cuadros = 75 - (63 + space);
			if(f_temp > cuadros)
				this.value = cuadros;
		}
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
			if ((space+200)>7000){
				maxDots=Math.trunc(1+(6800/$("#YDotsSpace").val()));
				this.value=maxDots;
			}
		} else{
			var space=$("#XDotsSpace").val()*($(this).val()-1);
			if ((space+200)>7000){
				maxDots=Math.trunc(1+(6800/$("#XDotsSpace").val()));
				this.value=maxDots;
			}
		}
		var s_temp = $(this).val();
		if (s_temp == '')
			this.value=1;
		DuplicateDots();
		Placas();
		$("#YCoords").trigger("change");
		$("#XCoords").trigger("change");
	}
	
	function DotsSpace(){
		var s_temp = $(this).val();
		var f_temp = parseFloat(s_temp);
		if ( f_temp < 130)
			this.value=130;
		else if ( f_temp > 6800)
			this.value=6800;
		else if (s_temp == '')
			this.value=130;
		
		

		var tipo=$(this).attr("id");
		if (tipo == "YDotsSpace"){
			var space=$(this).val()*($("#YDots").val()-1);
			if ((space+200)>7000){
				maxDots=Math.trunc(1+(6800/$(this).val()));
				$("#YDots").val(maxDots);
				DuplicateDots();
			}
		}
		else {
			var space=$(this).val()*($("#XDots").val()-1);
			if ((space+200)>7000){
				maxDots=Math.trunc(1+(6800/$(this).val()));
				$("#XDots").val(maxDots);
			}
		}
		Placas();
		$("#YCoords").trigger("change");
		$("#XCoords").trigger("change");
	}
	
	function DuplicateDots(){
		$("#DuplicateDotsY").empty();
		var ini=1;
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
			// Numero de muestras en las que se entra
			var div = 12;
			div=div*($("#DuplicateDotsY").val());
			var num = parseFloat(($("#XDots").val()))*parseFloat(($("#YDots").val()));
			var res = num/div;
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
			var xx=parseInt($("#XDotsSpace").val())/14.28;
			var yy=parseInt($("#YDotsSpace").val())/14.28;
			var x=7;
			var y=7;
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
			tab = tab + "X (7 mm)</font></p>";
			//Cada pixel es igual a 490/7mm -> 70 pixeles por mm
			tab = tab + "<center><canvas id='FiguraSlide' width='490' height='490' style='border:1px solid grey;'></canvas></center>";
			tab = tab + "<p align='left'><font size='3' class='text-muted'>&nbsp&nbsp&nbsp&nbsp Y (7 mm)</font>";
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
					x=7;
					temp ++;
					if (temp>(val*2))
						temp=1;
				}
			}
		}
		else{
			var pines = parseInt($("#PinesNumeros_ejeX").val()*(parseInt($("#PinesXDobles").val())+1));
			var escalar = 7; // Guarda la relacion entre los pixeles y los milimetros 525 / 75 = 7
			var x=Math.round(escalar*$("#XCoords").val());
			var y=Math.round(escalar*$("#YCoords").val());
			var xn=x;
			var yn=y;
			var max=Math.trunc(1+(6800/parseInt($("#XDotsSpace").val())));
			var xx=(parseInt($("#XDots").val()))/max;
			xx = Math.round(xx*7*escalar);
			var max=Math.trunc(1+(6800/parseInt($("#YDotsSpace").val())));
			var yy=(parseInt($("#YDots").val()))/max;
			yy = Math.round(yy*7*escalar);
			var tab = "<p align='left'><font size='3' class='text-muted'>Slide / vidrio individual";
			for (var i=0;i<80;i++)
					tab = tab + "&nbsp";
			tab = tab + "X (75 mm)</font></p>";
			tab = tab + "<center><canvas id='FiguraPines' width='530' height='178' style='border:1px solid grey;'></canvas></center>"
			tab = tab + "<p align='left'><font size='3' class='text-muted'>&nbsp&nbsp&nbsp&nbsp Y (25 mm)</font>"
			$("#Figura").html(tab);
			var canvas = document.getElementById('FiguraPines');
			if (canvas.getContext) {
				var ctx = canvas.getContext('2d');
				ctx.fillStyle = 'rgb(211, 216, 224)';
				for (var i=0;i<2;i++){
					for (var j=0;j<pines;j++){
						ctx.fillRect(xn+1, yn+1, xx, yy);
						xn=Math.round(xn+(9*escalar)+1);
					}
					xn=x;
					yn=Math.round(yn+(9*escalar)+1);
				}
			}
		}
	}

});
