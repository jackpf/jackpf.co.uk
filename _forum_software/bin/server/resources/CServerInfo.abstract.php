<?php
abstract class CServerInfo
{
	private
		$raw,
		$address,
		$port,
		$socket = false;

	/** Constants of the data we need for a query */
	const
		QUERY_HEADER							= "\xFF\xFF\xFF\xFF",
		A2A_PING								= "\x69",
		A2A_PING_RESPONSE						= "\x6A",
		A2S_SERVERQUERY_GETCHALLENGE			= "\x57",
		A2S_SERVERQUERY_GETCHALLENGE_RESPONSE	= "\x41",
		A2S_INFO								= "\x54Source Engine Query\0",
		A2S_INFO_RESPONSE						= "\x49",
		A2S_INFO_RESPONSE_OLD					= "\x6D",
		A2S_PLAYER								= "\x55",
		A2S_PLAYER_RESPONSE						= "\x44",
		A2S_RULES								= "\x56",
		A2S_RULES_RESPONSE						= "\x45";
	
	public final function __construct($server_address)
	{
		list
		(
		$this->address,
		$this->port
		) = explode(':', $server_address);
		
		$this->challenge = self::QUERY_HEADER;
	}
	
	public final function getInfo()
	{
		$socket = $this->_getSocket();
		$packet = $this->_request($socket, self::A2S_INFO);
		
		if(!empty($packet))
		{
			$this->raw	= $packet;
			$ret		= array();
			$type		= $this->_getraw(1);
			
			if($type == self::A2S_INFO_RESPONSE)
			{
				// New protocol for Source and Goldsrc
				$this->_getbyte(); // Version
				$ret['hostname']	= $this->_getnullstr();	
				$ret['map']			= $this->_getnullstr();
				$ret['gamename']	= $this->_getnullstr();
				$ret['gamedesc']	= $this->_getnullstr();
				$this->_getushort(); // AppId
				$ret['numplayers']	= $this->_getbyte();
				$ret['maxplayers']	= $this->_getbyte();
				$ret['botcount']	= $this->_getbyte();
				$ret['dedicated']	= $this->_getraw(1);
				$ret['os']			= $this->_getraw(1);
				$ret['password']	= $this->_getbyte();
				$ret['secure']		= $this->_getbyte();
			}
			else if($type == self::A2S_INFO_RESPONSE_OLD)
			{
				// Legacy Goldsrc support
				$this->_getnullstr(); // GameIP
				$ret['hostname']	= $this->_getnullstr();
				$ret['map']			= $this->_getnullstr();
				$ret['gamename']	= $this->_getnullstr();
				$ret['gamedesc']	= $this->_getnullstr();
				$ret['numplayers']	= $this->_getbyte();
				$ret['maxplayers']	= $this->_getbyte();
				$this->_getbyte(); // Version
				$ret['dedicated']	= $this->_getraw(1);
				$ret['os']			= $this->_getraw(1);
				$ret['password']	= $this->_getbyte();
				if($this->_getbyte()) // IsMod
				{
					$this->_getnullstr();
					$this->_getnullstr();
					$this->_getbyte();
					$this->_getlong();
					$this->_getlong();
					$this->_getbyte();
					$this->_getbyte();
				}
				$ret['secure']		= $this->_getbyte();
				$ret['botcount']	= $this->_getbyte();
			}
			
			return $ret;
		}
		else
			return array();
	}
	
	public final function getPlayers()
	{
		$socket	= $this->_getSocket();
		$packet	= $this->_requestWithChallenge($socket, self::A2S_PLAYER, self::A2S_PLAYER_RESPONSE);
		
		if(!empty($packet))
		{
			$this->raw	= $packet;
			$count		= $this->_getbyte();	 
			$players	= array();
			for($i = 0; $i < $count; $i++)
			{
				$temp = array('index' => $this->_getbyte(),
											'name'	=> $this->_getnullstr(),
											'kills' => $this->_getlong(),
											#'time'	=> SecondsToString((int)$this->_getfloat(), true));
											'time'	=> round($this->_getfloat()).'s');
				
				if(!empty($temp['name']))
					$players[] = $temp;
			}
			
			#array_qsort($players, 'kills', SORT_DESC);
			
			return $players;
		}
		else
			return array();
	}
	
	public final function getRules()
	{
		$socket	= $this->_getSocket();
		$packet	= $this->_requestWithChallenge($socket, self::A2S_RULES, self::A2S_RULES_RESPONSE);
		
		if(!empty($packet))
		{
			$this->raw	= $packet;
			$nump		= $this->_getushort(); 
			$ret		= array();
		
			for($i = 0; $i < $nump; $i++)
			{
				$name	= $this->_getnullstr();
				$value	= $this->_getnullstr();
				
				if(!empty($name))
					$ret[$name] = $value;
			}
			
			ksort($ret);
			
			return $ret;
		}
		else
			return array();
	}
	
	private final function _getSocket()
	{
		if($this->socket !== false)
			return $this->socket;
		
		$this->socket = fsockopen('udp://' . $this->address, $this->port);
		
		if($this->socket === false)
			return false;
			
		stream_set_timeout($this->socket, 1);
		
		return $this->socket;
	}
	
	private final function _request($socket, $code, $reply = null)
	{
		fwrite($socket, self::QUERY_HEADER . $code);
		$packet = $this->_readsplit($socket);
		
		if(!empty($packet))
		{
			$this->raw	= $packet;
			$magic		= $this->_getlong();
			
			if($magic != -1)
				return null;
			
			$response = $this->_getraw(1);
			
			if($reply == null)
				return substr($packet, 4); // Skip magic as it was checked
			else if($response == $reply)
				return substr($packet, 5); // Skip magic and type as it was checked
			
			return null;
		}
		else
			return null;
	}
	
	private final function _requestWithChallenge($socket, $code, $reply = null, $maxretries = 5)
	{
		while(--$maxretries >= 0)
		{
			fwrite($socket, self::QUERY_HEADER . $code . $this->challenge); // do the request with challenge id = -1
			$packet = $this->_readsplit($socket);
			
			if(!empty($packet))
			{
				$this->raw = $packet;
				$magic = $this->_getlong();
				
				if($magic != -1)
					return null;
				
				$response = $this->_getraw(1);
				
				if($response == self::A2S_SERVERQUERY_GETCHALLENGE_RESPONSE)
					$this->challenge = $this->_getraw(4);
				else if($reply == null)
					return substr($packet, 4); // Skip magic as it was checked
				else if($response == $reply)
					return substr($packet, 5); // Skip magic and type as it was checked
			}
			else
				return null;
		}
		
		return null;
	}
	
	private final function _readsplit($socket)
	{
		$packet = fread($socket, 1480);
		
		if(!empty($packet))
		{
			$this->raw	= $packet;
			$type		= $this->_getlong();
			
			if($type == -2)
			{
				// Parse first header
				$reqid		= $this->_getlong();
				$packets	= $this->_getushort();
				$numpackets	= $packets & 0xFF;
				$curpacket	= $packets >> 8;
				if($reqid >= 0) // Dummy value telling how big the split is (hardcoded to 1248), Orangebox or later
					$this->_skip(2);
				$data		= array();
				$tstart		= microtime(true);
				
				// Sanity check
				if($curpacket >= $numpackets)
					return null;
				
				// Compressed?
				if($curpacket == 0 && $reqid < 0)
				{
					$sizeuncompressed	= $this->_getlong();
					$crc				= $this->_getlong();
				}
				
				while(true)
				{
					// Split already received (duplicate)?
					if(!array_key_exists($curpacket, $data))
						$data[$curpacket] = $this->raw;

					// Finished?
					if(count($data) >= $numpackets)
					{
						// Join the parts
						ksort($data);
						$data = implode($data);
						
						// Uncompress if necessary
						if($reqid < 0)
						{
							$data = bzdecompress($data);
							if(strlen($data) != $sizeuncompressed)
								return null;
							
							// TODO: CRC32 check
							return $data;
						}
						
						// Not compressed
						return $data;
					}
					
					// Check the timeout over several receives
					if(microtime(true) - $tstart >= 2.0) // 2s
						return null;
					
					// Receive next packet
					$packet = fread($socket, 1480);
					
					if(!empty($packet))
					{
						// Parse packet
						$this->raw	= $packet;
						$_type		= $this->_getlong();
						
						if($_type != -2)
							return null;
						
						$_reqid			= $this->_getlong();
						$_packets		= $this->_getushort();
						$_numpackets	= $_packets & 0xFF;
						$curpacket		= $_packets >> 8;
						if($reqid >= 0)	// Dummy value telling how big the split is (hardcoded to 1248), Orangebox or later
							$this->_skip(2);
						
						// Sanity check
						if($_reqid != $reqid || $_numpackets != $numpackets || $curpacket >= $numpackets)
							return null;
						
						// Compressed?
						if($curpacket == 0 && $reqid < 0)
						{
							$sizeuncompressed	= $this->_getlong();
							$crc				= $this->_getlong();
						}
					}
				}	
			}
			else if($type == -1)
				// Non-split packet
				return $packet;
			else
				// Invalid
				return null;
		}
		else
			return null;
	}
	
	private final function _getraw($count)
	{
		$data		= substr($this->raw, 0, $count);
		$this->raw	= substr($this->raw, $count);
		
		return $data;
	}
	
	private final function _getbyte() 
	{
		$byte = $this->_getraw(1);
		
		return ord($byte);
	}
	
	private final function _getfloat() 
	{
		$f = unpack('f1float', $this->_getraw(4));
		
		return $f['float'];
	}
	
	private final function _getlong() 
	{
		$lo		= $this->_getushort();
		$hi		= $this->_getushort();
		$long	= ($hi << 16) | $lo;
		
		if ($long & 0x80000000 && $long > 0) // This is special for register size >32 bits
			return -((~$long & 0xFFFFFFFF) + 1);
		else
			return $long; // 32-bit handles negative values implicitly
	}
	
	private final function _getnullstr() 
	{
		if(!empty($this->raw))
		{
			$end		= strpos($this->raw, "\0");
			$str		= substr($this->raw, 0, $end);
			$this->raw	= substr($this->raw, $end + 1);
			
			return $str;
		}
		else
			return null;
	}
	
	private final function _getushort()
	{
		$lo		= $this->_getbyte();
		$hi		= $this->_getbyte();
		$short	= ($hi << 8) | $lo;
		
		return $short;
	}
	
	private final function _getshort() 
	{
		$short = $this->_getushort();
		
		if($short & 0x8000)
			return -((~$short & 0xFFFF) + 1);
		else
			return $short;
	}
	
	private final function _skip($c)
	{
		$this->raw = substr($this->raw, $c);
	}
}
?>