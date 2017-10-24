<?php
	require_once("config/db.php");
	
	initSession();
	
	if($_SESSION['auth'] && $_POST['npasswd'])
	{
		$ref = new mysqli($db_host, $db_user, $db_password, $db_name);
		
		if(!$ref->connect_error)
		{
			$newpasswd = $ref->real_escape_string($_POST['npasswd']);
			
			$query_string = "UPDATE ".$db_table_users." SET password = \"".sha1($newpasswd)."\" WHERE username = \"".$_SESSION['user']."\";";
			if($ref->query($query_string))
				print_success("Das Passwort wurde erfolgreich geändert.");
		}
		
		$ref->close();
	}
	else if($_SESSION['auth'])
	{
		?>
			<h1>Passwortänderung</h1>
			<div id = "inline-wrapper">
				<div id = "changepwddiv">
					<form method = "POST">
						<input type = "text" name = "npasswd" id = "p1"/>
						<input type = "text" id = "p2"/>
						<input type = "hidden" name = "location" value = "changepw"/>
						<input type = "submit" value = "Fertig"/>
					</form>
				</div>
				<div id = "tellpwnoteq">
				</div>
			</div>
		<?php
	}
	else if(isset($_POST['pwdresetkey']) && isset($_POST['npasswd']))
	{
		$ref = new mysqli($db_host, $db_user, $db_password, $db_name);
		
		if(!$ref->connect_error)
		{
			$newpasswd = $ref->real_escape_string($_POST['npasswd']);
			$rkey = $ref->real_escape_string($_POST['pwdresetkey']);
			
			$query_string = "UPDATE ".$db_table_users." SET password = \"".sha1($newpasswd)."\" WHERE pwdresetkey = \"".$rkey."\";";
			if($ref->query($query_string))
				print_success("Das Passwort wurde erfolgreich geändert.");
		}
		
		$ref->close();
	}
	else if(isset($_GET['pwdresetkey']))
	{
		$ref = new mysqli($db_host, $db_user, $db_password, $db_name);
		
		if(!$ref->connect_error)
		{
			$rkey = $ref->real_escape_string($_GET['pwdresetkey']);
			$query_string = "SELECT username FROM ".$db_table_users." WHERE pwdresetkey = \"".$rkey."\";";
			
			$username = $ref->query($query_string);
			
			print_normal("Neues Passwort für ".$username);
			echo "<div id = \"changepwddiv\">".PHP_EOL;
			echo "<form method = \"POST\">".PHP_EOL;
			echo "<input type = \"text\" name = \"npasswd\" id = \"p1\"/>".PHP_EOL;
			echo "<input type = \"text\" id = \"p2\"/>".PHP_EOL;
			echo "<input type = \"hidden\" name = \"pwdresetkey\" value = \"".$rkey."\"/>".PHP_EOL;
			echo "<input type = \"hidden\" name = \"location\" value = \"changepw\"/>".PHP_EOL;
			echo "<input type = \"submit\" value = \"Fertig\"/>".PHP_EOL;
			echo "</form>".PHP_EOL;
			echo "<div>".PHP_EOL;
			echo "<div id = \"tellpwnoteq\">".PHP_EOL;
			echo "</div>".PHP_EOL;
		}
		
		$ref->close();
	}
?>