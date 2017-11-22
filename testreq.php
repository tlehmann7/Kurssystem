<?php
	require_once("config/db.php");
	
	if(isset($_GET['email']))
	{
		RequestPwdReset($_GET['email']);
	}
?>
<!DOCTYPE html>
<html>
	<head>
		<meta charset = "UTF-8"/>
	</head>
	<body>
		<form method = "GET">
			<label for = "email">Gebe deinen Username oder deine E-Mail Adresse ein: </label><input type = "email" name = "email"/>
			<input type = "submit" value = "Abschicken"/>
		</form> 
		<br>
		Bitte schaue auch in deinem Spam-Ordner nach.
	</body>
</html>
