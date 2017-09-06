<?php
	$out = fopen("php://output", "w");
	
	fwrite($out, "Hallo Welt\r\n");
	
	fclose($out);
?>
