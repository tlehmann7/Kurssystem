function upper(id)
{
	document.getElementById(id).value = document.getElementById(id).value.toUpperCase();
	var charset = "0123456789ABCDEFX";
	var saveFalse = [];
	
	var found = false;
	for(var i = 0; i < document.getElementById(id).value.length; i++)
	{
		found = false;
		for(var p = 0; p < charset.length; p++)
		{
			if(document.getElementById(id).value.charAt(i) == charset.charAt(p))
			{
				found = true;
				break;
			}
		}
		
		if(!found)
		{
			var foundhere = false;
			for(var p = 0; p < saveFalse.length; p++)
			{
				if(saveFalse[p] == document.getElementById(id).value.charAt(i))
				{
					foundhere = true;
					break;
				}
			}
			
			if(!foundhere)
				saveFalse.push(document.getElementById(id).value.charAt(i));
		}
	}
	
	if(saveFalse.length > 0)
	{
		var msg = "Der Code enthält kein ";
		
		for(var i = 0; i < saveFalse.length; i++)
		{
			if(saveFalse[i] == " ")
				msg += "Leerzeichen";
			else if(saveFalse[i] == "	")
				msg += "Tabulator";
			else
				msg += document.getElementById(id).value.charAt(saveFalse[i]);
			if(i + 1 != saveFalse.length)
				msg += ", ";
		}
		
		document.getElementById(String(id + "-info")).innerHTML = msg;
	}
	else
		document.getElementById(String(id + "-info")).innerHTML = "";
}

window.onkeypress = function()
{
	event = event || window.event;

	charCode = event.charCode || event.keyCode;

	if(charCode == 18)
	{
		document.getElementById("form").submit();
	}
}
