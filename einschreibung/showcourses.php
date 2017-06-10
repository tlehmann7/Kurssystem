<?php
	require_once("../config/db.php");
	initSession();
?>
<!DOCTYPE html>
<html>
	<head>
		<meta charset = "UTF-8"/>
		<title>Kurssystem</title>
		<link rel = "stylesheet" href = "../style/main.css"/>
		<link rel = "stylesheet" href = "../style/input.css"/>
		<link rel = "stylesheet" href = "../style/course.css"/>
	</head>
	<body>
		<div class = "wrapper70">
			<?php
				require_once("../config/db.php");
				
				if($_SESSION['auth'] or $dauth)
				{
					$ref = new mysqli($db_host, $db_user, $db_password, $db_name);
					
					$query_string = "SELECT type FROM ".$db_table_user." WHERE username = \"".$_SESSION['user']."\";";
					
					$type = $ref->query($query_string)->fetch_array(MYSQLI_NUM)[0];
					
					if($type != $student_prefix)
					{
						$query_string = "SELECT * FROM ".$db_table_courses.";";
						$courses = $ref->query($query_string)->fetch_all(MYSQLI_ASSOC);
						if($type == $admin_prefix or $ok)
						{
							echo "<table>".PHP_EOL;
							echo "<tr><th width = \"12%\">Name</th><th width = \"27%\">Veröffentlichung</th><th width = \"27%\">Beendigung</th><th width = \"16%\">Besitzer</th><th width = \"18%\">Erlaubte Jahrgänge</th></tr>".PHP_EOL;
							for($i = 0; $i < count($courses); $i++)
							{
								echo "<tr><td>".$courses[$i]['name'].
									 "</td><td>".date("d.m.Y/H:i:s", $courses[$i]['reldate']).
									 "</td><td>".date("d.m.Y/H:i:s", $courses[$i]['reldate']).
									 "</td><td>".$courses[$i]['owner'].
									 "</td>";
								
								echo "<td>";
								
								$explodedArray = explode(";", $courses[$i]['allowed']);
								for($p = 0; $p < count($explodedArray); $p++)
								{
									for($o = 0; $o < count(explode("#", $explodedArray[$p])); $o++)
									{
										echo explode("#", $explodedArray[$p])[$o];
										echo "<br>";
									}
								}
								echo "</td>";
									 
								echo "</tr>".PHP_EOL;
							}
							echo "</table>".PHP_EOL;
						}
						else if($type == $teacher_prefix)
							echo "Du bist Lehrer";
					}
					else
					{
						print_err("Schüler können Kurse nicht verändern");
					}
					
					$ref->close();
				}
				else
				{
					print_err("Um Kurse verändern zu können wird eine Authentifikation benötigt");
				}
			?>
		</div>
	</body>
</html>