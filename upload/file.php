<?php
	include('conf/config.php');
	$s = new server;
	$file = 'files/'.urldecode($_GET['file']);
	$file = file_exists($file) ? $file : 'css/img/exist.png';
	$ext = strtolower(end(explode('.', $file)));
	$mime = $s->mime($ext);
	header('Content-Type: '.$mime);
	if(strstr($file, '.zip') || strstr($file, '.rar'))
		header('Content-Disposition: attachment; filename="'.basename($file).'"');
	readfile($file);
?>