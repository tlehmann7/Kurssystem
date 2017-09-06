function disablePartialForm(thisid)
{
	var x = document.getElementById("class");
	var y = document.getElementById("alevel");
	var z = document.getElementById("student_disable");
	if(document.getElementById(thisid).value != "<?php require_once("../config/db.php"); echo $student_prefix; ?>")
	{
		x.setAttribute("class", "off");
		y.setAttribute("class", "off");
		x.setAttribute("disabled", "");
		y.setAttribute("disabled", "");
		z.setAttribute("class", "off");
	}
	else
	{
		x.removeAttribute("disabled");
		y.removeAttribute("disabled");
		x.removeAttribute("class");
		y.removeAttribute("class");
		z.removeAttribute("class");
	}
}
