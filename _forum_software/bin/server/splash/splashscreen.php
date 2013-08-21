<?php
include_once $_SERVER['DOCUMENT_ROOT'].'/config/lib.php';
include_once '../HLServer.class.php';
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
	<head>
		<style type="text/css">
			/*<![CDATA[*/
				*
				{
					margin: 0;
					padding: 0;
				}
				body
				{
					background-color: black;
					color: white;
					text-align: center;
					text-decoration: none;
					padding: 25px;
				}
			/*]]>*/
		</style>
	</head>
	<body>
		<span style="font-size: 1.2em; color: #DC143C;">
			<?=$config_init->get_config('gameserver.splash.splash_message')?>
		</span>
		<h1>Servers</h1>
		<?$forks = array($config_init->get_config('gameserver.address'));
		$forks = array_merge($forks, array_filter(explode(',', preg_replace('/\s/', null, $config_init->get_config('gameserver.forks'))), create_function('$str', 'return !empty($str);'))); #function($str){return !empty($str);}
		
		foreach($forks as $id => $fork)
		{
			$servers[$id] = array('_address' => $fork, 'address' => (preg_match('/[0-9]+\:[0-9]+/', $fork)) ? $fork : gethostbyname(reset(explode(':', $fork))).':'.end(explode(':', $fork)));
			$hl_server = new hl_server($servers[$id]['address']);
			$servers[$id] += val::encode($hl_server->query_server());
		}
		
		foreach($servers as $server):?>
			<?=($server['gamename'] != null) ? strtoupper($server['gamename']) : '<span style="color: red;">Offline</span>'?> - <?=$server['address']?> <?=($server['_address'] != $server['address']) ? '('.$server['_address'].')' : null?> - <span style="color: <?=($server['numplayers'] == 0 && $server['maxplayers'] > 0) ? 'green' : 'red'?>;"><?=(int) $server['numplayers']?> / <?=(int) $server['maxplayers']?></span><br />
		<?endforeach?>
</html>