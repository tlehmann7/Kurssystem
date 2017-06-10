<?php
	require_once("../config/db.php");
	initSession();
?>
<!DOCTYPE html>
<html>
	<head>
		<title>Kurssystem</title>
		<link rel = "stylesheet" href = "../style/main.css"/>
		<link rel = "stylesheet" href = "../style/input.css"/>
		<link rel = "stylesheet" href = "../style/course.css"/>
		<script src = "../Javascript/ccourse.php"></script>
		<meta charset = "UTF-8"/>
	</head>
	<body>
		<div class = "wrapper70">
			<form method = "GET" id = "formular">
				<label for = "name">Name: </label><input name = "name" type = "text" required/><br>
				<label for = "reldate">Veröffentlichung: </label><input name = "reldate" type = "date" required/><br>
				<label for = "termdate">Beendigung: </label><input name = "termdate" type = "date" required/><br>
				<span>Jahrgänge erlauben:</span><br>
				<?php
					require_once("../config/db.php");
					$ref = new mysqli($db_host, $db_user, $db_password, $db_name);
					
					if(!$ref->connect_error)
					{
						$query_string = "SELECT DISTINCT alevel FROM ".$db_table_user." WHERE alevel IS NOT NULL ORDER BY alevel DESC;";
						$alevels = $ref->query($query_string)->fetch_all(MYSQLI_NUM);
						
						for($i = 0; $i < count($alevels); $i++)
						{
							$id = $allowedCPrefix.$alevels[$i][0];
							echo "<label for = \"".$id."\">".$alevels[$i][0]."</label><input type = \"checkbox\" name = \"".$id."\"/>".PHP_EOL;
							$query_string = "SELECT DISTINCT class FROM ".$db_table_user." WHERE alevel = ".$alevels[$i][0]." ORDER BY class ASC;";
							$classes = $ref->query($query_string)->fetch_all(MYSQLI_NUM);
							for($p = 0; $p < count($classes); $p++)
							{
								$id = $allowedCPrefix.$alevels[$i][0].$allowedClassesPostfix.$p;
								echo "<label for = \"".$id."\">".$classes[$p][0]."</label><input type = \"checkbox\" name = \"".$id."\"/>".PHP_EOL;
							}
							echo "<br>".PHP_EOL;
						}
					}
					
					$ref->close();
				?>
				<div id = "newProjects">
					<table>
						<tr><th width = "10%">Name</th><th width = "15%">Plätze</th></tr>
					</table>
				</div>
				<div class = "divbutton" onclick = "addNew();">
					Neue Projektspalte
				</div>
				<div class = "divbutton" onclick = "document.getElementById('formular').submit();">
					Fertig
				</div>
				<input type = "submit" value = "Fertig"/>
				<br>
			</form>
			<div>
			<?php
				require_once("../config/db.php");
				
				
				$ok = true;
				if($_SESSION['auth'] && $_SESSION['type'] == $admin_prefix || $_SESSION['type'] == $teacher_prefix || $ok)
				{
					if(isset($_GET['reldate']) && isset($_GET['termdate']) && isset($_GET['name']))
					{
						$ref = new mysqli($db_host, $db_user, $db_password, $db_name);
						if(!$ref->connect_error)
						{
							$relDateStamp = date_create_from_format("d.m.Y", $_GET['reldate'])->getTimestamp();
							$relDateStamp = courseStamp($relDateStamp);
							$termDateStamp = date_create_from_format("d.m.Y", $_GET['termdate'])->getTimestamp();
							$termDateStamp = courseStamp($termDateStamp);
							
							$allowString = "";
							
							$query_string = "SELECT DISTINCT alevel FROM ".$db_table_user.";";
							
							$alevels = $ref->query($query_string)->fetch_all(MYSQLI_NUM);
							
							for($i = 0; $i < count($alevels); $i++)
							{
								$id = $allowedCPrefix.$alevels[$i][0];
								if($_GET[$id] == TRUE)
								{
									$query_string = "SELECT DISTINCT class FROM ".$db_table_user." WHERE alevel = ".$alevels[$i][0]." ORDER BY class ASC;";
									$classes = $ref->query($query_string)->fetch_all(MYSQLI_NUM);
									
									$allowString .= $alevels[$i][0];
									
									for($p = 0; $p < count($classes); $p++)
									{
										$id = $allowedCPrefix.$alevels[$i][0].$allowedClassesPostfix.$p;
										if($_GET[$id])
										{
											$allowString .= "#".$classes[$p][0];
										}
									}
									
									$allowString .= ";";
								}
							}
							
							$projNames = array();
							$projSpaces = array();
							
							$projNum = 0;
							
							$projOk = TRUE;
							
							while(TRUE)
							{
								$idname = $newProjectPrefix.$projNum.$newProjectNamePrefix;
								$idspace = $newProjectPrefix.$projNum.$newProjectSpacePrefix;
								if(isset($_GET[$idname]) && isset($_GET[$idspace]))
								{
									if(!empty($_GET[$idname]) && !empty($_GET[$idspace]))
									{
										$projNames[] = $ref->real_escape_string($_GET[$idname]);
										$projSpaces[] = intval($ref->real_escape_string($_GET[$idspace]));
									}
									else
										$projOk = FALSE;
								}
								else
									break;
								$projNum++;
							}
							
							$realname = $ref->real_escape_string($_GET['name']);
							
							var_dump($projNames);
							echo "<br>";
							var_dump($projSpaces);
							
							if($projOk)
							{
								if($relDateStamp > $termDateStamp)
								{
									print_err("Der Kurs kann nicht vor der Veröffentlichung beendet werden");
								}
								else
								{
									$query_string = "INSERT INTO ".$db_table_courses."(name, reldate, termdate, owner, allowed) values(\"".$realname."\", ".$relDateStamp.", ".$termDateStamp.", \"".$_SESSION['user']."\", \"".$allowString."\");"; // TODO add allowedclasses
									$ref->query($query_string);
									
									$query_string = "CREATE TABLE IF NOT EXISTS `".$realname.$courseObservationPostfix."`(username varchar(100) UNIQUE);";
									$ref->query($query_string);
										
									$query_string = "CREATE TABLE IF NOT EXISTS `".$realname.$courseInfoPostfix."`(name varchar(100) UNIQUE, current int, max int);";
									$ref->query($query_string);
									
									for($i = 0; $i < count($projNames); $i++)
									{
										$query_string = "INSERT INTO `".$realname.$courseInfoPostfix."`(name, current, max) values(\"".$projNames[$i]."\", 0,".$projSpaces[$i].");";
										$ref->query($query_string);
										
										$query_string = "CREATE TABLE IF NOT EXISTS `".$realname."_".$projNames[$i].$projectSignUpPostfix."`(vname varchar(100), nname varchar(100), alevel tinyint, class varchar(1));";
										$ref->query($query_string);
									}
								}
							}
						}
						else
							print_err($db_connection_error_msg);
						$ref->close();
					}
				}
				else if(!$_SESSION['auth'])
				{
					print_err("Es wird eine Authentifikation benötigt");
				}
				else if($_SESSION['type'] == $student_prefix)
				{
					print_err("Schüler haben keine Berichtigung Kurse zu erstellen/ändern oder zu löschen");
				}
			?>
			</div>
		</div>
	</body>
</html>