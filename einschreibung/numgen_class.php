<?php
	require_once("../config/db.php");
	
	// 1 --> number: howmany keys
	// 2 --> character: "T" --> Teacher, 'S' --> Student (requires year), 'A' --> Admin // configure in ../config/db.php
	// 3 --> number: alevel 2019
	// 4 --> character: abcd --> Class
	
	if(isset($argv[1]) && isset($argv[2]))
	{
		$count = intval($argv[1]);
		$specifier = strtoupper($argv[2]);
		
		if($specifier == $student_prefix && isset($argv[3]) && isset($argv[4]))
		{
			$year = intval($argv[3]);
			$class = strtoupper($argv[4]);
		}
		else if($specifier == $student_prefix)
		{
			die("Usage: php ".$argv[0]." [keys] [type] [alevel] [class] Note: type=".$student_prefix." requires year and class".PHP_EOL);
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
				
				for($o = 0; $o < count($allnums); $o++)
				{
					if($specifier == $student_prefix)
						$query_string = "INSERT INTO ".$db_table_num." (AUTHNUM, type, alevel, class) values(\"".$allnums[$o]."\", \"".$specifier."\", \"".$year."\", \"".$class."\");";
					else
						$query_string = "INSERT INTO ".$db_table_num." (AUTHNUM, type) values(\"".$allnums[$o]."\", \"".$specifier."\");";
					
					echo $allnums[$o].PHP_EOL;
					
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
		else
		{
			die("Usage: php ".$argv[0]." [keys] [type] [alevel] [class]".PHP_EOL);
		}
	}
	else
	{
		die("USAGE: php ".$argv[0]." [keys] [type] [alevel] [class]".PHP_EOL);
	}
?>
