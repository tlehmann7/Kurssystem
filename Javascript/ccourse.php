var projectNum = 0;

var newProjectPrefix = <?php require_once("../config/db.php"); echo "\"".$newProjectPrefix."\""; ?>;
var newProjectNamePostfix = <?php require_once("../config/db.php"); echo "\"".$newProjectNamePostfix."\""; ?>;
var newProjectSpacePostfix = <?php require_once("../config/db.php"); echo "\"".$newProjectSpacePostfix."\""; ?>;

function addNew()
{
	var place = document.getElementById("newProjects");
	
	var name = "<input type = \"text\" name = \"" + newProjectPrefix + projectNum.toString() + newProjectNamePostfix + "\" required/>";
	var space = "<input type = \"number\" min = \"0\" name = \"" + newProjectPrefix + projectNum.toString() + newProjectSpacePostfix + "\"required/>";
	
	var toPlace = "<tr><td>" + name + "</td><td>" + space + "</td><td><input type = \"button\" value = \"x\" onclick =  \"remove(" + projectNum + ")\"></td></tr>\n<br>\n";
	
	place.innerHTML += toPlace;
	
	projectNum++;
}

function remove(index)
{
	var x = document.getElementById("projectTable");
	
	var names = [];
	var spaces = [];
	
	var counter = projectNum;
	while(counter >= 0)
	{
		if(counter != index)
		{
			var name = newProjectPrefix + counter.toString() + newProjectNamePostfix;
			if(document.getElementById(name) != null)
				names.push(document.getElementById(name).value);
			else
				break;
			
			var space = newProjectPrefix + counter.toString() + newProjectSpacePostfix;
			if(document.getElementById(space) != null)
				spaces.push(document.getElementById(space).value);
			else
				break;
		}
		
		counter--;
	}
	
	var node = document.getElementsByTagName("tr")[document.getElementsByTagName("tr").length - 1];
	node.parentNode.removeChild(node);
	
	if(names.length == spaces.length)
	{
		for(i = 0; i < names.length; i++)
		{
			var name = newProjectPrefix + i.toString() + newProjectNamePostfix;
			document.getElementById(name).value = names[i];
			
			var space = newProjectPrefix + i.toString() + newProjectSpacePostfix;
			document.getElementById(space).value = spaces[i];
		}
	}
	
	projectNum--;
}