<?php
class mail
{
	private
		//connection resource
		$connection,
		//servers
		$servers = array(),
		//email recipient
		$recipient,
		//email sender
		$sender,
		//sender authentication username
		$username,
		//sender authentication password
		$password,
		//server cmd log
		$log = array();
	
	public function __construct($sender, $server = null, $username = null, $password = null)
	{
		//extract sender & domain from sender's email adress
		list($this->sender['name'], $this->sender['domain']) = explode('@', $sender);
		
		$this->username = $username;
		$this->password = $password;
		
		if($server != null)
			//defined servers
			$this->servers = (array) $server;
		else
		{
			//find servers for the sender's domain
			getmxrr($this->sender['domain'], &$this->servers, &$server_weight);
			
			//sort servers by server weight
			if(is_array($this->servers))
				array_multisort($this->servers, $server_weight);
		}
		
	}
	public function send($recipient, $subject, $message)
	{
		//no servers :(
		if(!is_array($this->servers) || $this->servers = array('localhost'))
			return $this->send_default($recipient, $subject, $message);
		
		//extract recipient & domain from recipient's email adress
		list($this->recipient['name'], $this->recipient['domain']) = explode('@', $recipient);
		
		//iterate through each server; break on successful email
		foreach($this->servers as $domain)
		{
			//open a connection to the server
			$this->connection = fsockopen($domain, 25);
			
			//check the connection was successful
			if(!$this->connection || !$this->_response(220))
				continue;
			
			//say hello :)
			$this->_send('EHLO '.$domain);
			if(!$this->_response(220))
				continue;
			
			//authenticate with the server if a username and password was provided
			if(!in_array(null, array($this->username, $this->password)))
			{
				//init auth
				$this->_send('AUTH LOGIN');
				if(!$this->_response(220))
					continue;
				
				//send encoded username
				$this->_send(base64_encode($this->username));
				if(!$this->_response(250))
					continue;
				
				//send encoded password
				$this->_send(base64_encode($this->password));
				if(!$this->_response(250))
					continue;
			}
			
			//tell the server who's sending the email...
			$this->_send('MAIL FROM: <'.$this->sender['name'].'@'.$this->sender['domain'].'>');
			if(!$this->_response(array(220, 250)))
				continue;
			
			//tell the server who the email is to
			$this->_send('RCPT TO: <'.$this->recipient['name'].'@'.$this->recipient['domain'].'>');
			if(!$this->_response(250))
				continue;
			
			//tell the server data is coming...
			$this->_send('DATA');
			if(!$this->_response(250))
				continue;
			
			//send the email!
			$this->_send('To: '.$this->recipient['name'].'@'.$this->recipient['domain']);
			$this->_send('From: '.$this->sender['name'].'@'.$this->sender['domain']);
			$this->_send('Subject: '.$subject, true);
			$this->_send(/*'Message: '.*/$message);
			//send delimiting period
			$this->_send('.');
			
			//finished here...
			$this->_send('QUIT');
			
			//success! the email sent...
			return true;
		}
		
		//failure :(
		return $this->send_default($recipient, $subject, $message);
		
		return false;
	}
	protected function send_default($recipient, $subject, $message)
	{
		//just try PHP's native mail() function
		mail($recipient, $subject, $message, 'From:'.$this->sender['name'].'@'.$this->sender['domain']);
	}
	private function _send($message, $double_crlf = false)
	{
		//log this transaction
		$this->log[] = $message;
		
		//send data to the server
		fputs($this->connection, $message."\n".(($double_crlf) ? "\n" : null));
		
		return true;
	}
	private function _response($response_code)
	{
		//retrieve data from the server
		$response = fgets($this->connection);
		
		//log this transaction
		$this->log[] = $response;
		
		//accept multiple response codes (for different servers/software...)
		$response_code = (array) $response_code;
		
		//expected response code
		if(in_array(substr($response, 0, 3), $response_code))
			return true;
		//unexpected response code
		else
			return false;
	}
}
?>