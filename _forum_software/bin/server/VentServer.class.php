<?php
class vent_server
{
	private
		$address,
		$port,
		$status = array(),
		$_cache;
	
	public
		$caching = false;
	
	const
		GAMESERVER_CACHE_FILE = 'bin/server/cache/gameserver.cache';
	
	public function __construct($server_address)
	{
		list(
		$this->address,
		$this->port
		) = explode(':', $server_address);
		
		global $config_init;
		$config_init->load($_SERVER['DOCUMENT_ROOT'].'/'.self::GAMESERVER_CACHE_FILE);
		$this->_cache = $config_init->parse()->get_array();
	}
	public function query_server()
	{
		if($this->_cache_expired())
		{
			foreach(explode(
			"\n",
			shell_exec(dirname(__FILE__).'/resources/ventrilo_status'.((substr(PHP_OS, 0, 3) == 'WIN') ? '.exe' : null).' -c2 -t'.escapeshellcmd($this->address.':'.$this->port))
			) as $line)
			{
				list(
				$key,
				$value
				) = explode(': ', $line);
				
				$key = strtolower($key);
				
				if(array_key_exists($key, $this->status))
					if(!is_array($this->status[$key]))
						$this->status[$key] = array($this->status[$key], $value);
					else
						$this->status[$key][] = $value;
				else
					$this->status[$key] = $value;
			}
			
			return $this->_write_cache(__FUNCTION__, $this->status);
		}
		else
			return $this->_get_cache(__FUNCTION__) + array('_cached' => time() - $this->_cache['ventrilo']['cache']);
	}
	public function query_server2()
	{
		include_once 'gameq/GameQ.php';
		
		if($this->_cache_expired())
		{
			$gq = new GameQ();
			$gq->addServer($this->address, array('ventrilo', $this->address, $this->port));
			$status = $gq->requestData();
			$this->status = $status[$this->address];
			
			return $this->_write_cache(__FUNCTION__, $this->status);
		}
		else
			return $this->_get_cache(__FUNCTION__) + array('_cached' => time() - $this->_cache['ventrilo']['cache']);
	}
	
	private function _cache_expired()
	{
		if($this->caching)
			return ($this->_cache['ventrilo']['expire'] < time());
		else
			return true;
	}
	private function _write_cache($key, $value)
	{
		global $config_init;
		
		$this->_cache['ventrilo']['cache'] = time();
		$this->_cache['ventrilo']['expire'] = $this->_cache['ventrilo']['cache'] + round(stat::TIMEOUT / 10);
		$this->_cache['ventrilo'][$key] = serialize($value);
		
		$config_init->write($this->_cache);
		
		return $value;
	}
	private function _get_cache($key)
	{
		return unserialize($this->_cache['ventrilo'][$key]);
	}
}
?>