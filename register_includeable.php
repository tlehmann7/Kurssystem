<form method = "POST">
	<div id = "register-formwrapper">
		<div id = "register-labels">
			<label for = "authkey">Authentifikationscode: </label><br>
			<label for = "vname">Vorname: </label><br>
			<label for = "nname">Nachname: </label><br>
			<label for = "password">Passwort: </label><br>
			<label for = "password_eq">Passwort bestätigen: </label><br>
			<label for = "email">E-Mail: </label><br>
		</div>
		<div id = "register-inputfields">
			<input name = "authkey" type = "text" maxlength = <?php require_once("config/db.php"); echo "\"".$key_length."\""; ?> id = "authkey" onkeyup = "upper(this.id);" required/><span id = "authkey-info"></span><?php require_once("config/db.php"); if(isset($_POST['authkey'])) { if(!isGiven($db_table_num, "AUTHNUM", $_POST['authkey'])) print_err("<-- Es gibt diese Nummer nicht"); } ?><br>
			<input name = "vname" type = "text" required/><br>
			<input name = "nname" type = "text" required/><br>
			<input id = "p1" name = "password" type = "password" onchange = "checkifequal('p1', this.id, 'tellpwnoteq');" required/><?php require_once("config/db.php"); if(isset($_POST['password'])) { if(strlen($_POST['password']) < $pw_min_length) print_err("<-- Das Passwort hat mindestens ".$pw_min_length." Zeichen lang zu sein"); } ?><br>
			<input name = "password_eq" id = "p2" type = "password" onchange = "checkifequal(this.id, 'p2', 'tellpwnoteq');" required/><span class = "<?php echo $error_class; ?>" id = "tellpwnoteq"></span><br>
			<input name = "email" type = "e-mail" required/><?php require_once("config/db.php"); if(isset($_POST['email'])) { if(empty(filter_var($_POST['email'], FILTER_VALIDATE_EMAIL))) print_err("<-- Dies ist keine richtige E-Mail-Addresse"); else if(isGiven($db_table_user, "email", $_POST['email'])) print_err("<-- Es ist bereits ein User mit dieser E-Mail registriert"); } ?><br>
		</div>
		<br>
		<input class = "submit" type = "submit" value = "Registrieren"/>
	</div>
</form>
<?php
	require_once("config/db.php");
	
	if(!empty($_POST['authkey']) && !empty($_POST['vname']) && !empty($_POST['nname']) && !empty($_POST['password']) && strlen($_POST['password']) >= $pw_min_length && !empty(filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) && !isGiven($db_table_user, "email", $_POST['email']))
	{
		$ref = new mysqli($db_host, $db_user, $db_password, $db_name);
		if(!$ref->connect_error)
		{
			// Set Vars
			$authkey = $ref->real_escape_string($_POST['authkey']);
			$vname = $ref->real_escape_string($_POST['vname']);
			$nname = $ref->real_escape_string($_POST['nname']);
			$password = $ref->real_escape_string($_POST['password']);
			$email = $ref->real_escape_string($_POST['email']);
			
			$type = null;
			$alevel = null;
			$username = null;
			$class = null;
			
			// Only go ahead if the Number is given
			if(isGiven($db_table_num, "AUTHNUM", $authkey) && !isGiven($db_table_user, "email", $email))
			{
				// Build the Username
				$query_string = "SELECT type, class, alevel FROM ".$db_table_num." WHERE AUTHNUM = \"".$authkey."\";";
				$keyspecs = $ref->query($query_string)->fetch_array(MYSQLI_ASSOC);
				
				$type = $keyspecs['type'];
				
				if($type == $student_prefix)
				{
					$class = $keyspecs['class'];
					$alevel = $keyspecs['alevel'];
					
					$username = $type.$alevel.trimName($nname).trimName($vname)[0];
				}
				else
				{
					$username = $type.trimName($nname).trimName($vname)[0];
				}
				
				// If the username is already given
				$postfix = "";
				$counter = 2;
				while(isGiven($db_table_user, "username", $username.$postfix))
				{
					$postfix = strval($counter);
					$counter++;
				}
				$username = $username.$postfix;
				
				// Insert all information in database + delete the authkey
				if($type == $student_prefix)
					$query_string = "INSERT INTO ".$db_table_user."(username, password, vname, nname, email, alevel, type, class) values(\"".strtolower($username)."\", \"".sha1($password)."\", \"".$vname."\", \"".$nname."\", \"".$email."\", \"".$alevel."\", \"".$type."\", \"".$class."\");";
				else if($type != $student_prefix)
					$query_string = "INSERT INTO ".$db_table_user."(username, password, vname, nname, email, type) values(\"".strtolower($username)."\", \"".sha1($password)."\", \"".$vname."\", \"".$nname."\", \"".$email."\", \"".$type."\");";
				
				if($ref->query($query_string))
				{
					$query_string = "DELETE FROM ".$db_table_num." WHERE AUTHNUM = \"".$authkey."\";";
					$ref->query($query_string);
					$ref->close();
				
					// Give feedback to the user
					print_success("Erfolgreich registriert");
					echo "<br>";
					logAction(strtolower($username), array($log_register));
					if($counter > 2)
					{
						switch($type)
						{
							case $student_prefix:
								print_signal("Da dein generierter Benutzername bereits vergeben ist".PHP_EOL."lautet deiner jetzt ".$username);
							break;
							case $admin_prefix:
							case $teacher_prefix:
								print_signal("Da ihr generierter Benutzername bereits vergeben ist".PHP_EOL."lautet ihrer jetzt ".$username);
							break;
						}
					}
					else
					{
						switch($type)
						{
							case $student_prefix:
								print_normal("Dein Benutzername lautet ".$username);
							break;
							case $admin_prefix:
							case $teacher_prefix:
								print_normal("Ihr Benutzername lautet ".$username);
							break;
						}
					}
				}
				else
					die($db_query_error_msg);
			}
		}
		else
		{
			die($db_con_error_msg);
		}
		$ref->close();
	}
?>
