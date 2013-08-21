function AJAX(form, response, url, p)
{
	function AJAX_encode(str)
	{
		return escape(str.replace(/\+/gi, '%2b'));
	}
	
	var url = (!url) ? '/main/misc.php?status=ajax' : url;
	var r   = document.getElementById((!response) ? 'ajax' : response);
		r   = (r == null) ? response : r;
	
	//init message
	var message = '';
	
	//grab form values / compile message
	var form = document.forms[form];
	if(form)
	{
		for(var i = 0; i < form.length; i++)
		{
			//do not send unchecked checkboxes/radios
			if((form.elements[i].type == 'checkbox' || form.elements[i].type == 'radio') && form.elements[i].checked == false)
			{
				continue;
			}
			message += AJAX_encode(form.elements[i].name)+'='+AJAX_encode(form.elements[i].value)+'&';
		}
	}
	
	//IE
	if(window.XMLHttpRequest)
	{
		var ajax = new XMLHttpRequest();
	}
	//other browsers
	else if(window.ActiveXObject)
	{
		var ajax = new ActiveXObject('Microsoft.XMLHTTP');
	}
	
	if(ajax)
	{
		//init ajax request
		ajax.open('POST', url);
		//send content header
		ajax.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded; Charset = UTF-8');
		
		ajax.onreadystatechange = function()
		{
			if(ajax.response = 200)
			{
				if((ajax.readyState == 1 || ajax.readyState == 2 || ajax.readyState == 3))
				{
					if(r && p !== false)
					{
						r.innerHTML = '<img src="/templates/css/img/icons/wysiwyg/load.gif" /> Fetching data from the server...';
					}
				}
				else if(ajax.readyState == 4)
				{
					if(r)
					{
						r.innerHTML = ajax.responseText;
					}
				}
			}
		}
		
		//send
		ajax.send(message);
	}
	
	//return the request
	return ajax;
}