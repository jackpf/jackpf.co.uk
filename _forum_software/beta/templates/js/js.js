//handle for multiple window.onload functions
onload_functions = new Array();
function load(f)
{
	onload_functions[onload_functions.length] = f;
}
window.onload = function()
{
	for(var i = 0; i < onload_functions.length; i++)
	{
		eval(onload_functions[i]);
	}
}

function checkAll(checkType)
{
	for(var i = 0, check = document.getElementsByTagName('input'); i < check.length; i++)
	{
		if(check[i].type == 'checkbox' || check[i].type == 'radio')
		{
			check[i].checked = checkType;
		}
	}
}

/*
 *config.php
 */
function option(element, options)
{
	for(var i = 0; i < options.length; i++)
	{
		document.getElementById(options[i]).style.display = (document.getElementById(options[i]).id == element) ? 'block' : 'none';
		document.getElementById(options[i]+'_header').setAttribute('class', '');
	}
	
	document.getElementById(element+'_header').setAttribute('class', 'header-active');
}

//forum permissions
function populate_permission(permission)
{
	//var permissionForm = document.forms['thread_permission'];
	//set to form #0 so this function can be used in config.php as well as forum.php
	var permissionForm = document.forms[0];
	var input = 'specify_opt';
	var output = permission+'_specify';
	permissionForm.permission.options[output].value = permission+'('+permissionForm.elements[input].value+')';
}
//forum quotes
var quote = {
	//quotes
	quotes: new Array(),
	_quotes: new Array(),
	
	//default vars
	innerHTML: null,
	color: null,
	
	add: function(e, id)
	{
		//add quote
		if(!this.quotes[id])
		{
			//init default vars & edit post link
			if(this.innerHTML == null && this.color == null)
			{
				this.innerHTML = e.innerHTML;
				this.color = e.style.color;
				
				try
				{
					var post = document.getElementsByClassName('post')[0];
				}
				catch(err)
				{
					//IE hack
					for(var i = 0, post = document.getElementsByTagName('a'); i < post.length; i++)
					{
						if(post[i].innerHTML == 'Post')
						{
							var post = post[i];
							break;
						}
					}
				}
				
				with(post)
				{
					innerHTML = 'Quote';
					
					onclick = function()
					{
						//compile quotes
						for(var i = 0; i < quote.quotes.length; i++)
						{
							if(typeof quote.quotes[i] != 'undefined')
							{
								quote._quotes[quote._quotes.length] = quote.quotes[i];
							}
						}
						
						window.location = this.href.split('#')[0]+'&reply='+quote._quotes;
						return false;
					}
				}
			}
			
			this.quotes[id] = id;
			
			with(e)
			{
				innerHTML = 'Quoted';
				style.color = 'gray';
			}
		}
		//remove quote
		else
		{
			this.quotes[id] = null;
			
			with(e)
			{
				innerHTML = this.innerHTML;
				style.color = this.color;
			}
		}
	}
}
//profile email options
var email_options = {
	recipient: null,
	cc: function()
	{
		if(this.recipient == null)
		{
			this.recipient = document.getElementById('recipient').innerHTML;
		}
		
		var new_cc = document.forms['email'].new_cc;
		
		document.getElementById('recipient').innerHTML = (new_cc.value != '') ? this.recipient+', '+new_cc.value : this.recipient;
		document.forms['email'].recipient.value = new_cc.value;
	}
}

function setcookie(name, value, exp)
{
	var expire = new Date();
	expire.setDate(expire.getDate() + exp);
	document.cookie = name+'='+value+'; expires='+expire.toGMTString()+';path=/; domain='+window.location.hostname; 
}
function getcookie(name)
{
	if(document.cookie.length > 0)
	{
		start = document.cookie.indexOf(name + "=");
		if(start != -1)
		{
			start = start + name.length + 1;
			end = document.cookie.indexOf(";", start);
			if(end == -1)
				end = document.cookie.length;
			return unescape(document.cookie.substring(start, end));
		}
	}
}
function $_GET(type)
{
	if(location.href.match(type))
	{
		return location.href.split(type+'=')[1].split('&')[0];
	}
}
function enter(e)
{
	return (e.which == 13 || e.keyCode == 13) ? false : true;
}
function form_submit(uri)
{
	var form = document.createElement('form');
	
	with(form)
	{
		action = uri;
		id = 'form_submit';
		method = 'post';
	}
	
	document.body.appendChild(form);
	
	document.forms['form_submit'].submit();
}
function numVal(e)
{
	if(e.value.match(/[^0-9\-\.]/))
	{
		alert('This field must be numeric');
		e.value = e.value.slice(0, e.value.length - 1);
	}
}
function code(i, ii)
{
	var e = document.getElementsByTagName('textarea')[0];
	try
	{
		//IE hack
		if(navigator.userAgent.match(/MSIE/))
			throw "IE hack";
		
		var begin = e.value.substr(0, e.selectionStart);
		var end = e.value.substr(e.selectionEnd);
		var scroll = e.scrollTop;
		
		var c = e.value.substr(e.selectionStart, e.selectionEnd - e.selectionStart);
		var i = i+c+ii;
		
		e.value = begin+i+end;
		e.scrollTop = scroll;
	}
	catch(err)
	{
		e.value += i+ii;
		e.focus(e.value.length - 1);
	}
}

/*
 *fade
 */
function fade(e, _direction)
{
	var element = (typeof e == 'object') ? e : document.getElementById(e);
	
	if(!_direction)
		_direction = (element.style.display == 'none') ? 'in' : 'out';
	
	if(_direction == 'in' && element.style.display == 'none')
	{
		with(element)
		{
			//make sure that cunt is here
			style.display = 'block';
			//...but hide it
			style.opacity = 0;
			style.filter = 'alpha(opacity = 0)';
		}
	}
	
	var opacity = (_direction == 'in') ? parseFloat(element.style.opacity) + 0.1 : element.style.opacity - 0.1;
	
	element.style.opacity = opacity;
	//IE
	element.style.filter = 'alpha(opacity = '+(opacity * 100)+')';
	
	if(_direction == 'in')
	{
		if(opacity < 1)
		{
			setTimeout(function(){fade(element, 'in');}, 25);
		}
	}
	else if(_direction == 'out')
	{
		if(opacity > 0)
		{
			setTimeout(function(){fade(element, 'out');}, 20);
		}
		if(opacity <= 0)
		{
			//make sure that cunt is gone
			element.style.display = 'none';
		}
	}
}
/*
 *drag
 */
var drag = {
	element: '',
	offset: {x: 0, y: 0},
	
	init: function(element, e)
	{
		this.element = element;
		
		with(this.element)
		{
			style.position = 'absolute';
			style.opacity = 0.5;
			style.filter = 'alpha(opacity = 50)';
		}
		
		this.offset.x = this.mouse(e).x - parseInt((this.element.style.left > 0) ? this.element.style.left : this.element.offsetLeft);
		this.offset.y = this.mouse(e).y - parseInt((this.element.style.top > 0) ? this.element.style.top : this.element.offsetTop);
		
		document.onmousemove = function(event)
		{
			drag.move(event);
		}
		
		document.onmouseup = function()
		{
			document.onmousemove = null;
			drag.element.style.opacity = 1;
			drag.element.style.filter = 'alpha(opacity = 100)';
		}
		
		return false;
	},
	move: function(e)
	{
		with(this.element)
		{
			style.left = this.mouse(e).x - this.offset.x+'px';
			style.top = this.mouse(e).y - this.offset.y+'px';
		}
	},
	mouse: function(e)
	{
		if(!navigator.userAgent.match(/MSIE/))
		{
			return {x: e.pageX, y: e.pageY};
		}
		else
		{
			return {x: window.event.clientX, y: window.event.clientY};
		}
	}
}