<?php
$file = 'stuff/'.$_GET['file'];

header('Content-Type: application/octet-stream');
header('Content-Disposition: filename="'.basename($file).'"');
header('Content-Length: '.filesize($file));
echo file_get_contents($file);
?>