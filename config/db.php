<?php

	$version = "1.2";
	// Database Scrap
	$db_host = "localhost";
	$db_user = "root";
	$db_password = "zisch27";
	$db_name = "schule";
	
	$db_table_user = "users";
	$db_table_num = "authkey";
	$db_table_courses = "courses";
	$db_table_logs = "logs";
	
	// log shit
	$log_login = "login";
	$log_register = "register";
	$log_signup = "signup";
	$log_change = "change";
	$log_refused = "refused";
	$log_reqpwdchange = "requestpasswordchange";
	
	// User things
	$student_prefix = 'S';
	$teacher_prefix = 'L';
	$admin_prefix = 'A';
	
	$pw_min_length = 4;
	
	// Authnum generation
	$key_charset = "0123456789ABCDEF";
	$key_length = 7;
	
	// Course standard release time
	$deltaTime = 17 * 3600; // 18 Uhr
	
	// allowed alevels Prefix
	$allowedCPrefix = "alevel";
	$newProjectPrefix = "newProject";
	
	$newProjectNamePostfix = "_name";
	$newProjectSpacePostfix = "_maxspace";
	$allowedClassesPostfix = "_class";
	
	// table postfixes
	$courseObservationPostfix = "_observation";
	$courseInfoPostfix = "_info";
	$projectSignUpPostfix = "_project";
	
	// Format
	$error_tag = "span";
	$error_class = "Error";
	
	$success_tag = "span";
	$success_class = "Success";
	
	$normal_tag = "span";
	$normal_class = "Normal";
	
	$signal_tag = "span";
	$signal_class = "Signal";
	
	$db_query_error_msg = "Ein Fehler ist bei der MYSQL-Query aufgetreten";
	$db_connection_error_msg = "Ein Fehler ist bei der MYSQL-Verbindung aufgetreten";
	
	$auth_required_msg = "Es wird eine Authentifikation benötigt";
	
	$linkClass = "guiA";
	
	// CSV
	
	$csvSep = ",";
	$csvNewline = "\n";
	
	// debug
	$dauth = false;
	
	// mail
	$email_caption = "Kurssystem - ";
	$mail_reset_key_length = 40;
	$mail_template_insert_keyword = "%HERE%";
	$mail_template_filename = "config/email.template";
	$mail_link_to_script = "https://fontaneum.gq/einschreibung/?location=changepw&pwdresetkey="; // please change afterwords
	
	function createDatabases()
	{
		global $db_host;
		global $db_user;
		global $db_password;
		global $db_name;
		global $db_table_user;
		global $db_table_num;
		global $db_table_courses;
		global $db_table_logs;
		
		global $key_length;
		
		$ref = new mysqli($db_host, $db_user, $db_password);
		
		if(!$ref->connect_error)
		{
			// Create Database
			$query_string = "CREATE DATABASE IF NOT EXISTS ".$db_name.";";
			if(!$ref->query($query_string))
				print_err($db_query_error_msg);
			if(!$ref->select_db($db_name))
				print_err($db_query_error_msg);
			
			
			// Create Tables
			$query_string = "CREATE TABLE IF NOT EXISTS ".$db_table_user."(ID MEDIUMINT AUTO_INCREMENT, username VARCHAR(100) UNIQUE, password VARCHAR(40), vname VARCHAR(100), nname VARCHAR(100), email VARCHAR(100), type VARCHAR(1), alevel TINYINT, class VARCHAR(10), pwdresetkey VARCHAR(40) UNIQUE, ips TEXT, PRIMARY KEY(ID));";
			if(!$ref->query($query_string))
				print_err($db_query_error_msg);
			
			
			$query_string = "CREATE TABLE IF NOT EXISTS ".$db_table_num."(AUTHNUM VARCHAR(".$key_length.") UNIQUE, timestamp INT, type VARCHAR(10), alevel TINYINT, class VARCHAR(10));";
			if(!$ref->query($query_string))
				print_err($db_query_error_msg);
			
			
			$query_string = "CREATE TABLE IF NOT EXISTS ".$db_table_courses."(ID TINYINT, name VARCHAR(100), reldate INT, termdate INT, owner VARCHAR(100), allowed VARCHAR(60), enableChange BOOLEAN, PRIMARY KEY(ID));";
			if(!$ref->query($query_string))
				print_err($db_query_error_msg);
				
			$query_string = "CREATE TABLE IF NOT EXISTS ".$db_table_logs."(ID BIGINT UNSIGNED AUTO_INCREMENT, IP VARCHAR(15), username VARCHAR(100), timestamp INT, action TEXT, PRIMARY KEY(ID));";
			if(!$ref->query($query_string))
				print_err($db_query_error_msg);
		}
		else
			print_err($db_connection_error_msg);
		
		$ref->close();
	}
	
	function isGiven($db_table, $db_column, $value)
	{
		global $db_host;
		global $db_user;
		global $db_password;
		global $db_name;
		
		$ref = new mysqli($db_host, $db_user, $db_password, $db_name);
		
		if(!$ref->connect_error)
		{
			$value = $ref->real_escape_string($value);
			if(is_string($value))
					$value = "\"".$value."\"";

			$query_string = "SELECT * FROM `".$db_table."` WHERE ".$db_column." = ".$value.";";
			$result = $ref->query($query_string);
			if(!is_bool($result))
			{
				if(count($result->fetch_array(MYSQLI_NUM)) > 0)
				{
					return TRUE;
				}
				else
				{
					return FALSE;
				}
			}
			else
				return FALSE;
		}
		$ref->close();
	}
	
	function saveString($s)
	{
		$disallowedCharset = " ()[];\"\'";
	
		$found = false;
		$return = $s;
		for($i = 0; $i < strlen($s); $i++)
		{
			if(!$found)
				for($p = 0; $p < strlen($disallowedCharset); $p++)
				{
					if($s[$i] == $disallowedCharset[$p])
					{
						$found = true;
						break;
					}
					else
					{
						$return[$i] = $s[$i];
					}
				}
			else
				$return[$i] = " ";
		}
		
		return $return;
	}
	
	function print_err($msg)
	{
		global $error_tag;
		global $error_class;
		echo "<".$error_tag." class = \"".$error_class."\">".$msg."</".$error_tag.">".PHP_EOL;
	}
	
	function print_success($msg)
	{
		global $success_tag;
		global $success_class;
		echo "<".$success_tag." class = \"".$success_class."\">".$msg."</".$success_tag.">".PHP_EOL;
	}
	
	function print_normal($msg)
	{
		global $normal_tag;
		global $normal_class;
		echo "<".$normal_tag." class = \"".$normal_class."\">".$msg."</".$normal_tag.">".PHP_EOL;
	}
	
	function print_signal($msg)
	{
		global $signal_tag;
		global $signal_class;
		echo "<".$signal_tag." class = \"".$signal_class."\">".$msg."</".$signal_tag.">".PHP_EOL;
	}
	
	function courseStamp($ledate)
	{
		global $deltaTime;
		$timeNow = time();
		
		// seconds
		$ledate = $ledate - $timeNow % 60;
		$timeNow = $timeNow - $timeNow % 60;
		
		// minutes
		$ledate = $ledate - $timeNow % 3600;
		$timeNow = $timeNow - $timeNow % 3600;
		
		// hours
		$ledate = $ledate - $timeNow % 86400;
		$timeNow = $timeNow - $timeNow % 86400;
		
		//$ledate = $ledate + $deltaTime;
		
		return $ledate;
	}
	
	function getDays($s)
	{
		return date_create_from_format("d.m.Y-H:i:s", $s."-00:00:00")->getTimestamp() + 3600;
	}
	
	function getTime($s)
	{
		return date_create_from_format("d.m.Y-H:i:s", "01.01.1970-".$s)->getTimestamp();
	}
	
	function initSession()
	{
		session_start();
		
		if(!isset($_SESSION['auth']))
			$_SESSION['auth'] = FALSE;
		
		if(!isset($_SESSION['type']))
			$_SESSION['type'] = "";
		
		if(!isset($_SESSION['user']))
			$_SESSION['user'] = "";
		
		if(!isset($_SESSION['class']))
			$_SESSION['class'] = "";
		
		if(!isset($_SESSION['alevel']))
			$_SESSION['alevel'] = "";
	}
	
	/*function containsAny($s, $charset)
	{
		for($i = 0; $i < count($charset); $i++)
		{
			for($p = 0; $p < count($s); $p++)
			{
				if($s[$p] == $charset[$i])
					return true;
			}
		}
		
		return false;
	}*/
	
	function messageUser($msg)
	{
		echo "<script>".PHP_EOL;
		echo "window.onload = function() { alert(\"".$msg."\"); }".PHP_EOL;
		echo "</script>".PHP_EOL;
	}
	
	function csvquote($s)
	{
		return "\"".$s."\"";
	}
	
	function mkSignupOnclick($cname, $pname, $cid, $pid, $type)
	{
		return "onclick = \"signUp(".$cid.", ".$pid.", ".intval($type != $student_prefix).");\""; 
	}
	
	function reDir($s)
	{
		echo "<script>".PHP_EOL;
		echo "window.onload = function() { var tempString = \"\"; for(i = 0; i < window.location.href.length; i++) { if(window.location.href.charAt(i) != '?') tempString += window.location.href.charAt(i); else break; } window.location.href = tempString + \"".$s."\"; }".PHP_EOL;
		echo "</script>".PHP_EOL;
	}
	
	function hardReDir($s)
	{
		header("Location: ".$s);
	}
	
	function linkGen($text, $href)
	{
		global $linkClass;
		echo "<ul>".PHP_EOL;
		echo "<a class = \"".$linkClass."\" href = \"".$href."\">".$text."</a>".PHP_EOL;
		echo "</ul>".PHP_EOL;
	}
	
	function isAllowed($allowString, $ualevel, $uclass)
	{
		$yearOk = FALSE;
		$classOk = FALSE;
		$alevels = explode(";", $allowString);
		for($i = 0; $i < count($alevels); $i++)
		{
			if($yearOk && $classOk)
				break;
			
			$yearOk = FALSE;
			$classOk = FALSE;
			$classes = explode("#", $alevels[$i]);
			for($p = 0; $p < count($classes); $p++)
			{
				if($p == 0 && $classes[$p] == $ualevel)
				{
					$yearOk = TRUE;
				}
				else if($classes[$p] == $uclass)
				{
					$classOk = TRUE;
					break;
				}
			}
		}
		return $yearOk && $classOk;
	}
	
	function loadCSV($cname, $headerStrings, $arr, $keys)
	{
		global $csvSep;
		global $csvNewline;
		header("Content-Disposition: attachment; filename=\"".$cname.".csv\"");
		header("Content-Type: application/vnd.ms-excel;");
		header("Pragma: no-cache");
		header("Expires: 0");
		$out = fopen("php://output", "w");
		
		$hline = "";
		for($i = 0; $i < count($headerStrings); $i++)
		{
			$hline .= csvquote($headerStrings[$i]);
			if($i + 1 < count($headerStrings))
				$hline .= $csvSep;
			else
				$hline .= $csvNewline;
		}
		
		fwrite($out, $hline);
		
		for($i = 0; $i < count($arr); $i++)
		{
			$ts = "";
			for($ki = 0; $ki < count($keys); $ki++)
			{
				$ts .= csvquote($arr[$i][$keys[$ki]]);
				if($ki + 1 < count($keys))
					$ts .= $csvSep;
			}
			$ts .= $csvNewline;
			fwrite($out, $ts);
		}
		fclose($out);
	}
	
	function getCheckboxOutput($s)
	{
		return strtolower($s) == "on";
	}
	
	function getFilledTemplate($tofill)
	{
		global $mail_template_filename;
		global $mail_template_insert_keyword;
		
		$file = fopen($mail_template_filename, "r");
		
		$fs = filesize($mail_template_filename);
		
		$tempString = "";
		$content = "";
		
		for($ch = 0; $ch < $fs; $ch++)
		{
			$char = fread($file, 1);
			
			if($char == $mail_template_insert_keyword[strlen($tempString)])
				$tempString .= $char;
			else
			{
				$content .= $tempString.$char;
				$tempString = "";
			}
			
			if(strlen($mail_template_insert_keyword) == strlen($tempString))
			{
				$tempString = "";
				$content .= $tofill;
			}
		}
		
		fclose($file);
		
		return $content;
	}
	
	function RequestPwdReset($email)
	{
		global $key_charset;
		global $email_caption;
		global $mail_link_to_script;
		global $mail_reset_key_length;
		global $db_table_user;
		global $db_name;
		global $db_password;
		global $db_user;
		global $db_host;
		
		$ref = new mysqli($db_host, $db_user, $db_password, $db_name);
		
		if(!$ref->connect_error)
		{
			$email = $ref->real_escape_string($email);
			if(isGiven($db_table_user, "email", $email) || isGiven($db_table_user, "username", $email))
			{
				$query_string = "SELECT email FROM ".$db_table_user." WHERE email = \"".$email."\" or username = \"".$email."\";";
				$rmail = $ref->query($query_string)->fetch_all(MYSQLI_ASSOC)[0]['email'];
				
				$key = "";
				do
				{
					$key = "";
					for($i = 0; $i < $mail_reset_key_length; $i++)
					{
						$key .= $key_charset[random_int(0, strlen($key_charset) - 1)];
					}
				}
				while(isGiven($db_table_user, "pwdresetkey", $key));
				
				$query_string = "UPDATE ".$db_table_user." SET pwdresetkey = \"".$key."\" WHERE email = \"".$email."\" or username = \"".$email."\";";
				$ref->query($query_string);
				
				$headers[] = 'MIME-Version: 1.0';
				$headers[] = 'Content-type: text/html; charset=iso-8859-1';
				
				return mail($rmail, $email_caption."Passwort vergessen", getFilledTemplate($mail_link_to_script.$key), implode("\r\n", $headers));
			}
			else
				return FALSE;
		}
		else
		{
			echo "DB_ERR".PHP_EOL;
			return false;
		}
	}
	
	function logAction($username, $action)
	{
		global $db_host;
		global $db_user;
		global $db_password;
		global $db_name;
		global $db_table_user;
		global $db_table_logs;
		
		$ref = new mysqli($db_host, $db_user, $db_password, $db_name);
		
		if(!$ref->connect_error)
		{			
			if(isset($_SERVER['REMOTE_ADDR']) && !empty($_SERVER['REMOTE_ADDR']))
			{
				$currIP = $_SERVER['REMOTE_ADDR'];
				$query_string = "SELECT ips FROM ".$db_table_user." WHERE username = \"".$username."\";";
				$ips = $ref->query($query_string)->fetch_array(MYSQLI_ASSOC)['ips'];
				
				$query_string = "INSERT INTO ".$db_table_logs."(IP, timestamp, username, action) values(\"".$currIP."\", ".time().", \"".$username."\", \"".implode(";", $action)."\");";
				$ref->query($query_string);
			
				$arrip = explode(";", $ips);
				$found = false;
				for($i = 0; $i < count($arrip); $i++)
				{
					if($arrip[$i] == $currIP)
					{
						$found = true;
						break;
					}
				}
			
				if(!$found)
				{
					$arrip[] = $currIP;
					$ips = implode(";", $arrip);
				
					$query_string = "UPDATE ".$db_table_user." set ips = \"".$ips."\" WHERE username = \"".$username."\";";
					$ref->query($query_string);
				}
			}
			else
			{
				$query_string = "INSERT INTO ".$db_table_logs."(timestamp, username, action) values(".time().", \"".$username."\", \"".implode(";", $action)."\");";
				$ref->query($query_string);
			}
		}
		
		$ref->close();	
	}

	function trimName($s)
	{
		$step1 = "";
		for($it = 0; $it < strlen($s); $it++)
		{
			switch($s[$it])
			{
				case " ":
					$step1 .= "_";
				break;
				case "	":

				break;
				case "\n":

				break;
				default:
					$step1 .= $s[$it];
				break;
			}
		}

		$step2 = "";
		$hasBegun = false;
		for($it = 0; $it < strlen($step1); $it++)
		{
			if(!$hasBegun)
			{
				if($step1[$it] != "_")
				{
					$hasBegun = true;
					$step2 .= $step1[$it];
				}
			}
			else
			{
				$step2 .= $step1[$it];
			}
		}

		$ret = "";
		$step2 = strrev($step2);
		$hasBegun = false;
		for($it = 0; $it < strlen($step2); $it++)
		{
			if(!$hasBegun)
			{
				if($step2[$it] != "_")
				{
					$hasBegun = true;
					$ret .= $step2[$it];
				}
			}
			else
			{
				$ret .= $step2[$it];
			}
		}

		return strrev($ret);
	}
	
	function getAllowInfo()
	{
		global $db_host;
		global $db_user;
		global $db_password;
		global $db_name;
		global $db_table_user;
		
		$ref = new mysqli($db_host, $db_user, $db_password, $db_name);
	
		if(!$ref->connect_error)
		{
			$ret = array();
		
			$query_string = "SELECT DISTINCT alevel FROM ".$db_table_user." WHERE alevel IS NOT NULL ORDER BY alevel ASC;";
			$alevels = $ref->query($query_string)->fetch_all(MYSQLI_ASSOC);
		
			for($i = 0; $i < count($alevels); $i++)
			{
				$prePush = new stdClass;
				$prePush->alevel = $alevels[$i]['alevel'];
			
				$query_string = "SELECT DISTINCT class FROM ".$db_table_user." WHERE alevel = ".$alevels[$i]['alevel']." ORDER BY class ASC;";
				$classes = $ref->query($query_string)->fetch_all(MYSQLI_ASSOC);
			
				$classarr = array();
				for($p = 0; $p < count($classes); $p++)
				{
					$classarr[] = $classes[$p]['class'];
				}
			
				$prePush->classes = $classarr;
			
				$ret[] = $prePush;
			}
		
			return $ret;
		}
		else
			return (new stdClass);
	}
?>
