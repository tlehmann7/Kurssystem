<?php
	require_once("../config/db.php");
	initSession();

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
			<input name = "reldate" type = "date" placeholder = "TAG.MONAT.JAHR" required/><br>
			<input name = "termdate" type = "date" placeholder = "TAG.MONAT.JAHR" required/><br>
			<input name = "enableChange" type = "checkbox"/><br>
		</div>
		<br>
		<span>Jahrgänge erlauben:</span><br>
		<?php
			$ref = new mysqli($db_host, $db_user, $db_password, $db_name);
			
			if(!$ref->connect_error)
			{
				$query_string = "SELECT DISTINCT alevel FROM ".$db_table_user." WHERE alevel IS NOT NULL ORDER BY alevel DESC;";
				$alevels = $ref->query($query_string)->fetch_all(MYSQLI_NUM);
				
				for($i = 0; $i < count($alevels); $i++)
				{
					$query_string = "SELECT DISTINCT class FROM ".$db_table_user." WHERE alevel = ".$alevels[$i][0]." ORDER BY class ASC;";
					$classes = $ref->query($query_string)->fetch_all(MYSQLI_NUM);
					print_normal($alevels[$i][0].":");
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
			<table id = "projectTable">
				<tr><th width = "10%">Name</th><th width = "15%">Plätze</th></tr>
			</table>
		</div>
		<input type = "button" value = "Neues Projekt" onclick = "addNew();"/>
		<input type = "button" value = "Lösche letztes Projekt" onclick = "remove();"/>
		<br>
		<input class = "off" type = "text" name = "location" value = "createcourse"/>
		<input type = "submit" value = "Fertig"/>
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
				$termDateStamp = date_create_from_format("d.m.Y", $_GET['termdate'])->getTimestamp();
				$termDateStamp = courseStamp($termDateStamp);
				
				$allowString = "";
				
				$query_string = "SELECT DISTINCT alevel FROM ".$db_table_user." WHERE alevel IS NOT NULL;";
				$alevels = $ref->query($query_string)->fetch_all(MYSQLI_ASSOC);
				
				for($i = 0; $i < count($alevels); $i++)
				{
					$alreadyFound = FALSE;
					
					$query_string = "SELECT DISTINCT class FROM ".$db_table_user." WHERE alevel = ".$alevels[$i]['alevel']." ORDER BY class ASC;";
					
					$classes = $ref->query($query_string)->fetch_all(MYSQLI_ASSOC);
					
					$buildString = "";
					for($p = 0; $p < count($classes); $p++)
					{
						$id = $allowedCPrefix.$alevels[$i]['alevel'].$allowedClassesPostfix.$p;
						if($_GET[$id] == "on")
						{
							if(!$alreadyFound)
							{
								$alreadyFound = TRUE;
								$buildString .= $alevels[$i]['alevel'].$buildString;
							}
							$buildString .= "#".$classes[$p]['class'];
						}
					}
					
					if($buildString != "")
						$allowString .= $buildString.";";
				}
				
				$projNames = array();
				$projSpaces = array();
				
				$projNum = 0;
				
				$projOk = TRUE;
				
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
						
						$query_string = "CREATE TABLE IF NOT EXISTS `".$cid.$courseObservationPostfix."`(username varchar(100) UNIQUE);";
						$ref->query($query_string);
							
						$query_string = "CREATE TABLE IF NOT EXISTS `".$cid.$courseInfoPostfix."`(ID tinyint, name varchar(100) UNIQUE, current int, max int, PRIMARY KEY(ID));";
						$ref->query($query_string);
						
						for($i = 0; $i < count($projNames); $i++)
						{
							$query_string = "INSERT INTO `".$cid.$courseInfoPostfix."`(ID, name, current, max) values($i, \"".$projNames[$i]."\", 0,".$projSpaces[$i].");";
							$ref->query($query_string);
							
							$query_string = "CREATE TABLE IF NOT EXISTS `".$cid."_".$i.$projectSignUpPostfix."`(username varchar(100), vname varchar(100), nname varchar(100), email varchar(100), alevel tinyint, class varchar(1));";
							$ref->query($query_string);
						}
					}
				}
			}
			else
				print_err($db_connection_error_msg);
			$ref->close();
		}
		else
		{
			messageUser("Es sind nicht alle Parameter\nzum Erstellen des Kurses\ngegeben");
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
