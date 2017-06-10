<?php
	require_once("../config/db.php");

    function excel_file()
	{
		global $db_host;
		global $db_user;
		global $db_password;
		global $db_name;
		
		$list = array (
			array('aaa', 'bbb', 'ccc', 'dddd'),
			array('123', '456', '789'),
			array('"aaa"', '"bbb"')
		);
			
		header("Content-Disposition: attachment; filename=\"keys.csv\"");
		header("Content-Type: application/vnd.ms-excel;");
		header("Pragma: no-cache");
		header("Expires: 0");
		$out = fopen("php://output", "w");
		
		$ref = new mysqli($db_host, $db_user, $db_password, $db_name);
		
		$tab = ",";
		$newline = "\n";
		
		if(!$ref->connect_error)
		{
			$query_string = "SELECT * FROM authkey;";
			$authkey_data = $ref->query($query_string)->fetch_all(MYSQLI_ASSOC);
			
			$header = "AUTHNUM,type,alevel,class\n";
			fwrite($out, $header);
			for($i = 0; $i < count($authkey_data); $i++)
			{
				$tline = $authkey_data[$i]['AUTHNUM'].$tab.$authkey_data[$i]['type'].$tab.$authkey_data[$i]['alevel'].$tab.$authkey_data[$i]['class'].$newline;
				fwrite($out, $tline);
			}
		}
		else
			fwrite($out, "ERROR");
		
		fclose($out);
    }

	excel_file();
?>