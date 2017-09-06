<?php
	require_once("../config/db.php");
	initSession();
	
	//$dauth = true;
	
	//$_GET['timestamp'] = $argv[1];
	
	if($_SESSION['auth'])
	{
		if($_SESSION['type'] == $admin_prefix)
		{
			if(isset($_GET['timestamp']))
			{
				$ref = new mysqli($db_host, $db_user, $db_password, $db_name);
				
				if(!$ref->connect_error)
				{
					$stamp = $_GET['timestamp'];
				
					$query_string = "SELECT * FROM ".$db_table_num." WHERE timestamp = ".$stamp.";";
					
					$keydata = $ref->query($query_string)->fetch_all(MYSQLI_ASSOC);
					
					if(count($keydata) > 0)
					{
						if($keydata[0]['type'] == $student_prefix)
							loadCSV("keys", array("Code", "Typ", "Klasse/Tutorium"), $keydata, array("AUTHNUM", "type", "class"));
						else
							loadCSV("keys", array("Code", "Typ"), $keydata, array("AUTHNUM", "type"));
					}
				}
				else
					print_err("No DB");
				
				$ref->close();
			}
			else
				print_err("No set");
		}
		else
			print_err("No Type");
	}
	else
		print_err("No Auth");
?>
