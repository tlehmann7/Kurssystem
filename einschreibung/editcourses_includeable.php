<?php
	include("../config/db.php");
	
	if($_SESSION['auth'])
	{
		if($_SESSION['type'] != $student_prefix)
		{
			$ref = new mysqli($db_host, $db_user, $db_password, $db_name);		
		
			if(!$ref->connect_error)
			{
				if($_SESSION['type'] == $teacher_prefix)
				{
					$query_string = "SELECT * FROM ".$db_table_courses." WHERE owner = \"".$_SESSION['user']."\"";
					$courses = $ref->query($query_string)->fetch_all(MYSQLI_ASSOC);
					
					if(count($courses) > 0)
					{
						for($i = 0; $i < count($courses); $i++)
						{
							$query_string = "SELECT * FROM ".$courses[$i]['ID'].$courseInfoPostfix.";";
							$projects = $ref->query($query_string)->fetch_all(MYSQLI_ASSOC);
							
							echo "<h1>".$courses[$i]['name']."</h1>".PHP_EOL;
							
							if(count($projects) > 0)
							{
								echo "<table>".PHP_EOL;
							
								echo "</table>".PHP_EOL;
							}
							else
								print_signal("Der Kurs hat keine Projekte");
						}
					}
					else
						print_signal("Sie besitzen keine Kurse");
				}
				else if($_SESSION['type'] == $admin_prefix)
				{
					$query_string = "SELECT * FROM ".$db_table_courses.";";
				}
			}
			else
				print_err($db_connection_error_msg);
		}
		else
			print_err("Schüler dürfen Kurse nicht ändern");
	}
	else
		print_err("Es wird eine Authentifikation benötigt");
?>
