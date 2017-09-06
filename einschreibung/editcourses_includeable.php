<?php
	require_once("../config/db.php");
	
	if($_SESSION['auth'])
	{
		if($_SESSION['type'] != $student_prefix)
		{
			$ref = new mysqli($db_host, $db_user, $db_password, $db_name);		
		
			if(!$ref->connect_error)
			{
				// Apply Changes
				$query_string = "SELECT ID, OWNER FROM ".$db_table_courses.";";
				$CIDs = $ref->query($query_string)->fetch_all(MYSQLI_ASSOC);
				
				for($i = 0; $i < count($CIDs); $i++)
				{
					if($_SESSION['type'] == $admin_prefix || $CIDs[$i]['OWNER'] == $_SESSION['user'])
					{
						if(isset($_GET[$CIDs[$i]['ID']."_delete"]))
						{
							$query_string = "DROP TABLE ".$CIDs[$i]['ID'].$courseObservationPostfix.";";
							$ref->query($query_string);
						
							$query_string = "SELECT ID FROM ".$CIDs[$i]['ID'].$courseInfoPostfix.";";
						
							$PIDs = $ref->query($query_string)->fetch_all(MYSQLI_ASSOC);
						
							for($p = 0; $p < count($PIDs); $p++)
							{
								$query_string = "DROP TABLE ".$CIDs[$i]['ID']."_".$PIDs[$p]['ID'].$projectSignUpPostfix.";";
								$ref->query($query_string);
							}
						
							$query_string = "DROP TABLE ".$CIDs[$i]['ID'].$courseInfoPostfix.";";
						
							$ref->query($query_string);
						
							$query_string = "DELETE FROM ".$db_table_courses." WHERE ID = ".$CIDs[$i]['ID'].";";
							$ref->query($query_string);
						}
						
						if(isset($_GET[$CIDs[$i]['ID']."_reldate"]) && isset($_GET[$CIDs[$i]['ID']."_reldate-daytime"]))
						{
							$rstamp = getDays($_GET[$CIDs[$i]['ID']."_reldate"]) + getTime($_GET[$CIDs[$i]['ID']."_reldate-daytime"]);
							$query_string = "UPDATE ".$db_table_courses." SET reldate = ".$rstamp." WHERE ID = ".$CIDs[$i]['ID'].";";
							$ref->query($query_string);
						}
						
						if(isset($_GET[$CIDs[$i]['ID']."_termdate"]) && isset($_GET[$CIDs[$i]['ID']."_termdate-daytime"]))
						{
							$rstamp = getDays($_GET[$CIDs[$i]['ID']."_termdate"]) + getTime($_GET[$CIDs[$i]['ID']."_termdate-daytime"]);
							$query_string = "UPDATE ".$db_table_courses." SET termdate = ".$rstamp." WHERE ID = ".$CIDs[$i]['ID'].";";
							$ref->query($query_string);
						}
						
						if(isset($_GET[$CIDs[$i]['ID']."_enableChange"]))
						{
							if($_GET[$CIDs[$i]['ID']."_enableChange"] == 'on')
							{
								$query_string = "UPDATE ".$db_table_courses." SET enableChange = 1 WHERE ID = ".$CIDs[$i]['ID'].";";
							}
							else
							{
								$query_string = "UPDATE ".$db_table_courses." SET enableChange = 0 WHERE ID = ".$CIDs[$i]['ID'].";";
							}
							$ref->query($query_string);
						}
						
						if(isset($_GET[$CIDs[$i]['ID']."_name"]))
						{
							$query_string = "UPDATE ".$db_table_courses." SET name = \"".$_GET[$CIDs[$i]['ID']."_name"]."\" WHERE ID = ".$CIDs[$i]['ID'].";";
							$ref->query($query_string);
						}
						
						// TODO:
						$query_string = "SELECT DISTINCT alevel FROM ".$db_table_user." WHERE alevel IS NOT NULL;";
						$alevels = $ref->query($query_string)->fetch_all(MYSQLI_ASSOC);
						
						$tallowString = "";
						
						for($a = 0; $a < count($alevels); $a++)
						{
							$query_string = "SELECT DISTINCT class FROM ".$db_table_user." WHERE alevel = ".$alevels[$a]['alevel'].";";
							$classes = $ref->query($query_string)->fetch_all(MYSQLI_ASSOC);
							
							$ffound = true;
							for($c = 0; $c < count($classes); $c++)
							{
								$id = $CIDs[$i]['ID']."_".$allowedCPrefix.$alevels[$a]['alevel'].$allowedClassesPostfix.$classes[$c]['class'];
								if(isset($_GET[$id]))
								{
									if(getCheckboxOutput($_GET[$id]))
									{
										if($ffound)
										{
											$ffound = false;
											$tallowString .= $alevels[$a]['alevel'];
										}
										
										$tallowString .= "#".$classes[$c]['class'];
									}
								}
							}
							
							if(!$ffound)
								$tallowString .= ";";
						}
						
						if(isset($_GET['recognize_allows']))
						{
							$query_string = "UPDATE ".$db_table_courses." SET allowed = \"".$tallowString."\" WHERE ID = ".$CIDs[$i]['ID'].";";
							$ref->query($query_string);
						}
						
						$query_string = "SELECT ID FROM ".$CIDs[$i]['ID'].$courseInfoPostfix.";";
						$PIDs = $ref->query($query_string)->fetch_all(MYSQLI_ASSOC);
						
						for($p = 0; $p < count($PIDs); $p++)
						{
							if(isset($_GET[$CIDs[$i]['ID']."_".$PIDs[$p]['ID']."_delete"]))
							{
								$query_string = "SELECT username FROM ".$CIDs[$i]['ID']."_".$PIDs[$p]['ID'].$projectSignUpPostfix.";";
								$usernames = $ref->query($query_string)->fetch_all(MYSQLI_ASSOC);
								for($u = 0; $u < count($usernames); $u++)
								{
									$query_string = "DELETE FROM ".$CIDs[$i]['ID'].$courseObservationPostfix." WHERE username = \"".$usernames[$u]['username']."\";";
									$ref->query($query_string);
								}
								
								$query_string = "DELETE FROM ".$CIDs[$i]['ID'].$courseInfoPostfix." WHERE ID = ".$PIDs[$p]['ID'].";";
								$ref->query($query_string);
								
								$query_string = "DROP TABLE ".$CIDs[$i]['ID']."_".$PIDs[$p]['ID'].$projectSignUpPostfix.";";
								$ref->query($query_string);
							}
							
							if(isset($_GET[$CIDs[$i]['ID']."_".$PIDs[$p]['ID'].$newProjectNamePostfix]))
							{
								$query_string = "UPDATE ".$CIDs[$i]['ID'].$courseInfoPostfix." SET name = \"".$_GET[$CIDs[$i]['ID']."_".$PIDs[$p]['ID'].$newProjectNamePostfix]."\" WHERE ID = ".$PIDs[$p]['ID'].";";
								$ref->query($query_string);
							}
							
							if(isset($_GET[$CIDs[$i]['ID']."_".$PIDs[$p]['ID'].$newProjectSpacePostfix]))
							{
								$query_string = "UPDATE ".$CIDs[$i]['ID'].$courseInfoPostfix." SET max = ".$_GET[$CIDs[$i]['ID']."_".$PIDs[$p]['ID'].$newProjectSpacePostfix]." WHERE ID = ".$PIDs[$p]['ID'].";";
								$ref->query($query_string);
							}
						}
						
						
						
					}
				}
				
				// Output
				
				if($_SESSION['type'] == $teacher_prefix)
				{
					// Überarbeiten
					$query_string = "SELECT * FROM ".$db_table_courses." WHERE owner = \"".$_SESSION['user']."\";";
					$courses = $ref->query($query_string)->fetch_all(MYSQLI_ASSOC);
					
					if(count($courses) > 0)
					{
						for($i = 0; $i < count($courses); $i++)
						{
							$query_string = "SELECT * FROM ".$courses[$i]['ID'].$courseInfoPostfix.";";
							$projects = $ref->query($query_string)->fetch_all(MYSQLI_ASSOC);
							
							echo "<div class = \"pheader\">".$courses[$i]['name']."</div>".PHP_EOL;
							echo "<h2>".$courses[$i]['reldate']."</h2>".PHP_EOL;
							echo "<h2>".$courses[$i]['termdate']."</h2>".PHP_EOL;
							
							if(count($projects) > 0)
							{
								echo "<table>".PHP_EOL;
								echo "<tr><th>Name</th><th>Plätze</th></tr>".PHP_EOL;
								for($p = 0; $p < count($projects); $p++)
								{
									echo "<tr><td>".$projects[$p]['name']."</td><td>".$projects[$p]['max']."</tr>".PHP_EOL;
								}
								echo "</table>".PHP_EOL;
							}
						}
					}
					else
						print_signal("Sie besitzen keine Kurse");
						
					// Überarbeiten
				}
				else if($_SESSION['type'] == $admin_prefix)
				{
					$query_string = "SELECT * FROM ".$db_table_courses.";";
					$courses = $ref->query($query_string)->fetch_all(MYSQLI_ASSOC);
					
					if(count($courses) > 0)
					{
						echo "<form method = \"GET\">".PHP_EOL;
						echo "<input class = \"off\" type = \"text\" name = \"location\" value = \"editcourses\"/>".PHP_EOL;
						echo "<input class = \"off\" type = \"text\" name = \"recognize_allows\"/>".PHP_EOL;
						for($i = 0; $i < count($courses); $i++)
						{
							$query_string = "SELECT * FROM ".$courses[$i]['ID'].$courseInfoPostfix.";";
							$projects = $ref->query($query_string)->fetch_all(MYSQLI_ASSOC);
							
							echo "<div class = \"pheader\"><h1>".$courses[$i]['name']."</h1><img class = \"deleteicon\" src = \"../images/whitedelete4ever.png\" alt = \"unwiderruflich löschen\" onclick = \"if(confirm('Sind im Begriff ".$courses[$i]['name']." zu l\u00f6schen. \\nBest\u00e4tigen?')) window.location.href += '\u0026".$courses[$i]['ID']."_delete';\"/></div>".PHP_EOL;
							echo "<div>Datum der Veröffentlichung: <input type = \"date\" name = \"".$courses[$i]['ID']."_reldate\" value = \"".date("d.m.Y", $courses[$i]['reldate'])."\"/> um <input type = \"date\" name = \"".$courses[$i]['ID']."_reldate-daytime\" value = \"".date("H:i:s", $courses[$i]['reldate'])."\"/></div>".PHP_EOL;
							echo "<div>Datum der Abschaltung: <input type = \"date\" name = \"".$courses[$i]['ID']."_termdate\" value = \"".date("d.m.Y", $courses[$i]['termdate'])."\"/> um <input type = \"date\" name = \"".$courses[$i]['ID']."_termdate-daytime\" value = \"".date("H:i:s", $courses[$i]['termdate'])."\"/></div>".PHP_EOL;
							
							echo "<span>Jahrgänge erlauben:</span><br>".PHP_EOL;
							
							$query_string = "SELECT DISTINCT alevel FROM ".$db_table_user." WHERE alevel IS NOT NULL ORDER BY alevel DESC;";
							$alevels = $ref->query($query_string)->fetch_all(MYSQLI_ASSOC);
							
							$query_string = "SELECT allowed FROM ".$db_table_courses." WHERE ID = ".$courses[$i]['ID'].";";
							$allowString = $ref->query($query_string)->fetch_all(MYSQLI_ASSOC)[0]['allowed'];
							
							for($a = 0; $a < count($alevels); $a++)
							{
								print_normal($alevels[$a]['alevel'].":");
								$query_string = "SELECT DISTINCT class FROM ".$db_table_user." WHERE alevel = ".$alevels[$a]['alevel']." ORDER BY class ASC;";
								$classes = $ref->query($query_string)->fetch_all(MYSQLI_ASSOC);
								
								for($c = 0; $c < count($classes); $c++)
								{
									$id = $courses[$i]['ID']."_".$allowedCPrefix.$alevels[$a]['alevel'].$allowedClassesPostfix.$classes[$c]['class'];
									echo $classes[$c]['class']."<input type = \"checkbox\" name = \"".$id."\"";
									
									$expl1 = explode(";", $allowString);
									for($e1 = 0; $e1 < count($expl1); $e1++)
									{
										$expl2 = explode("#", $expl1[$e1]);
										if($expl2[0] == $alevels[$a]['alevel'])
										{
											for($e2 = 1; $e2 < count($expl2); $e2++)
											{
												if($expl2[$e2] == $classes[$c]['class'])
												{
													echo " checked";
													break;
												}
											}
											break;
										}
									}
									
									echo "/>".PHP_EOL;
								}
								
								echo "<br>".PHP_EOL;
							}
							
							if(count($projects) > 0)
							{
								echo "<table id = \"".$courses[$i]['ID']."_ptable\">".PHP_EOL;
								echo "<tr><th>Name</th><th>Plätze</th><th>Löschen</th></tr>".PHP_EOL;
								for($p = 0; $p < count($projects); $p++)
								{
									echo "<tr><td>";
									echo "<input type = \"text\" value = \"".$projects[$p]['name']."\" name = \"".$courses[$i]['ID']."_".$projects[$p]['ID']."_name\"/>";
									echo "</td><td>";
									echo "<input type = \"text\" value = \"".$projects[$p]['max']."\" name = \"".$courses[$i]['ID']."_",$projects[$p]['ID']."_maxspace\"/>";
									echo "</td><td>";
									echo "<input type = \"checkbox\" name = \"".$courses[$i]['ID']."_".$projects[$p]['ID']."_delete\"/>";
									
									echo "</td></tr>".PHP_EOL;
								}
								echo "</table>".PHP_EOL;
							}
						}
						echo "<input class = \"submit\" type = \"submit\" value = \"Übernehme Änderungen\"/>".PHP_EOL;
						echo "</form>".PHP_EOL;
					}
					else
						print_signal("Es gibt keine Kurse"); 
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
