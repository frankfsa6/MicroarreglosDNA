$(document).ready( function(){
	$("#botones").hide();
	$("#info").empty().hide();
	$( "#Entrar" ).click(function() {
		$.ajax({
			type:'POST',
			url:'php/LSArch.php',
			data:{
				confirmLogin: true,
				Contrasena : $("#Pass").val()
			},
			success: function(data){
				cadena = String(data);
				$("#Pass").val("");
				if(cadena == '0')
				{
					$.post("php/config.php", function(datos){
						$("#info").empty().hide();
						$("#error").empty().hide();
						$("#pags").html(datos);
					}).fail(function(){
						$("#info, #pags, #botones").empty();
						$("#info").hide();
						$("#error").text("No se complet? la petici?n de p?gina").show();
					});
				}
				else
				{
					$("#alertaLogin").removeAttr('hidden');
					$("#alertaLogin").html(data);
				}
			}
		});
	});
	$( "a" ).on("click", function() {
		var pags = $(this).attr("id");
		if(pags != "login"){
			$("#botones").show();
		}
	});
	$("#Pass").keypress(function (event) {
		var keycode = (event.keyCode ? event.keyCode : event.which);
		if(keycode == '13'){
			 event.preventDefault();
			$("#Entrar").trigger("click");
		}
	});


  });
