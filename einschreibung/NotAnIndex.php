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
		<script src = "../Javascript/signup.js">
		</script>
	</head>
	<body>
		<div class = "wrapper70">
			<?php
				require_once("../config/db.php");
				
				$accessTime = time();
				
				if(isset($_GET['signup']) && isset($_GET['coursename']) && isset($_GET['projectname']))
				{
					$ref = new mysqli($db_host, $db_user, $db_password, $db_name);
					if(!$ref->connect_error)
					{
						$query_string = "SELECT * FROM ".$db_table_courses.";";
						$courses = $ref->query($query_string)->fetch_all(MYSQLI_ASSOC);
						
						$signedUp = FALSE;
						
						for($i = 0; $i < count($courses); $i++)
						{
							if($_GET['coursename'] == $courses[$i]['name'])
							{
								$query_string = "SELECT * FROM ".$courses[$i]['name'].$courseInfoPostfix.";";
								$projects = $ref->query($query_string)->fetch_all(MYSQLI_ASSOC);
								for($p = 0; $p < count($projects); $p++)
								{
									if($_GET['projectname'] == $projects[$p]['name'])
									{
										if(intval($courses[$i]['reldate']) <= $accessTime && intval($courses[$i]['termdate'] > $accessTime))
										{
											if(isAllowed($courses[$i]['allowed'], $_SESSION['alevel'], $_SESSION['class']))
											{
												if(!isGiven($courses[$i]['name'].$courseObservationPostfix, "username", $_SESSION['user']))
												{
													$query_string = "SELECT current, max FROM `".$courses[$i]['name'].$courseInfoPostfix."` WHERE name = \"".$projects[$p]['name']."\";";
													$places = $ref->query($query_string)->fetch_array(MYSQLI_ASSOC);
													if($places['current'] < $places['max'])
													{
														$query_string = "INSERT INTO `".$courses[$i]['name'].$courseObservationPostfix."`(username) values(\"".$_SESSION['user']."\");";
														$ref->query($query_string);
														$query_string = "SELECT vname, nname, email, class, alevel FROM ".$db_table_user." WHERE username = \"".$_SESSION['user']."\";";
														$user_information = $ref->query($query_string)->fetch_array(MYSQLI_ASSOC);
														$query_string = "INSERT INTO `".$courses[$i]['name']."_".$projects[$p]['name'].$projectSignUpPostfix."` values(\"".$user_information['vname']."\", \"".$user_information['nname']."\", \"".$user_information['alevel']."\", \"".$user_information['class']."\");";
														$ref->query($query_string);
														$query_string = "UPDATE `".$courses[$i]['name'].$courseInfoPostfix."` SET current = current + 1 WHERE name = \"".$projects[$p]['name']."\";";
														$ref->query($query_string);
														$signedUp = TRUE;
														break;
													}
													else
														messageUser("Das Projekt ist bereits voll");
												}
												else
													messageUser("Du hast dich bereits eingeschrieben");
											}
											else
												messageUser("Es ist dir nicht gestattet an diesem Kurs teilzunehmen");
										}
										else if(intval($courses[$i]['reldate']) > $accessTime)
											messageUser("Du bist zu fr\u00fch");
										else if(intval($courses[$i]['termdate']) < $accessTime)
											messageUser("Du bist zu sp\u00e4t");
									}
								}
							}
							
							if($signedUp)
								break;
						}
					}
					else
						print_err("Es ist ein Fehler bei der MYSQL Verbindung aufgetreten");
				}
				
				$courseFound = FALSE;
				if(!$_SESSION['auth'] and !$dauth)
				{
					print_err("Du bist nicht angemeldet");
				}
				else if($_SESSION['auth'] or $dauth)
				{
					$ref = new mysqli($db_host, $db_user, $db_password, $db_name);
					
					if(!$ref->connect_error)
					{
						$query_string = "SELECT name, allowed, termdate, reldate FROM ".$db_table_courses.";";
						$courses = $ref->query($query_string)->fetch_all(MYSQLI_ASSOC);
						
						for($i = 0; $i < count($courses); $i++)
						{
							if(($courses[$i]['reldate'] <= $accessTime && $accessTime < $courses[$i]['termdate']) or $dauth)
							{
								if(isAllowed($courses[$i], $_SESSION['alevel'], $_SESSION['class']) or $dauth)
								{
									$query_string = "SELECT * FROM ".$courses[$i]['name'].$courseInfoPostfix.";";
									$projects = $ref->query($query_string)->fetch_all(MYSQLI_ASSOC);
									
									$courseFound = TRUE;
									echo "<h1>".$courses[$i]['name']."</h1><br>".PHP_EOL;
									echo "<table>".PHP_EOL;
									echo "<tr><th width = \"70%\">Projekte</th><th width = \"30%\">Plätze belegt</th><th width = \"30%\">Plätze</th>".PHP_EOL;
									for($k = 0; $k < count($projects); $k++)
									{
										echo "<tr class = \"rowHover\" onclick = \"signUp('".$courses[$i]['name']."', '".$projects[$k]['name']."');\"><td>".$projects[$k]['name']."</td><td>".$projects[$k]['current']."</td><td>".$projects[$k]['max']."</td></tr>".PHP_EOL;
									}
									echo "</table>".PHP_EOL;
								}
							}
						}
						
						if(!$courseFound)
							print_signal("Sorry, für dich gibt es gerade keine relevanten Kurse");
					}
					else
					{
						print_err($db_connection_error_msg);
					}
					
					$ref->close();
				}
			?>
		</div>
	</body>
</html>