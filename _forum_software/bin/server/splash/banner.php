<?php
	include $_SERVER['DOCUMENT_ROOT'].'/config/lib.php';
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
	<head>
		<style type="text/css">
			/*<![CDATA[*/
				body
				{
					color: black;
					font-size: 1.2em;
					background: none;
				}
			/*]]>*/
		</style>
	</head>
	<body>
		<?=$config_init->get_config('gameserver.splash.banner_message')?>
	</body>
</html>