<?php
include_once $_SERVER['DOCUMENT_ROOT'].'/config/lib.php';
include $_SERVER['DOCUMENT_ROOT'].'/bin/server/VentServer.class.php';

$config_init->load($_SERVER['DOCUMENT_ROOT'].'/bin/server/servers.cfg');

$servers = $config_init->parse()->get_object()->voiceservers;
$vars = new stdclass;
$vars->server = array();

foreach($servers as $id => $server)
{
	$vars->server[$id] = array('address' => $server, 'password' => $config_init->get_config('gameserver.auth.password'));
	$vent_server = new vent_server($vars->server[$id]['address']);
	$vars->server[$id] += $vent_server->query_server2();
}

class vent_server_display extends vent_server
{
	private $channels, $players, $displayed = array();
	
	public function display_server($server)
	{
		$this->channels = $server['teams'];
		$this->players = $server['players'];
		
		print '<img src="http://ventrilo.com/venticon_server.png" align="absmiddle" /> '.$server['name'].' ('.$server['address'].')<br />';
		
		if($this->channels != null)
		{
			foreach($this->channels as $channel)
			{
				$this->_display_channel($channel);
			}
		}
		else
		{
			if(fsockopen('udp://'.$server['address']))
				print '&nbsp;&nbsp;&nbsp;&nbsp;Online';
			else
				print '&nbsp;&nbsp;&nbsp;&nbsp;Offline';
		}
	}
	
	private function _display_channel($channel)
	{
		static $nested = 0;
		$nested++;
		
		if(!$this->displayed[$channel['name']])
		{
			print str_repeat('&nbsp;', $nested * 5).'<img src="http://ventrilo.com/venticon_chanopen.png" align="absmiddle" /> '.$channel['name'].'<br />';
			
			foreach($this->players as $player)
			{
				if($player['cid'] == $channel['cid'])
					print str_repeat('&nbsp;', ($nested + 1) * 5).'<img src="http://ventrilo.com/venticon_voiceoff.png" align="absmiddle" />'.(($player['admin']) ? '&quot;A&quot; ' : null).$player['name'].'<br />';
			}
		}
		
		$this->displayed[$channel['name']] = true;
		
		foreach($this->channels as $id => $_channel)
		{
			if($_channel['pid'] == $channel['cid'])
			{
				$this->_display_channel($_channel);
				$nested--;
				return;
			}
		}
		
		$nested--;
	}
}
?>
<?$vent_server_display = new vent_server_display;
for($id = 0; $id < count($vars->server); $id++):?>
	<?=$vent_server_display->display_server($vars->server[$id]);?>
<?endfor?>