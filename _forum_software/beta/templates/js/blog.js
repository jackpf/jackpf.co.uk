function s(e)
{
	if((e && (e.which == 13 || e.keyCode == 13)) || !e)
	{
		window.location = '/blog/search='+document.forms['search'].search.value; /*uri*/
		
		return false;
	}
}
var slide = {
	//element
	e: '',
	//initial height offset
	y: '',
	//velocity; increases when height < 1/2 y & then decreases thereafter
	velocity: 0,
	//to <decrease> the chance of it bugging up.
	buffer: 0,
	
	//initial function
	init: function(element)
	{
		//define element
		this.e = element;
		//define initial height offset
		this.y = this.e.offsetHeight;
		
		//style/hide element...
		this.e.style.overflow = 'hidden';
		this.e.style.display = 'none';
		this.e.style.height = 0;
	},

	//main handler for calling functions
	exec: function(direction)
	{
		switch(direction)
		{
			case 'in':
				//exec s if height is <= 0
				if(parseInt(this.e.style.height) <= 0)
				{
					//set/reset velocity to initial value
					this.velocity = 2;
					//reset buffer
					this.buffer = 0;
					
					//style/show element
					this.e.style.display = 'block';
					//init expand
					setTimeout(function(_this){_this._in();}, 250, this);
				}
			break;
			case 'out':
				//exec s if height is >= offset height
				if(parseInt(this.e.style.height) >= this.y)
				{
					//reset buffer
					this.buffer = 0;
					
					//init contract
					setTimeout(function(_this){_this._out();}, 1000, this);
				}
				//try again in a second
				else
				{
					setTimeout(function(_this){_this.exec('out');}, 1000, this);
				}
			break;
		}
	},

	//slide in
	_in: function()
	{
		//make sure element is visible
		if(this.e.style.display != 'block')
		{
			this.e.style.display = 'block';
		}
		
		//define height
		var height = parseInt(this.e.style.height);
		//increase/decrease velocity depending on whether the height is <|> half of the original offset
		this.velocity = (height <= this.y / 2) ? this.velocity * 1.15 : this.velocity * 0.9;
		
		//increment buffer
		this.buffer += 1;
		
		//update height
		this.e.style.height = (height + this.velocity)+'px';
		
		//re-call function(only if it hasn't been called > 100 times already)
		if(height <= this.y && this.buffer < 100)
		{
			setTimeout(function(_this){_this._in();}, 10, this);
		}
	},

	_out: function()
	{
		//define height
		var height = parseInt(this.e.style.height);
		
		//update height - height / 10 seems to work well enough...
		this.e.style.height = (height - (height / 5))+'px';
		
		//increment buffer
		this.buffer += 1;
		
		//re-call function(only if it hasn't been called > 100 times already)
		if(height >= 1 && this.buffer < 100)
		{
			setTimeout(function(_this){_this._out();}, 10, this);
		}
		else
		{
			//we're done here...
			this.e.style.display = 'none';
			this.e.style.height = 0;
		}
	}
}

var textarea_drag = {
	element: '',
	offset: {y: 0},
	
	init: function(element, e)
	{
		this.element = element;
		
		this.offset.y = this.element.offsetTop + 75;
		
		document.onmousemove = function(event)
		{
			textarea_drag.move(event);
		}
		
		document.onmouseup = function()
		{
			document.onmousemove = null;
		}
		
		return false;
	},
	move: function(e)
	{
		this.element.style.height = parseInt(drag.mouse(e).y) - this.offset.y+'px';
	}
}

function AJAXComment(action)
{
	if(!navigator.userAgent.match(/MSIE/))
	{
		AJAX('AJAXComment', document.getElementsByTagName('body')[0], action, false);
	}
	else
	{
		var form = document.forms['AJAXComment'];
		form.action = action;
		form.submit();
	}
}