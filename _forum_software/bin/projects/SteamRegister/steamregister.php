<?php
	include $_SERVER['DOCUMENT_ROOT'].'/config/lib.php';
	
	$tpl = new template;
	$tpl->load($_SERVER['DOCUMENT_ROOT'].'/bin/projects/SteamRegister/steamregister.tpl');
	
	
	echo $tpl->compile('steamregister_main');
?>