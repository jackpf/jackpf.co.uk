<?php
include 'resources/CServerInfo.abstract.php';

class hl_server extends CServerInfo
{
	private
		$status = array(),
		$_cache;
	
	public
		$caching = false;
	
	const
		GAMESERVER_CACHE_FILE = 'bin/server/cache/gameserver.cache';
	
	public function query_server()
	{
		$this->_obtain_cache();
		
		if($this->_cache_expired())
		{
			$this->status				= $this->getInfo();
			$this->status['players']	= $this->getPlayers();
			$this->status['cvars']		= $this->getRules();
			
			//l4d hack
			$this->status['_gamename'] = $this->status['gamename'];
			switch($this->status['gamename'])
			{
				case 'left4dead':
					$this->status['_gamename'] = 'l4d';
					$this->status['gamename'] = 'l4d';
					$this->status['gamedesc'] = 'Left 4 Dead';
				break;
				case 'left4dead2':
					//_gamename = left4dead2
					$this->status['gamename'] = 'l4d2';
				break;
				case 'cstrike':
					$this->status['_gamename'] = $this->status['gamename'] = 'css';
				break;
			}
			
			return $this->_write_cache(__FUNCTION__, $this->status);
		}
		else
			return $this->_get_cache(__FUNCTION__) + array('_cached' => time() - $this->_cache['gameserver']['cache']);
	}
	
	private function _obtain_cache()
	{
		if(empty($this->_cache))
		{
			global $config_init;
			$config_init->load($_SERVER['DOCUMENT_ROOT'].'/'.self::GAMESERVER_CACHE_FILE);
			$this->_cache = $config_init->parse()->get_array();
		}
	}
	private function _cache_expired()
	{
		if($this->caching)
			return ($this->_cache['gameserver']['expire'] < time());
		else
			return true;
	}
	private function _write_cache($key, $value)
	{
		global $config_init;
		
		$this->_cache['gameserver']['cache'] = time();
		$this->_cache['gameserver']['expire'] = $this->_cache['gameserver']['cache'] + round(stat::TIMEOUT / 10);
		$this->_cache['gameserver'][$key] = serialize($value);
		
		$config_init->write($this->_cache);
		
		return $value;
	}
	private function _get_cache($key)
	{
		return unserialize($this->_cache['gameserver'][$key]);
	}
	/*
	//query constants
	const
		A2S_PING						= "\x69",
		A2S_SERVERQUERY_GETCHALLENGE	= "\x57",
		A2S_INFO						= "TSource Engine Query\x00",
		A2S_PLAYER						= "\x55",
		A2S_RULES						= "\x56";
	
	//private properties
	private
		$sv_ip,
		$sv_port,
		$socket,
		$challenge,
		$ping,
		$serverinfo = array(),
		$playerlist = array(),
		$cvarlist;
	
	//construct
	public function __construct($server_address = null)
	{
		if($server_address != null)
			$this->connect($server_address);
	}
	
	//connect/disconnect
	public function connect($server_address)
	{
		list(
		$this->sv_ip,
		$this->sv_port
		) = explode(':', $server_address);
		
		$this->socket = fsockopen("udp://".$this->sv_ip, $this->sv_port);
		
		if(!$this->socket)
			throw new Exception('Could not open UDP connection to server ('.$this->sv_ip.':'.$this->sv_port.')');
		
		$this->ping = array_sum(explode(' ', microtime()));
		$this->send(self::A2S_PING);
		$this->get_byte();
		$this->get_string();
		$this->ping = round((array_sum(explode(' ', microtime())) - $this->ping) * 1000);
	}
	public function disconnect()
	{
		fclose($this->socket);
	}
	
	//queries
	private function query_challenge()
	{
		$this->send(self::A2S_SERVERQUERY_GETCHALLENGE);
		
		$this->get_int32();
		$this->get_byte();
		
		return $this->get_4();
	}
	public function query_server()
	{
		$this->send(self::A2S_INFO);
		
		$this->get_int32();
		$this->get_byte();
		$this->serverinfo = array(
		'network_version'	=> $this->get_byte(),
		'name'				=> $this->get_string(),
		'map'				=> $this->get_string(),
		'directory'			=> $this->get_string(),
		'description'		=> $this->get_string(),
		'steam_id'			=> $this->get_int16(),
		'players'			=> $this->get_byte(),
		'maxplayers'		=> $this->get_byte(),
		'bot'				=> $this->get_byte(),
		'dedicated'			=> $this->get_char(),
		'os'				=> $this->get_char(),
		'password'			=> $this->get_byte(),
		'secure'			=> $this->get_byte(),
		'version'			=> $this->get_string(),
		'ping'				=> $this->ping
		);
		
		return $this->serverinfo;
	}
	public function query_players()
	{
		$challenge = $this->query_challenge();
		
		$this->send(self::A2S_PLAYER.$challenge);
		
		$this->get_int32();
		$this->get_byte();
		
		$playercount = $this->get_byte();
		for($i = 1; $i <= $playercount; $i++)
		{
			$this->playerlist['index'][$i]	= $this->get_byte();
			$this->playerlist['name'][$i]	= $this->get_string();
			$this->playerlist['frags'][$i]	= $this->get_int32();
			$this->playerlist['time'][$i]	= date('H:i:s', round($this->get_float32(), 0) + 82800);
		}
		
		return $this->playerlist;
	}
	public function query_cvars()
	{
		$challenge = $this->query_challenge();
		
		$this->send(self::A2S_RULES.$challenge);
		
		$this->get_int32();
		$this->get_byte();
		
		$cvarcount = $this->get_int16();
		for($i = 1; $i <= $cvarcount; $i++)
			$this->cvarlist[$this->get_string()] = $this->get_string();
		
		return $this->cvarlist;   
	}
	
	//write
	public function send($cmd)
	{
		fwrite($this->socket, sprintf('%c%c%c%c%s%c', 0xFF, 0xFF, 0xFF, 0xFF, $cmd, 0x00));
	}
	
	//read
	public function get_byte()
	{
		return ord(fread($this->socket, 1));
	}
	public function get_char()
	{
		return fread($this->socket, 1);
	}
	public function get_int16()
	{
		$unpacked = unpack('sint', fread($this->socket, 2));
		return $unpacked['int'];
	}
	public function get_int32()
	{
		$unpacked = unpack('iint', fread($this->socket, 4));
		return $unpacked['int'];
	}
	public function get_float32()
	{
		$unpacked = unpack('fint', fread($this->socket, 4));
		return $unpacked['int'];
	}
	public function get_string()
	{
		$str = null;
		
		while(($char = $this->get_char()) != chr(0))
		{
			$str .= $char;
		}
		
		return $str;
	}
	public function get_4()
	{
		return fread($this->socket, 4);
	}
	*/
}
?>