<?php
class RCon
{
	private
		$Password,
		$Host,
		$Port,
		$_Sock,
		$_Id = 0;
	
	const
		SERVERDATA_EXECCOMMAND	= 2,
		SERVERDATA_AUTH			= 3;

	public function __construct($host, $password)
	{
		$this->Password	= $password;
		list(
		$this->Host,
		$this->Port
		)				= explode(':', $host);
		$this->_Sock	= @fsockopen($this->Host, $this->Port, $errno, $errstr, 30);
		$this->_Set_Timeout($this->_Sock, 2, 500);
		
		if(!$this->_Sock)
		{
			throw new Exception("Connection error.");
		}

		$this->Auth();
	}
	
	private function Auth()
	{
		$PackID = $this->_Write(self::SERVERDATA_AUTH, $this->Password);
		
		// Real response (id: -1 = failure)
		$ret = $this->PacketRead();
		if($ret[1]['id'] == -1)
			throw new Exception("Authentication failure.");
	}

	private function _Set_Timeout(&$res, $s, $m = 0)
	{
		return stream_set_timeout($res, $s, $m);
	}

	private function _Write($cmd, $s1 = '', $s2 = '')
	{
		// Get and increment the packet id
		$id = ++$this->_Id;

		// Put our packet together
		$data = pack("VV", $id, $cmd).$s1.chr(0).$s2.chr(0);

		// Prefix the packet size
		$data = pack("V", strlen($data)).$data;

		// Send packet
		fwrite($this->_Sock, $data, strlen($data));

		// In case we want it later we'll return the packet id
		return $id;
	}

	private function PacketRead()
	{
		//Declare the return array
		$retarray = array();
		
		// Fetch the packet size
		while($size = @fread($this->_Sock, 4))
		{
			$size = unpack("V1Size", $size);
			// Work around valve breaking the protocol
			if($size["Size"] > 4096)
				// Pad with 8 nulls
				$packet = "\x00\x00\x00\x00\x00\x00\x00\x00".fread($this->_Sock, 4096);
			else
				// Read the packet back
				$packet = fread($this->_Sock, $size["Size"]);
			
			array_push($retarray, unpack("V1ID/V1Response/a*S1/a*S2", $packet));
		}
		
		return $retarray;
	}

	private function Read()
	{
		$Packets = $this->PacketRead();
		
		foreach($Packets as $pack)
		{
			if(isset($ret[$pack['ID']]))
			{
				$ret[$pack['ID']]['S1'] .= $pack['S1'];
				$ret[$pack['ID']]['S2'] .= $pack['S1'];
			}
			else
			{
				$ret[$pack['ID']] = array(
				'Response'	=> $pack['Response'],
				'S1'		=> $pack['S1'],
				'S2'		=> $pack['S2'],
				);
			}
		}
		
		return $ret;
	}

	private function sendCommand($Command)
	{
		#$Command = "\"".trim(str_replace(" ", "\" \"", $Command))."\"";
		$this->_Write(self::SERVERDATA_EXECCOMMAND, $Command, "");
	}

	public function RConCommand($Command)
	{
		$this->sendcommand($Command);

		$ret = $this->Read();

		return $ret[$this->_Id]['S1'];
	}
}
?>