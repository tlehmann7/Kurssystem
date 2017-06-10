function signUp(coursename, courseid, projectname, projectid, politemode)
{
	var tempString = String("");
	for(i = 0; i < window.location.href.length; i++)
	{
		if(window.location.href.charAt(i) == '?')
			break;
		else
			tempString += window.location.href.charAt(i);
	}
	
	if(politemode)
	{
		if(confirm(String("Sind Sie sicher, dass Sie sich f\u00fcr\ndas Projekt " + projectname + " registrieren m\u00f6chten?")))
			window.location.href = tempString + String("?location=showcourses&signup&courseid=" + encodeURIComponent(courseid.toString()) + "&projectid=" + encodeURIComponent(projectid));
	}
	else
		if(confirm(String("Bist du sicher, dass du dich f\u00fcr\ndas Projekt " + projectname + " registrieren m\u00f6chtest?")))
			window.location.href = tempString + String("?location=showcourses&signup&courseid=" + encodeURIComponent(courseid.toString()) + "&projectid=" + encodeURIComponent(projectid));
}
