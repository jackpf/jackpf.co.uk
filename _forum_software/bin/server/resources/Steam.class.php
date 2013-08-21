<?php
class Steam
{
	private
		$iServer, 
		$iAuthID,
		$iSteamID;
	
	protected
		$isAuth = false;
	
	const
		STEAMURL = 'steamcommunity.com',
		COOKIE_STRING = 'steamcommunity.com	FALSE	/	FALSE	0	steamLogin	%s';
	
	public function GetFriendID($pszAuthID)
	{
		list(
		,
		$this->iServer,
		$this->iAuthID
		) = explode(':', $pszAuthID);

		$i64friendID = bcadd(bcmul($this->iAuthID, '2'), bcadd('76561197960265728', $this->iServer));

		return $i64friendID;
	}
	public function GetSteamID($friendID)
	{
		$this->iSteamID = bcdiv(bcsub($friendID, bcadd('76561197960265728', ($friendID & 1 == 0) ? '0' : '1')), '2');

		return 'STEAM_0:'.(($friendID & 1 == 0) ? '0' : '1').':'.$this->iSteamID;
	}
	
	public function Authenticate($username, $password)
	{
		if($username != null && $password != null)
		{
			global $config_init;
			session_start();
			
			if(!isset($_SESSION[$config_init->get_config('cookie_prefix').'Steam']) || $_SESSION[$config_init->get_config('cookie_prefix').'Steam(Username)'] != $username)
			{
				$ch = curl_init();
				curl_setopt($ch, CURLOPT_URL, 'https://'.self::STEAMURL);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
				curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
				curl_setopt($ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
				curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
				curl_setopt($ch, CURLOPT_POST, true);
				curl_setopt($ch, CURLOPT_POSTFIELDS, array(
				'action' => 'doLogin',
				'goto' => null,
				'qs' => null,
				'msg' => null,
				'steamAccountName' => $username,
				'steamPassword' => $password,
				'x' => 0,
				'y' => 0 
				));
				curl_setopt($ch, CURLOPT_HEADER, 1);
				#curl_setopt($ch, CURLOPT_NOBODY, 1);
				$profile = curl_exec($ch);
				curl_close($ch);
				
				if(preg_match('/steamLogin\=(.*?)\;/', $profile, &$cookie))
				{
					$_SESSION[$config_init->get_config('cookie_prefix').'Steam(Username)'] = $username;
					$_SESSION[$config_init->get_config('cookie_prefix').'Steam'] = $cookie[1];
				}
			}
			
			if($_SESSION[$config_init->get_config('cookie_prefix').'Steam'] != null && $_SESSION[$config_init->get_config('cookie_prefix').'Steam(Username)'] == $username)
			{
				$cookie_file = tempnam('/tmp', 'steam');
				$cookie_file_fh = fopen($cookie_file, 'r+');
				fwrite($cookie_file_fh, sprintf(self::COOKIE_STRING, $_SESSION[$config_init->get_config('cookie_prefix').'Steam']));
				fseek($cookie_file_fh, 0);
				#fclose($cookie_file_fh);
				
				$this->isAuth = true;
				
				return $cookie_file;
			}
		}
		
		#else
		$this->isAuth = false;
		return $this->isAuth;
	}
	public function GetProfile($steamID, $extension = null, $authFile = null)
	{
		if($steamID == null)
			$steamID = '_';
		
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, self::STEAMURL.'/'.((!is_numeric($steamID)) ? 'id' : 'profiles').'/'.$steamID.$extension);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		if($authFile != null)
			curl_setopt($ch, CURLOPT_COOKIEFILE, $authFile);
		$profile = curl_exec($ch);
		curl_close($ch);
		
		return $profile;
	}
	public function &ProfileXMLElement($profile)
	{
		try
		{
			return new SimpleXMLElement($profile);
		}
		catch(Exception $e)
		{
			return (object) array('error' => 'XML parsing error: '.$e->GetMessage().'.');
		}
	}
	public static function server_unix($unix)
	{
		//alias of profile::profile_unix()
		$time = array('hours' => 0, 'minutes' => 0, 'seconds' => 0);
		
		$time['hours'] = floor($unix / (60 * 60));
		$time['minutes'] = floor($unix % 60);
		$time['seconds'] = $unix % 60 % 60;
		
		if($time['hours'] > 0)
			$time['hours'] .= 'h';
		else
			unset($time['hours']);
		if($time['minutes'] > 0)
			$time['minutes'] .= 'm';
		else
			unset($time['minutes']);
		if($time['seconds'] > 0)
			$time['seconds'] .= 's';
		else
			unset($time['seconds']);
		
		return implode(', ', $time);
	}
}
?>