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
						
						if(isset($_GET[$CIDs[$i]['ID']."_reldate"]))
						{
							$query_string = "UPDATE ".$db_table_courses." SET reldate = ".$_GET[$CIDs[$i]['ID']."_reldate"].";";
							$ref->query($query_string);
						}
						
						if(isset($_GET[$CIDs[$i]['ID']."_termdate"]))
						{
							$query_string = "UPDATE ".$db_table_courses." SET termdate = ".$_GET[$CIDs[$i]['ID']."_termdate"].";";
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
				
				
				
				if($_SESSION['type'] == $teacher_prefix)
				{
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
				}
				else if($_SESSION['type'] == $admin_prefix)
				{
					$query_string = "SELECT * FROM ".$db_table_courses.";";
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
						print_signal("Es gibt keine Projekte"); 
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
