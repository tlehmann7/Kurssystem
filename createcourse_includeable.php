<?php
	require_once("config/db.php");
	initSession();
	
	if(!isset($_GET['count']) || intval($_GET['count']) < 1)
		$_GET['count'] = 1;

	if($_SESSION['auth'] && ($_SESSION['type'] == $teacher_prefix || $_SESSION['type'] == $admin_prefix))
	{
	?>
	<form method = "GET" id = "formular">
		<div id = "createcourse-labels">
			<label for = "name">Name: </label><br>
			<label for = "reldate">Veröffentlichung: </label><br>
			<label for = "termdate">Beendigung: </label><br>
			<label for = "enableChange">Umentscheiden erlauben: </label><br>
		</div>
		<div id = "createcourse-inputfields">
			<input name = "name" type = "text" placeholder = "z.B. Projektwoche" required/><br>
			<input name = "reldate" type = "text" placeholder = "27.04.2031" required/>
			um
			<input name = "reldate-daytime" type = "text" placeholder = "16:34:56" required/><br>
			<input name = "termdate" type = "text" placeholder = "27.04.2031" required/>
			um
			<input name = "termdate-daytime" type = "text" placeholder = "16:34:56" required/><br>
			<input name = "enableChange" type = "checkbox"/><br>
		</div>
		<br>
		<span>Jahrgänge erlauben:</span><br>
		<?php
			$ref = new mysqli($db_host, $db_user, $db_password, $db_name);
			
			if(!$ref->connect_error)
			{
				$query_string = "SELECT DISTINCT alevel FROM ".$db_table_user." WHERE alevel IS NOT NULL ORDER BY alevel ASC;";
				$alevels = $ref->query($query_string)->fetch_all(MYSQLI_ASSOC);
				
				for($i = 0; $i < count($alevels); $i++)
				{
					$query_string = "SELECT DISTINCT class FROM ".$db_table_user." WHERE alevel = ".$alevels[$i]['alevel']." ORDER BY class ASC;";
					$classes = $ref->query($query_string)->fetch_all(MYSQLI_ASSOC);
					print_normal($alevels[$i]['alevel'].":");
					for($p = 0; $p < count($classes); $p++)
					{
						$id = $allowedCPrefix.$alevels[$i]['alevel'].$allowedClassesPostfix.$classes[$p]['class'];
						echo "<label for = \"".$id."\">".$classes[$p]['class']."</label><input type = \"checkbox\" name = \"".$id."\"/>".PHP_EOL;
					}
					echo "<br>".PHP_EOL;
				}
			}
			
			$ref->close();
		?>
		<div id = "newProjects">
			<table id = "projectTable">
				<tr><th width = "10%">Name</th><th width = "15%">Plätze</th><th width = "20%">Jahrgänge erlauben</th></tr>
				<?php
					if(isset($_GET['count']))
					{
						$allowInfo = getAllowInfo();
						for($kop = 0; $kop < intval($_GET['count']); $kop++)
						{
							echo "<tr><td><input type = \"text\" name = \"".$newProjectPrefix.$kop.$newProjectNamePostfix."\" required/></td><td><input type = \"number\" min = \"0\" name = \"".$newProjectPrefix.$kop.$newProjectSpacePostfix."\"></td>";
							
							$toput = "";
							
							for($l = 0; $l < count($allowInfo); $l++)
							{
								$toput .= $allowInfo[$l]->alevel.": ";
								
								for($cl = 0; $cl < count($allowInfo[$l]->classes); $cl++)
								{
									$toput .= $allowInfo[$l]->classes[$cl];
									$toput .= "<input type = \"checkbox\" name = \"".$newProjectPrefix.$kop.$allowedCPrefix.$allowInfo[$l]->alevel.$allowedClassesPostfix.$allowInfo[$l]->classes[$cl]."\"/>";
								}
								
								$toput .= "<br>";
							}
							
							echo "<td>".$toput."</td>";
							
							echo "</tr>".PHP_EOL;
						}
					}
				?>
			</table>
		</div>
		<a href = "?location=createcourse&count=<?php echo intval($_GET['count']) + 1; ?>">Neues Projekt</a>
		<a href = "?location=createcourse&count=<?php if(intval($_GET['count']) > 1) echo intval($_GET['count']) - 1; else echo intval($_GET['count']); ?>">Lösche letztes Projekt</a>
		<br>
		<input class = "off" type = "text" name = "location" value = "createcourse"/>
		<input type = "submit" value = "Fertig" class = "submit"/>
		<br>
	</form>
	<?php
	}
	
	if($_SESSION['auth'] && ($_SESSION['type'] == $admin_prefix || $_SESSION['type'] == $teacher_prefix))
	{
		if(isset($_GET['reldate']) && isset($_GET['termdate']) && isset($_GET['name']))
		{
			$ref = new mysqli($db_host, $db_user, $db_password, $db_name);
			if(!$ref->connect_error)
			{
				$relDateStamp = date_create_from_format("d.m.Y", $_GET['reldate'])->getTimestamp();
				$relDateStamp = courseStamp($relDateStamp);
				$relDateStamp += (date_create_from_format("d.m.Y-H:i:s", "01.01.1970-".$_GET['reldate-daytime'])->getTimestamp() - 3600);
				$termDateStamp = date_create_from_format("d.m.Y", $_GET['termdate'])->getTimestamp();
				$termDateStamp = courseStamp($termDateStamp);
				$termDateStamp += (date_create_from_format("d.m.Y-H:i:s", "01.01.1970-".$_GET['termdate-daytime'])->getTimestamp() - 3600);
				
				$allowString = "";
				
				$allowInfo = getAllowInfo();
				
				$aEndArray = array();
				for($i = 0; $i < count($allowInfo); $i++)
				{
					$alreadyFound = false;
					
					$aPreArr = array();
					
					for($p = 0; $p < count($allowInfo[$i]->classes); $p++)
					{
						$id = $allowedCPrefix.$allowInfo[$i]->alevel.$allowedClassesPostfix.$allowInfo[$i]->classes[$p];
						if(getCheckboxOutput($_GET[$id]))
						{
							if(!$alreadyFound)
							{
								$alreadyFound = true;
								$aPreArr[] = $allowInfo[$i]->alevel;
							}
							
							$aPreArr[] = $allowInfo[$i]->classes[$p];
						}
					}
					
					if(count($aPreArr) > 0)
						$aEndArray[] = implode("#", $aPreArr);
				}
				
				$allowString = implode(";", $aEndArray);
				
				$projNames = array();
				$projSpaces = array();
				$projAllowStrings = array();
				
				$projNum = 0;
				
				$projOk = TRUE;
				
				$allowInfo = getAllowInfo();
				
				while(TRUE)
				{
					$idname = $newProjectPrefix.$projNum.$newProjectNamePostfix;
					$idspace = $newProjectPrefix.$projNum.$newProjectSpacePostfix;
					if(isset($_GET[$idname]) && isset($_GET[$idspace]))
					{
						if(!empty($_GET[$idname]) && !empty($_GET[$idspace]))
						{
							$projNames[] = $ref->real_escape_string($_GET[$idname]);
							$projSpaces[] = intval($ref->real_escape_string($_GET[$idspace]));
							
							// goes here
							
							$endArray = array();
							for($ol = 0; $ol < count($allowInfo); $ol++)
							{
								$alreadyFound = false;
								
								$preArr = array();
								
								for($oc = 0; $oc < count($allowInfo[$ol]->classes); $oc++)
								{
									$idallow = $newProjectPrefix.$projNum.$allowedCPrefix.$allowInfo[$ol]->alevel.$allowedClassesPostfix.$allowInfo[$ol]->classes[$oc];
									
									echo $idallow."<br>".PHP_EOL;
									
									if(getCheckboxOutput($_GET[$idallow]))
									{
										if(!$alreadyFound)
										{
											$alreadyFound = true;
											$preArr[] = $allowInfo[$ol]->alevel;
										}
										
										$preArr[] = $allowInfo[$ol]->classes[$oc];
									}
								}
								
								if(count($preArr) > 0)
									$endArray[] = implode("#", $preArr);
							}
							
							$projAllowStrings[] = implode(";", $endArray);
						}
						else
							$projOk = FALSE;
					}
					else
						break;
					$projNum++;
				}
				
				$realname = $ref->real_escape_string($_GET['name']);
				if(isset($_GET['enableChange']))
				{
					$enableChange = $ref->real_escape_string($_GET['enableChange']);
					
					$enableChange = getCheckboxOutput($enableChange);
				}
				else
					$enableChange = 0;
				
				if($projOk)
				{
					if($relDateStamp > $termDateStamp)
					{
						print_err("Der Kurs kann nicht vor der Veröffentlichung beendet werden");
					}
					else
					{
						// courseID erzeugen
						$cid = 0;
						while(isGiven($db_table_courses, "ID", $cid)) 
							$cid++;
						
						$query_string = "INSERT INTO ".$db_table_courses."(ID, name, reldate, termdate, owner, allowed, enableChange) values(\"".$cid."\", \"".$realname."\", ".$relDateStamp.", ".$termDateStamp.", \"".$_SESSION['user']."\", \"".$allowString."\", ".$enableChange.");";
						$ref->query($query_string);
						
						$query_string = "CREATE TABLE IF NOT EXISTS ".$cid.$courseObservationPostfix."(username VARCHAR(100) UNIQUE);";
						$ref->query($query_string);
						
						$query_string = "CREATE TABLE IF NOT EXISTS ".$cid.$courseInfoPostfix."(ID TINYINT, name VARCHAR(100), current MEDIUMINT, max MEDIUMINT, allowed TEXT, PRIMARY KEY(ID));";
						$ref->query($query_string);
						
						for($i = 0; $i < count($projNames); $i++)
						{
							$query_string = "INSERT INTO ".$cid.$courseInfoPostfix."(ID, name, current, max, allowed) values(".$i.", \"".$projNames[$i]."\", 0,".$projSpaces[$i].", \"".$projAllowStrings[$i]."\");";
							$ref->query($query_string);
							
							$query_string = "CREATE TABLE IF NOT EXISTS ".$cid."_".$i.$projectSignUpPostfix."(username varchar(100) UNIQUE, vname varchar(100), nname varchar(100), email varchar(100), alevel TINYINT, class varchar(10));";
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
