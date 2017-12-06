<?php
	require_once("config/db.php");
	
	initSession();
	
	$printpwdwrong = false;
	
	if(!empty($_POST['username']) && !empty($_POST['password']))
	{
		$ref = new mysqli($db_host, $db_user, $db_password, $db_name);
		
		if(!$ref->connect_error)
		{
			$username = $ref->real_escape_string($_POST['username']);
			$password = $ref->real_escape_string($_POST['password']);
			
			$query_string = "SELECT username FROM ".$db_table_user." WHERE username = \"".$username."\" or email = \"".$username."\";";
			$realusername = $ref->query($query_string)->fetch_array(MYSQLI_NUM)[0];
			
			$query_string = "SELECT password FROM ".$db_table_user." WHERE username = \"".$realusername."\";";
			$pwd = $ref->query($query_string)->fetch_array(MYSQLI_NUM)[0];
		
			if($pwd == sha1($password))
			{
				$query_string = "SELECT type FROM ".$db_table_user." WHERE username = \"".$realusername."\";";
				$type = $ref->query($query_string)->fetch_array(MYSQLI_NUM)[0];
				$query_string = "SELECT class FROM ".$db_table_user." WHERE username = \"".$realusername."\";";
				$class = $ref->query($query_string)->fetch_array(MYSQLI_NUM)[0];
				$query_string = "SELECT alevel FROM ".$db_table_user." WHERE username = \"".$realusername."\";";
				$alevel = $ref->query($query_string)->fetch_array(MYSQLI_NUM)[0];
				$_SESSION['auth'] = TRUE;
				$_SESSION['user'] = $realusername;
				$_SESSION['type'] = $type;
				$_SESSION['alevel'] = $alevel;
				$_SESSION['class'] = $class;
				logAction($_SESSION['user'], array($log_login));
				
				reDir("?location=showcourses");
				
				if($type == $student_prefix)
				{
					echo "<noscript>".PHP_EOL;
					echo "<a href = \"?location=showcourses\">".PHP_EOL;
					echo "Schau was los ist".PHP_EOL;
					echo "</a>".PHP_EOL;
					echo "</noscript>".PHP_EOL;
				}
				else
				{
					echo "<noscript>".PHP_EOL;
					echo "<a href = \"?location=showcourses\">".PHP_EOL;
					echo "Schauen Sie was los ist".PHP_EOL;
					echo "</a>".PHP_EOL;
					echo "</noscript>".PHP_EOL;
				}
			}
			else
				$printpwdwrong = true;
		}
		else
			die($db_con_error_msg);
		
		$ref->close();
	}
	
	if(!$_SESSION['auth'])
	{
	?>
	<form method = "POST">
		<div id = "login-formwrapper">
			<div id = "login-labels">
				<label for = "username">Benutzername oder Email: </label><br>
				<label for = "password">Passwort: </label><br>
			</div>
			<div id = "login-inputfields">
				<input name = "username" type = "text"/><?php require_once("config/db.php"); if(isset($_POST['username'])) { if(empty($_POST['username'])) print_err("<-- wird zum Einloggen benötigt"); else if(!isGiven($db_table_user, "username", $_POST['username']) && !isGiven($db_table_user, "email", $_POST['username'])) print_err("<-- den Benutzernamen/die Email gibt es nicht"); } ?><br>
				<input name = "password" type = "password"/><?php if(isset($_POST['password'])) { if(empty($_POST['password'])) print_err("<-- wird zum Einloggen benötigt"); } ?>
			</div>
		</div>
		<input class = "submit" type = "submit" value = "Anmelden"/> oder <br><a href = "?location=forgotpw">Passwort vergessen</a><br><a href = "?location=register">Registrieren</a>
	</form>
	<?php
		if($printpwdwrong)
		{
			print_err("Das Passwort ist falsch");
		}
	}
?>
