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
			<label for = "email">Enter ur Username or Email Address: </label><input type = "email" name = "email"/>
			<input type = "submit" value = "Abschicken"/>
		</form>
	</body>
</html>
