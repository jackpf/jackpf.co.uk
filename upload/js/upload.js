function upload()
{
	//IE hack
	try
	{
		var element = document.createElement('<iframe name="target">');
	}
	catch(e)
	{
		var element = document.createElement('iframe');
	}
	element.setAttribute('name', 'target');
	element.style.display = 'none';
	document.body.appendChild(element);
	
	var file = document.forms['upload'];
	file.onsubmit = function()
	{
		file.submit.disabled = true;
		var response = document.getElementById('response');
		response.innerHTML = '<img src="/css/img/load.gif" />';
	}
}
function response(r)
{
	var file = document.forms['upload'];
	document.getElementById('response').innerHTML = r;
	file.submit.disabled = false;
}
window.onload = function()
{
	upload();
}