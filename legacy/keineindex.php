<?php
	session_start();
?>
<!DOCTYPE html>
<html>
	<head>
		<title>Fontaneum</title>
		<meta charset = "UTF-8"/>
		<link rel = "stylesheet" href = "style/main.css"/>
		<script src = "Javascript/main.js"></script>
		<script src = "Javascript/register.js"></script>
	</head>
	<body>
		<div class = "navbar basebar basediv">
			<ul>
				<li><a href = "index.php?section=home">Home</a></li>
				<li><a href = "/Downloadcenter" target = "_blank">Downloadcenter</a></li>
				<li><a href = "index.php?section=gallerie">Gallerie</a></li>
				<li><a href = "index.php?section=impressum">Impressum</a></li>
			</ul>
		</div>
		<div class = "login-div basebar basediv">
			<span>Hallo</span>
		</div>
		<div class = "vertical-navbar basebar basediv">
			<ul>
				<li><li><a href = "/Downloadcenter" target = "_blank">Downloadcenter</a></li>
				
				<li><a href = "index.php?section=impressum">Impressum</a></li>
			</ul>
		</div>
		<div class = "basebar basediv content">
			<?php
				if(isset($_SESSION['auth']))
				{
					if($_SESSION['auth'] == TRUE)
					{
						echo "<span class = \"Success\">Eingeloggt als ".$_SESSION['user']."</span>".PHP_EOL;
					}
				}
			?>
		</div>
	</body>
</html>