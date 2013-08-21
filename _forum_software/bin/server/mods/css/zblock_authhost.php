<?php
include $_SERVER['DOCUMENT_ROOT'].'/config/lib.php';

class zblock_auth
{
	private
		$players,
		$uid;
	
	public function zblock_auth($useragent, $uid)
	{
		global $config_init;
		
		#if($useragent !== "zBlock" || !preg_match("/^0:[0-1]:[0-9]{1,10}$/", $uid))
		#	return exit(0);
		
		$this->players = $config_init->load($_SERVER['DOCUMENT_ROOT'].'/bin/server/mods/css/players.cfg')->parse()->get_object()->players;
		$this->uid = $uid;
		
		return $this;
	}
	public function getplayer()
	{
		foreach($this->players as $playername => $steamid)
		{
			if($steamid == $this->uid)
				return array('name' => $playername, 'steamid' => $steamid);
		}
		
		return false;
	}
	public function write_response($player)
	{
		if($player)
			echo base64_encode(chr(0).'Player '.chr(1).$player['name'].' '.chr(2).'['.$player['steamid'].'] '.chr(0).'connected.');
		else
			echo base64_encode(chr(0).'Unknown player '.chr(2).'['.$this->uid.'] '.chr(0).'connected.');
	}
}

$zblock_auth = new zblock_auth($_SERVER['HTTP_USER_AGENT'], $_REQUEST["uid"]);
$zblock_auth->write_response($zblock_auth->getplayer());
?>