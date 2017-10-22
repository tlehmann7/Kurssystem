<?php
	require_once("config/db.php");
	initSession();
	
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
							loadCSV($keydata[0]['type'].$keydata[0]['alevel']." - ".$keydata[0]['class']." - keys", array("Code", "Typ", "Abiturjahrgang", "Klasse/Tutorium"), $keydata, array("AUTHNUM", "type", "alevel", "class"));
						else if($keydata[0]['type'] == $teacher_prefix)
							loadCSV("Lehrerkeys", array("Code", "Typ"), $keydata, array("AUTHNUM", "type"));
						else if($keydata[0]['type'] == $admin_prefix)
							loadCSV("Adminkeys", array("Code", "Typ"), $keydata, array("AUTHNUM", "type"));
						else
							loadCSV("keys", array("Code", "Typ"), $keydata, array("AUTHNUM", "type"));
					}
				}
				
				$ref->close();
			}
		}
	}
?>
