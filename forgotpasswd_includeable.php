<?php
	require_once("config/db.php");
	
	if(isset($_POST['email']))
	{
		// Change
		$success = RequestPwdReset($_POST['email']);
	}
?>
<form method = "post" id = "form">
	<h2>E-Mail für Passwortänderung</h2>
	<label for = "email">E-Adresse oder Benutzername: </label><input name = "email" type = "text"/>
	<input type = "hidden" value = "forgotpw" name = "location"/>
	<input type = "submit" value = "Abschicken"/>
	<br>
	<?php
		if($success && isset($_POST['email']))
		{
			print_success("Es wurde eine E-Mail versandt, um das Passwort zurückzusetzen");
		}
		else if(isset($_POST['email']))
			print_err("Konnte E-Mail nicht versenden");
	?>
</form>
