<?php
	require_once("config/db.php");

	if($_SESSION['auth'])
	{
		if($_SESSION['type'] == $student_prefix)
		{
			$ref = new mysqli($db_host, $db_user, $db_password, $db_name);
			if(!$ref->connect_error)
			{
				$foundSomething = FALSE;
				$query_string = "SELECT ID, name FROM ".$db_table_courses.";";
				$courseIDs = $ref->query($query_string)->fetch_all(MYSQLI_ASSOC);
				
				for($i = 0; $i < count($courseIDs); $i++)
				{
					$query_string = "SELECT ID, name FROM ".$courseIDs[$i]['ID'].$courseInfoPostfix.";";
					$projIDs = $ref->query($query_string)->fetch_all(MYSQLI_ASSOC);
					
					for($p = 0; $p < count($projIDs); $p++)
					{
						if(isGiven($courseIDs[$i]['ID']."_".$projIDs[$p]['ID'].$projectSignUpPostfix, "username", $_SESSION['user']))
						{
							if(!$foundSomething)
							{
								$foundSomething = TRUE;
								echo "<h1>Deine Teilnahmen</h1>".PHP_EOL;
								echo "<table>".PHP_EOL;
								echo "<tr><th width = \"35%\">Kurs</th><th width = \"35%\">Projekt</th></tr>".PHP_EOL;
							}
							echo "<tr><td>".$courseIDs[$i]['name']."</td><td>".$projIDs[$p]['name']."</td><tr>".PHP_EOL;
						}
					}
				}
				
				if(!$foundSomething)
					print_signal("Du bist nirgends eingeschrieben");
				else
				{
					echo "</table><br>".PHP_EOL;
				}
			}
			else
				print_err($db_connection_error_msg);
			
			$ref->close();
		}
		else if($_SESSION['type'] == $teacher_prefix or $_SESSION['type'] == $admin_prefix)
		{
			print_err("Sie müssen Schüler sein um ihre Kurse einzusehen");
		}
	}
	else
		messageUser($auth_required_msg);
?>
