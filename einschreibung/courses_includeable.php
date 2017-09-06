<?php
	require_once("../config/db.php");
	
	$accessTime = time();
	
	if($_SESSION['auth'])
	{
		$ref = new mysqli($db_host, $db_user, $db_password, $db_name);
		if(!$ref->connect_error)
		{
			// Check register
			if(isset($_GET['signup']) && isset($_GET['courseid']) && isset($_GET['projectid']))
			{
				$query_string = "SELECT * FROM ".$db_table_courses.";";
				$courses = $ref->query($query_string)->fetch_all(MYSQLI_ASSOC);
				
				$signedUp = FALSE;
				
				for($i = 0; $i < count($courses); $i++)
				{
					if($_GET['courseid'] == $courses[$i]['ID'])
					{
						if(isAllowed($courses[$i]['allowed'], $_SESSION['alevel'], $_SESSION['class']))
						{
							if(intval($courses[$i]['reldate']) <= $accessTime && intval($courses[$i]['termdate']) > $accessTime)
							{
								$query_string = "SELECT * FROM ".$courses[$i]['ID'].$courseInfoPostfix.";";
								$projects = $ref->query($query_string)->fetch_all(MYSQLI_ASSOC);
								for($p = 0; $p < count($projects); $p++)
								{
									if($_GET['projectid'] == $projects[$p]['ID'])
									{
										// Unsure
										$query_string = "SELECT current, max FROM `".$courses[$i]['ID'].$courseInfoPostfix."` WHERE ID = \"".$projects[$p]['ID']."\";";
										$places = $ref->query($query_string)->fetch_array(MYSQLI_ASSOC);
										if($places['current'] < $places['max'])
										{
										/*if($projects[$p]['current'] < $project[$p]['max'])
										{*/
											$query_string = "SELECT username, vname, nname, email, class, alevel FROM ".$db_table_user." WHERE username = \"".$_SESSION['user']."\";";
											$user_information = $ref->query($query_string)->fetch_array(MYSQLI_ASSOC);
											if(!isGiven($courses[$i]['ID'].$courseObservationPostfix, "username", $_SESSION['user']))
											{
												$query_string = "INSERT INTO `".$courses[$i]['ID'].$courseObservationPostfix."`(username) values(\"".$_SESSION['user']."\");";
												$ref->query($query_string);
												$query_string = "INSERT INTO `".$courses[$i]['ID']."_".$projects[$p]['ID'].$projectSignUpPostfix."` values(\"".$user_information['username']."\", \"".$user_information['vname']."\", \"".$user_information['nname']."\", \"".$user_information['email']."\", \"".$user_information['alevel']."\", \"".$user_information['class']."\");";
												$ref->query($query_string);
												$query_string = "UPDATE `".$courses[$i]['ID'].$courseInfoPostfix."` SET current = current + 1 WHERE ID = \"".$projects[$p]['ID']."\";";
												$ref->query($query_string);
												$signedUp = TRUE;
												messageUser("Du bist jetzt in ".$projects[$p]['name']." eingeschrieben");
												break;
											}
											else if($courses[$i]['enableChange'] && isGiven($courses[$i]['ID'].$courseObservationPostfix, "username", $_SESSION['user']))
											{
												$query_string = "SELECT ID, name FROM ".$courses[$i]['ID'].$courseInfoPostfix.";";
												$tProjects = $ref->query($query_string)->fetch_all(MYSQLI_ASSOC);
												for($k = 0; $k < count($tProjects); $k++)
												{
													if(isGiven($courses[$i]['ID']."_".$tProjects[$k]['ID'].$projectSignUpPostfix, "username", $_SESSION['user']))
													{
														if($projects[$p]['ID'] != $tProjects[$k]['ID'])
														{
															$query_string = "DELETE FROM ".$courses[$i]['ID']."_".$tProjects[$k]['ID'].$projectSignUpPostfix." WHERE username = \"".$_SESSION['user']."\";";
															$ref->query($query_string);
															$query_string = "INSERT INTO ".$courses[$i]['ID']."_".$projects[$p]['ID'].$projectSignUpPostfix." values(\"".$user_information['username']."\", \"".$user_information['vname']."\", \"".$user_information['nname']."\", \"".$user_information['email']."\", \"".$user_information['alevel']."\", \"".$user_information['class']."\");";
															$ref->query($query_string);
															$query_string = "UPDATE ".$courses[$i]['ID'].$courseInfoPostfix." SET current = current - 1 WHERE ID = \"".$tProjects[$k]['ID']."\";";
															$ref->query($query_string);
															$query_string = "UPDATE ".$courses[$i]['ID'].$courseInfoPostfix." SET current = current + 1 WHERE ID = \"".$projects[$p]['ID']."\";";
															$ref->query($query_string);
															$signedUp = TRUE;
															messageUser("Du bist zu ".$projects[$p]['name']." gewechselt");
															break;
														}
														else
															messageUser("Du bist dort schon eingeschrieben");
													}
												}
											}
											else
												messageUser("Du hast dich bereits eingeschrieben");
										}
										else
											messageUser("Das Projekt ist bereits voll");
									}
								}
							}
							else if(intval($courses[$i]['reldate']) > $accessTime)
								messageUser("Du bist zu fr\u00fch");
							else if(intval($courses[$i]['termdate']) < $accessTime)
								messageUser("Du bist zu sp\u00e4t");
						}
						else if($_SESSION['type'] != $student_prefix)
							messageUser("Es ist ihnen nicht gestattet an ".$courses[$i]['name']." teilzunehmen");
						else
							messageUser("Es ist dir nicht gestattet an ".$courses[$i]['name']." teilzunehmen");
					}
					
					if($signedUp)
						break;
				}
			}
		
		
			// Show Courses
			$query_string = "SELECT ID, name, allowed, termdate, reldate, enableChange FROM ".$db_table_courses.";";
			$courses = $ref->query($query_string)->fetch_all(MYSQLI_ASSOC);
			
			for($i = 0; $i < count($courses); $i++)
			{
				if(($courses[$i]['reldate'] <= $accessTime && $accessTime < $courses[$i]['termdate']) || $_SESSION['type'] == $teacher_prefix || $_SESSION['type'] == $admin_prefix || $dauth)
				{
					if(isAllowed($courses[$i]['allowed'], $_SESSION['alevel'], $_SESSION['class']) || $_SESSION['type'] == $teacher_prefix || $_SESSION['type'] == $admin_prefix || $dauth)
					{
						$query_string = "SELECT * FROM ".$courses[$i]['ID'].$courseInfoPostfix.";";
						$projects = $ref->query($query_string)->fetch_all(MYSQLI_ASSOC);
						
						$courseFound = TRUE;
						
						echo "<div class = \"pheader\"><h1>".$courses[$i]['name']."</h1>";
						
						if($_SESSION['type'] != $student_prefix)
							echo "<a href = \"getresults.php?courseid=".$courses[$i]['ID']."\"><img alt = \"Download\" class = \"downloadicon moveUp\" src = \"../images/download.png\"/></a>".PHP_EOL;
						echo "</div>".PHP_EOL;
						
						if($courses[$i]['enableChange'])
						{
							echo "<h2>Wechseln erlaubt</h2>".PHP_EOL;
						}
						
						echo "<script>mkTimer(".$courses[$i]['termdate'].", ".time().");</script>".PHP_EOL;
						
						
						echo "<table>".PHP_EOL;
						if($_SESSION['type'] != $student_prefix)
						{
							echo "<tr><th width = \"40%\">Projekt</th><th width = \"25%\">Plätze frei</th><th width = \"25%\">Plätze</th>";
						}
						else
						{
							echo "<tr><th width = \"60%\">Projekt</th><th width = \"30%\">Plätze frei</th><th width = \"30%\">Plätze</th>";
						}
						
						if($_SESSION['type'] != $student_prefix)
						{
							echo "<th width = \"20%\">Ergebnisse</th>";
						}
						
						echo "</tr>".PHP_EOL;
						
						for($k = 0; $k < count($projects); $k++)
						{
							echo "<tr><td class = \"rowHover\" onclick = \"signUp('".$courses[$i]['name']."', ".$courses[$i]['ID'].", '".$projects[$k]['name']."', ".$projects[$k]['ID'].", ".intval($_SESSION['type'] != $student_prefix).");\">".$projects[$k]['name']."</td><td>".($projects[$k]['max'] - $projects[$k]['current'])."</td><td>".$projects[$k]['max']."</td>";
						
							if($_SESSION['type'] != $student_prefix)
								echo "<td><a href = \"getresults.php?courseid=".$courses[$i]['ID']."&projectid=".$projects[$k]['ID']."\"><img alt = \"Download\" class = \"downloadicon\" src = \"../images/download.png\"/></a></td>";
							echo "</tr>".PHP_EOL;
						}
						echo "</table><br>".PHP_EOL;
					}
				}
			}
			
			if(!$courseFound && $_SESSION['type'] == $student_prefix)
				print_signal("Sorry, für dich gibt es gerade keine relevanten Kurse");
		}
		else
			print_err("Es ist ein Fehler bei der MYSQL Verbindung aufgetreten");
		
		$ref->close();
	}
?>
