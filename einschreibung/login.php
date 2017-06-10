<?php
	require_once("../config/db.php");
	initSession();
?>
<!DOCTYPE html>
<html>
	<head>
		<title>Login</title>
		<meta charset = "UTF-8"/>
		<link rel = "stylesheet" href = "../style/main.css"/>
		<link rel = "stylesheet" href = "../style/input.css"/>
		<link rel = "stylesheet" href = "../style/course.css"/>
	</head>
	<body>
		<div class = "wrapper70">
			<form method = "POST">
				<?php
					require_once("../config/db.php");
					if(!$_SESSION['auth'])
					{
					?>
						<label for = "username">Benutzername oder Email: </label><input name = "username" type = "text"/><?php require_once("../config/db.php"); if(isset($_POST['username'])) { if(empty($_POST['username'])) echo "<span class = \"Error\"><-- wird zum Einloggen benötigt</span>".PHP_EOL; else if(!isGiven($db_table_user, "username", $_POST['username']) && !isGiven($db_table_user, "email", $_POST['username'])) echo "<span class = \"Error\"><-- den Benutzernamen/die Email gibt es nicht</span>".PHP_EOL; } ?>
						<br>
						<label for = "password">Passwort: </label><input name = "password" type = "password"/><?php if(isset($_POST['password'])) { if(empty($_POST['password'])) echo "<span class \"Error\"><-- wird zum Einloggen benötigt</span>"; } ?>
						<br>
						<input type = "submit"/>
						<br>
					<?php
					}
					
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
								$_SESSION['class'] = strtoupper($class);
								if($type == $student_prefix)
									print_success("Du bist jetzt angemeldet");
								else
									print_success("Sie sind jetzt angemeldet");
							}
							else
								print_err("Das Passwort ist falsch");
						}
						else
							die($db_con_error_msg);
						
						$ref->close();
					}
				?>
			</form>
		</div>
	</body>
</html>