<?php
	require_once("config/db.php");
	initSession();
	
	if($_SESSION['auth'])
	{
		if(isset($_GET['count']) && isset($_GET['type']))
		{
			$specifier = strtoupper($_GET['type']);
			$count = intval($_GET['count']);
			
			if($specifier == $student_prefix)
			{
				if(isset($_GET['class']) && isset($_GET['alevel']))
				{
					$class = $_GET['class'];
					$year = $_GET['alevel'];
				}
				else
				{
					print_err("Bei Schüler-Keys muss der Jahrgang und die Klasse angegeben werden");
				}
			}
			
			if(($specifier == $student_prefix || $specifier == $teacher_prefix || $specifier == $admin_prefix) && $count > 0)
			{
				$allnums = array();
				$found = true;
				
				$ref = new mysqli($db_host, $db_user, $db_password, $db_name);
				
				if(!$ref->connect_error)
				{
					for($i = 0; $i < $count; $i++)
					{
						$found = false;
						do
						{
							$newnum = "";
							for($p = 0; $p < $key_length - 1; $p++)
							{
								$newnum .= $key_charset[random_int(0, strlen($key_charset) - 1)];
							}
							
							$newnum = "X".$newnum;
							
							for($p = 0; $p < count($allnums); $p++)
							{
								if($allnums[$p] == $newnum)
								{
									$found = true;
									break;
								}
							}
							
							if(isGiven($db_table_num, "AUTHNUM", $newnum))
								$found = true;
						}
						while($found);
						
						$allnums[] = $newnum;
					}
					
					$time = time();
					
					for($o = 0; $o < count($allnums); $o++)
					{
						if($specifier == $student_prefix)
							$query_string = "INSERT INTO ".$db_table_num." (AUTHNUM, timestamp, type, alevel, class) values(\"".$allnums[$o]."\", \"".$time."\", \"".$specifier."\", \"".$year."\", \"".$class."\");";
						else
							$query_string = "INSERT INTO ".$db_table_num." (AUTHNUM, timestamp, type) values(\"".$allnums[$o]."\", \"".$time."\",\"".$specifier."\");";
						
						if(!$ref->query($query_string))
							die($db_query_error_msg);
					}
				}
				else
				{
					die($db_con_error_msg.PHP_EOL);
				}
				
				$ref->close();
			}
		}
		
	?>
	<form method = "GET">
		<div id = "numgen-labels">
			<label for = "count">Anzahl neue Codes: </label><br>
			<label for = "type">Typ des neuen Benutzers:</label><br>
			<div id = "student_disable">
				<label for = "class">Klasse des Benutzers [A, B, C, D]:</label><br>
				<label for = "alevel">Abiturjahrgang des Benutzers:</label>
			</div>
		</div>
		<div id = "numgen-inputfields">
			<input name = "count" id = "count" type = "number" required/><br>
			<select name = "type" id = "type" onchange = "disablePartialForm(this.id);" required>
				<option value = "<?php echo $student_prefix; ?>">Schüler</option>
				<option value = "<?php echo $teacher_prefix; ?>">Lehrer</option>
				<option value = "<?php echo $admin_prefix; ?>">Administrator</option>
			</select>
			<br>
			<input name = "class" id = "class" type = "text" maxlength = "10" required/><br>
			<input name = "alevel" id = "alevel" type = "number" required/><br>
			<input name = "location" id = "location" type = "text" value = "keygen" class = "off"/>
			<input name = "output" type = "text" value = "0" class = "off"/>
		</div>
		<br>
		<input type = "submit" value = "Fertig"/>
	</form>
	<?php
	
		$ref = new mysqli($db_host, $db_user, $db_password, $db_name);
		if(!$ref->connect_error)
		{
		
			$query_string = "SELECT DISTINCT timestamp FROM ".$db_table_num.";";
			$stamps = $ref->query($query_string)->fetch_all(MYSQLI_ASSOC);
	
			for($l = 0; $l < count($stamps); $l++)
			{
				print_normal(date("d.m.Y - H:i:s", $stamps[$l]['timestamp']).":");
				echo "<a href = \"getkeys.php?timestamp=".$stamps[$l]['timestamp']."\"><img src = \"images/download.png\" class = \"downloadicon\"/></a><br><br>".PHP_EOL;
			}
		}
		else
			print_err($db_connection_error_msg);
		
		$ref->close();
	}
?>
