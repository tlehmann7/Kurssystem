<!DOCTYPE html>
<html>
	<head>
		<meta charset = "UTF-8"/>
		<title>Kurssystem</title>
		<?php
			// fix please, no redirect
		
			require_once("../config/db.php");
			initSession();

			if(isset($_GET['location']))
			{
				if($_GET['location'] == "logout")
				{
					session_destroy();
					reDir("?location=welcome");
				}
			}
			
			// To Be fixed 
		?>
		
		<meta name = "author" content = "Tom Reinhardt"/>
		<link rel = "stylesheet" href = "../style/main.css"/>
		<link rel = "stylesheet" href = "../style/input.css"/>
		<link rel = "stylesheet" href = "../style/navbar.css"/>
		<link rel = "stylesheet" href = "../style/extra.css"/>
		<script src = "../Javascript/register.js"></script>
		<script src = "../Javascript/coursesignup.js"></script>
		<script src = "../Javascript/ccourse.php"></script>
		<script src = "../Javascript/keygen.php"></script>
		<script src = "../Javascript/timer.js"></script>
		<script src = "../Javascript/check.js"></script>
	</head>
	<body>
		<div class = "blackground">
		</div>
		<div class = "navbar">
			<li>
				<ul>
					<a class = "guiA" href = "?location=showcourses">
						Aktuelle Kurse
					</a>
				</ul>
				<?php
					if($_SESSION['auth'])
					{
						if($_SESSION['type'] == $student_prefix)
						{
							linkGen("Meine Kurse", "?location=mycourses");
						}
						
						if($_SESSION['type'] == $teacher_prefix || $_SESSION['type'] == $admin_prefix)
						{
							linkGen("Kurs erstellen", "?location=createcourse");
							linkGen("Kurse bearbeiten", "?location=editcourses");
							linkGen("Keys generieren", "?location=keygen");
						}
					}
				?>
				<ul>
					<a class = "guiA" href = "?location=register">
						Registrieren
					</a>
				</ul>
				<ul>
					<?php
						if(!$_SESSION['auth'])
							echo "<a class = \"guiA\" href = \"?location=signin\">Anmelden</a>".PHP_EOL;
						else
							echo "<a class = \"guiA\" href = \"?location=logout\">Abmelden</a>".PHP_EOL;
					?>
				</ul>
			<li>
		</div>
		<div class = "footerdiv">
			<span>ver <?php echo $version; ?></span>
			<a class = "footer-link" target = "_blank" href = "http://www.tom-reinhardt.de">
				<img src = "../images/logo3_egg.png" id = "logo"/>
				Tom Reinhardt &copy; 2017
			</a>
		</div>
		<div id = "content" class = "wrapper70">
			<?php
				if(isset($_GET['location']))
				{
					switch($_GET['location'])
					{
						case "signin":
							include("login_includeable.php");
							echo PHP_EOL;
						break;
						case "register":
							include("register_includeable.php");
							echo PHP_EOL;
						break;
						case "showcourses":
							include("courses_includeable.php");
							echo PHP_EOL;
						break;
						case "createcourse":
							include("createcourse_includeable.php");
							echo PHP_EOL;
						break;
						case "welcome":
							//include("");
							echo PHP_EOL;
						break;
						case "keygen":
							include("numgen_includeable.php");
							echo PHP_EOL;
						break;
						case "mycourses":
							include("mycourses_includeable.php");
							echo PHP_EOL;
						break;
						case "editcourses":
							include("editcourses_includeable.php");
							echo PHP_EOL;
						break;
					}
				}
			?>
		</div>
	</body>
</html>
