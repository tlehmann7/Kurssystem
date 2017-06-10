<?php
	require_once("../config/db.php");
	initSession();
	
	if($_SESSION['auth'])
	{
		if(isset($_GET['courseid']))
		{
			$cid = intval($_GET['courseid']);
			if(isset($_GET['projectid']))
				$pid = intval($_GET['projectid']);
		}
		
		if(isset($cid))
		{
			$ref = new mysqli($db_host, $db_user, $db_password, $db_name);
			$canDownload = FALSE;
		
			if(!$ref->connect_error)
			{
				if($_SESSION['type'] == $admin_prefix)
					$canDownload = TRUE;
				else if($_SESSION['type'] == $teacher_prefix)
				{
					$query_string = "SELECT owner FROM ".$db_table_courses." WHERE ID = ".$cid.";";
					if($ref->query($query_string)->fetch_array(MYSQLI_ASSOC)['owner'] == $_SESSION['user'])
						$canDownload = TRUE;
					else
					{
						print_err("Sie besitzen diesen Kurs nicht");
						print_err("Nur Admins oder die jeweiligen Besitzer eines Kurses können entsprechende Daten abrufen");
					}
				}
				else if($_SESSION['type'] == $student_prefix)
				{
					print_err("Schüler können keine Daten abrufen");
				}
			
				if($canDownload)
				{
					if(!isset($pid))
					{
						$query_string = "SELECT name FROM ".$db_table_courses." WHERE ID = ".$cid.";";
						$cname = $ref->query($query_string)->fetch_array(MYSQLI_ASSOC)['name'];
						$query_string = "SELECT * FROM ".$cid.$courseInfoPostfix.";";
						$projInfos = $ref->query($query_string)->fetch_all(MYSQLI_ASSOC);
					
						header("Content-Disposition: attachment; filename=\"".$cname.".csv\"");
						header("Content-Type: application/vnd.ms-excel;");
						header("Pragma: no-cache");
						header("Expires: 0");
					
						$out = fopen("php://output", "w");
						$hline = csvquote($cname).$csvNewline;
						for($i = 0; $i < count($projInfos); $i++)
						{
							$hline = csvquote($projInfos[$i]['name']).$csvNewline;
							fwrite($out, $hline);
						
							$query_string = "SELECT * FROM ".$cid."_".$projInfos[$i]['ID'].$projectSignUpPostfix.";";
							$presults = $ref->query($query_string)->fetch_all(MYSQLI_ASSOC);
						
							$hline = csvquote("Vorname").$csvSep.csvquote("Nachname").$csvSep.csvquote("Email").$csvSep.csvquote("Abiturjahrgang").$csvSep.csvquote("Klasse").$csvNewline;
							fwrite($out, $hline);
							
							for($p = 0; $p < count($presults); $p++)
							{					
								$line = csvquote($presults[$p]['vname']).$csvSep.csvquote($presults[$p]['nname']).$csvSep.csvquote($presults[$p]['email']).$csvSep.csvquote($presults[$p]['alevel']).$csvSep.csvquote($presults[$p]['class']).$csvNewline;
								fwrite($out, $line);
							}
						
							fwrite($out, $csvNewline);
						}
						fclose($out);
					}
					else
					{
						$query_string = "SELECT name FROM ".$db_table_courses." WHERE ID = ".$cid.";";
						$cname = $ref->query($query_string)->fetch_array(MYSQLI_ASSOC)['name'];
						$query_string = "SELECT name FROM ".$cid.$courseInfoPostfix." WHERE ID = ".$pid.";";
						$pname = $ref->query($query_string)->fetch_array(MYSQLI_ASSOC)['name'];
						$query_string = "SELECT * FROM ".$cid."_".$pid.$projectSignUpPostfix.";";
						$presults = $ref->query($query_string)->fetch_all(MYSQLI_ASSOC);
					
						header("Content-Disposition: attachment; filename=\"".$pname.".csv\"");
						header("Content-Type: application/vnd.ms-excel;");
						header("Pragma: no-cache");
						header("Expires: 0");
					
						$out = fopen("php://output", "w");
						$hline1 = csvquote($cname." - ".$pname).$csvNewline;
						$hline2 = csvquote("Vorname").$csvSep.csvquote("Nachname").$csvSep.csvquote("Email").$csvSep.csvquote("Abiturjahrgang").$csvSep.csvquote("Klasse").$csvNewline;
						fwrite($out, $hline1.$hline2);
						for($i = 0; $i < count($presults); $i++)
						{
							$line = csvquote($presults[$i]['vname']).$csvSep.csvquote($presults[$i]['nname']).$csvSep.csvquote($presults[$i]['email']).$csvSep.csvquote($presults[$i]['alevel']).$csvSep.csvquote($presults[$i]['class']).$csvNewline;
							fwrite($out, $line);
						}
						fclose($out);
					}
				}
			}
			else
				print_err($db_connection_error_msg);
		}
	}
	else
		print_err("Es wird eine Authentifikation benötigt");
?>
