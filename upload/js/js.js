function file(file)
{
	var element = document.getElementsByName('file');
	if(element.length > 0)
	{
		for(var i = 0; i < element.length; i++)
		{
			element[i].style.display = 'none';
		}
	}
	
	var element = document.createElement('input');
	element.setAttribute('name', 'file');
	element.setAttribute('style', 'width: 500px; margin: 0 auto;border: 1px solid green;');
	element.setAttribute('type', 'text');
	element.setAttribute('value', 'http://www.jackpf2.000space.com/file.php?file='+file);
	element.setAttribute('onclick', 'this.focus(); this.select();');
	element.setAttribute('readonly', 'true');
	document.body.appendChild(element);
}