<script type="text/javascript" src="js/login.js?v=4"></script>
<?php
	//	se define los campos de html
	session_name("IFCLab");
	session_start();
	if(!isset($_SESSION['login'])){
	echo "
		<form autocomplete='off' id='formLogin'>
		<div class='input-group flex-nowrap'>
			<div class='input-group-prepend'>
				<span class='input-group-text' id='addon-wrapping'>Contrase&ntilde;a</span>
			</div>
			<input id='Pass' type='password'  maxlength='20' class='form-control' placeholder='Contraseña' aria-label='Username' aria-describedby='addon-wrapping'>
		</div>
		</br><center>
		<button type='button' class='btn btn-info btn-lg' id='Entrar'> Entrar </button>
		</center>
		</form>
		</br>
		<div class='alert alert-danger' role='alert' id='alertaLogin' hidden>
		</div>
		";
	}
	else
		include 'config.php';
	session_write_close();
?>
