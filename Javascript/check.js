var ref1;
var ref2;
var wheretogo;
var stopInt;

function checkifequal()
{
	if(ref1.value == ref2.value)
	{
		wheretogo.innerHTML = "";
	}
	else
	{
		wheretogo.innerHTML = "Die Passwörter stimmen nicht überein";
	}
}

window.onload = function()
{
	ref1 = document.getElementById('p1');
	ref2 = document.getElementById('p2');
	wheretogo = document.getElementById('tellpwnoteq');
	
	if(ref1 != null && ref2 != null)
	{
		stopInt = setInterval(checkifequal, 10);
	}
}
