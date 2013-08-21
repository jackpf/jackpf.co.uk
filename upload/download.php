<?php
	$file = 'files/'.urldecode($_GET['file']);
	header('Pragma: public');
	header('Expires: 0');
	header('Cache-Control: must-revalidate, post-check = 0, pre-check = 0');
	header('Cache-Control: private', false);
	header('Content-Type: application/zip');
	header('Content-Disposition: attachment; filename="'.basename($file).'"');
	header('Content-Transfer-Encoding: binary');
	header('Content-Length: '.filesize($file));
	readfile($file);
?>