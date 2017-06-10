<?php
	// Database Scrap
	$db_host = "localhost";
	$db_user = "root";
	$db_password = "zisch27";
	$db_name = "schule";
	
	$db_table_user = "users";
	$db_table_num = "authkey";
	$db_table_courses = "courses";
	
	// User things
	$student_prefix = 'S';
	$teacher_prefix = 'L';
	$admin_prefix = 'A';
	
	$pw_min_length = 4;
	
	// Authnum generation
	$key_charset = "0123456789ABCDEF";
	$key_length = 6;
	
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
	
	function createDatabases()
	{
		global $db_host;
		global $db_user;
		global $db_password;
		global $db_name;
		global $db_table_user;
		global $db_table_num;
		global $db_table_courses;
		
		global $key_length;
		
		$ref = new mysqli($db_host, $db_user, $db_password);
		
		if(!$ref->connect_error)
		{
			// Create Database
			$query_string = "CREATE DATABASE IF NOT EXISTS ".$db_name;
			if(!$ref->query($query_string))
				print_err("Fehler bei der MYSQL Query");
			if(!$ref->select_db($db_name))
				print_err("Fehler bei der Datenbank");
			
			
			// Create Tables
			$query_string = "CREATE TABLE IF NOT EXISTS ".$db_table_user."(ID mediumint AUTO_INCREMENT, username varchar(100), password varchar(40), vname varchar(100), nname varchar(100), email varchar(100), type varchar(1), alevel tinyint, class varchar(1), PRIMARY KEY(ID));";
			if(!$ref->query($query_string))
				print_err("Fehler bei der MYSQL Query");
			
			
			$query_string = "CREATE TABLE IF NOT EXISTS ".$db_table_num."(AUTHNUM varchar(".$key_length.") UNIQUE, type varchar(1), alevel tinyint, class varchar(1));";
			if(!$ref->query($query_string))
				print_err("Fehler bei der MYSQL Query");
			
			
			$query_string = "CREATE TABLE IF NOT EXISTS ".$db_table_courses."(ID tinyint, name varchar(100), reldate int, termdate int, owner varchar(100), allowed varchar(60), enableChange boolean, PRIMARY KEY(ID));";
			if(!$ref->query($query_string))
				print_err("Fehler bei der MYSQL Query");
		}
		else
			print_err("Die Verbindung zu MYSQL konnte nicht hergestellt werden");
		
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
		
		$ledate = $ledate + $deltaTime;
		
		return $ledate;
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
	
	function mkSignupOnlick($cname, $pname, $cid, $pid, $type)
	{
		return "onclick = \"signUp(".$cid.", ".$pid.", ".intval($type != $student_prefix).");\""; 
	}
	
	/*function isEmail($themail)
	{
		if(count($themail) < 6)
			return FALSE;
		else
		{
			$at = FALSE;
			$tld = FALSE;
			$pre = FALSE;
			$post = FALSE;
			$atpos = 0;
			$tldpos = 0;
			for($i = 0; $i < count($themail); $i++)
			{
				if($themail[$i] == '@')
				{
					if(!$at)
					{
						$at = TRUE;
						$atpos = $i;
						if($i > 0)
							$pre = TRUE;
						else
							return FALSE;
					}
					else
						return FALSE;
				}
			}
			
			for($i = count($themail) - 1; $i >= 0; $i++)
			{
				if(!$tld && $themail[$i] == '.' && $i < count($themail) - 1)
				{
					$tld = TRUE;
					$tldpos = $i;
				}
			}
			
			if($tldpos - $atpos >= 2)
				$post = TRUE;
			
			if($pre && $at && 
		}
	}*/
	
	function reDir($s)
	{
		echo "<script>".PHP_EOL;
		echo "window.onload = function() { var tempString = \"\"; for(i = 0; i < window.location.href.length; i++) { if(window.location.href.charAt(i) != '?') tempString += window.location.href.charAt(i); else break; } window.location.href = tempString + \"".$s."\"; }".PHP_EOL;
		echo "</script>".PHP_EOL;
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
	
	function getCheckboxOutput($s)
	{
		if(strtolower($s) == "on")
			return TRUE;
		else
			return FALSE;
	}
?>
