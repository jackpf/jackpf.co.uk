var DDSPEED = 10;
var DDTIMER = 15;

// main function to handle the mouse events //
function DropDown(id1, id2, d)
{
	var h = document.getElementById(id1);
	var c = document.getElementById(id2);
	clearInterval(c.timer);
	if(d == 1)
	{
		clearTimeout(h.timer);
		if(c.maxh && c.maxh <= c.offsetHeight)
			return
		else if(!c.maxh)
		{
			c.style.display = 'block';
			c.style.height = 'auto';
			c.maxh = c.offsetHeight;
			c.style.height = '0px';
		}
		c.timer = setInterval(function(){ddSlide(c, 1)}, DDTIMER);
	}
	else
	{
		c.timer = setInterval(function(){ddSlide(c, -1)}, DDTIMER);
	}
}

// cancel the collapse if a user rolls over the dropdown //
function DropDownCancel(id1, id2)
{
	var h = document.getElementById(id1);
	var c = document.getElementById(id2);
	clearTimeout(h.timer);
	clearInterval(c.timer);
	if(c.offsetHeight < c.maxh)
	{
		c.timer = setInterval(function(){ddSlide(c, 1)}, DDTIMER);
	}
}

// incrementally expand/contract the dropdown and change the opacity //
function ddSlide(c, d)
{
	if(c.style.display == 'none')
		c.style.display = 'block';
	
	var currh = c.offsetHeight;
	var dist;
	if(d == 1)
	{
		dist = (Math.round((c.maxh - currh) / DDSPEED));
	}
	else
	{
		dist = (Math.round(currh / DDSPEED));
	}
	if(dist <= 1)
	{
		dist = 1;
	}
	c.style.height = currh + (dist * d) + 'px';
	c.style.opacity = currh / c.maxh;
	c.style.filter = 'alpha(opacity=' + (currh * 100 / c.maxh) + ')';
	if((currh <= 0 && d != 1) || (currh > c.maxh && d == 1))
	{
		clearInterval(c.timer);
		if(d == -1)
			c.style.display = 'none';
	}
}

var isDisplayed = false;
function ToggleDropDown(id1, id2, id3, t1, t2)
{
	if(!isDisplayed)
	{
		id3.innerHTML = t2;
		DropDown(id1, id2, 1);
	}
	else
	{
		id3.innerHTML = t1;
		DropDown(id1, id2, -1);
	}
	
	isDisplayed = !isDisplayed;
}