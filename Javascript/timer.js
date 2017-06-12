var timer = [];		
			
function spawnTimer(end, now, id)
{
	this.id = id;
	document.write("<span id = "+ String("timer" + this.id) + "></span>");
	
	this.ref = document.getElementById(String("timer" + this.id));
	this.deltaTime = end - now;
	
	this.days = 0;
	this.hours = 0;
	this.minutes = 0;
	this.seconds = 0;
	this.difftimer = 0;
	
	this.output = "";
	
	this.format = function(value, len)
	{				
		r = String(value);
		if(r.length >= len)
			return r;
		else
		{
			offset = len - r.length;
			for(i = 0; i < offset; i++)
			{
				r = String(0) + r;
			}
			return r;
		}
	}
	
	this.tick = function()
	{
		if(this.deltaTime > 0)
		{
			this.deltaTime -= 1;
			this.difftimer = this.deltaTime;
			this.days = (this.difftimer - (this.difftimer % (24 * 3600))) / (24 * 3600);
			this.difftimer -= this.days * 24 * 3600;
			this.hours = (this.difftimer - (this.difftimer % 3600)) / 3600;
			this.difftimer -= this.hours * 3600;
			this.minutes = (this.difftimer - (this.difftimer % 60)) / 60;
			this.difftimer -= this.minutes * 60;
			this.seconds = this.difftimer % 60;
		}
	}
	
	this.display = function()
	{
		if(this.deltaTime > 0)
			this.ref.innerHTML = "<h6>" + this.format(this.days, 2) + ":" + this.format(this.hours, 2) + ":" + this.format(this.minutes, 2) + ":" + this.format(this.seconds, 2) + "</h6>";
		else
			this.ref.innerHTML = "<h6>Die Zeit zum registrieren ist vorbei</h6>";
	}
}

function mkTimer(endStamp, now)
{
	timer.push(new spawnTimer(endStamp, now, timer.length));
}

window.onload = function()
{
	setInterval(function()
	{
		for(var i = 0; i < timer.length; i++)
		{
			timer[i].tick();
			timer[i].display();
		}
	}, 1000);
}
