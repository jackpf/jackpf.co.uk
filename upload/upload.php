<?php
	$s = new server;
	
	if(!isset($_GET['upload']))
	{
		echo '<script type="text/javascript" src="./js/upload.js"></script>
		<form id="upload" action="'.$_SERVER['REQUEST_URI'].'&upload=true" method="post" target="target" enctype="multipart/form-data"><div>
			<input type="file" name="file" /><br />
			<input type="submit" name="submit" value="Upload" />
			<div id="response"></div>
		</div></form>';
		echo $s->public;
	}
	else
	{
		if($_FILES['error'] > 0)
		{
			$s->respond('An error has occured.');
		}
		$_FILES['name'] = (isset($_GET['filename'])) ? urldecode($_GET['filename']) : $_FILES['name'];
		$s->val($_FILES['name']);
		$_FILES['name'] = $s->fname_check($_FILES['name']);
		move_uploaded_file($_FILES['tmp_name'], $s->dir.'/'.$_FILES['name']);
		if(file_exists($s->dir.'/'.$_FILES['name']))
		{
			$s->respond('Your file has been uploaded - <a href="./file.php?file='.urlencode(str_ireplace('files/', null, $s->dir)).'/'.$_FILES['name'].'">'.$_FILES['name'].'</a>.<br /><br />URL:<br /><input type="text" style="width: 500px; margin: 0 auto;border: 1px solid green;" value="http://www.jackpf.co.uk/upload/file.php?file='.urlencode(str_ireplace('files/', null, $s->dir)).'/'.$_FILES['name'].'" onclick="this.focus(); this.select();" readonly="true" />');
		}
		else
		{
			$s->respond('An error has occured.');
		}
	}
?>