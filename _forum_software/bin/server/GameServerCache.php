<?php
include_once $_SERVER['DOCUMENT_ROOT'].'/config/lib.php';
#include <resources/steam.cpp>
include 'resources/Steam.class.php';

class gameserver_cache extends Steam
{
	const
		INFO_CACHE_FILE		= 'bin/server/cache/info.cache',
		BAN_CFG				= 'cfg/banned_user.cfg',
		ADMIN_CFG			= 'addons/sourcemod/configs/admins_simple.ini',
		STEAM_PROFILE_URL	= 'http://steamcommunity.com/profiles';
	
	private
		$address,
		$port,
		$username,
		$password,
		$cache = array('admins' => array(), 'bans' => array()),
		$gamedirs = array('l4d/left4dead'/*, 'left4dead2/left4dead2'*/);
	
	public function __construct($server_address, $server_username, $server_password)
	{
		list(
		$this->address,
		$this->port
		) = explode(':', $server_address);
		$this->username = $server_username;
		$this->password = $server_password;
	}
	public function get_cache()
	{
		global $config_init;
		
		//connect
		$ftp = ftp_connect($this->address);
		ftp_login($ftp, $this->username, $this->password);
		
		$fh = fopen($_SERVER['DOCUMENT_ROOT'].'/'.self::INFO_CACHE_FILE, 'w+');
		
		foreach($this->gamedirs as $gamedir)
		{
			//admins
			ftp_fget($ftp, $fh, '/srcds_l/'.$gamedir.'/'.self::ADMIN_CFG, FTP_ASCII);
			
			foreach(file($_SERVER['DOCUMENT_ROOT'].'/'.self::INFO_CACHE_FILE) as $index => $line)
			{
				if(substr($line, 0, 2) != '//' && trim($line) != null)
				{
					preg_match('/\"(?<steamid>.*?)\"\s\"(?<flags>.*?)\"/', $line, &$admin);
					
					$this->cache['admins'][$index]['steamid']	= $admin['steamid'];
					$this->cache['admins'][$index]['flags']		= $admin['flags'];
					
					#$this->cache['admins'][$index]['friendid'] = trim(shell_exec('resources/steam.exe '.escapeshellcmd($this->cache['admins'][$index]['steamid'])));
					$this->cache['admins'][$index]['friendid'] = $this->GetFriendID($this->cache['admins'][$index]['steamid']);
					
					$ch = curl_init();
					curl_setopt($ch, CURLOPT_URL, self::STEAM_PROFILE_URL.'/'.$this->cache['admins'][$index]['friendid'].'?xml=1');
					curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
					$profile = curl_exec($ch);
					curl_close($ch);
					
					preg_match('/\<steamID\>\<\!\[CDATA\[(.*?)\]\]\>\<\/steamID\>/', $profile, &$steamid);
					$this->cache['admins'][$index]['nick'] = $steamid[1];
				}
			}
			
			//bans
			ftp_fget($ftp, $fh, '/srcds_l/'.$gamedir.'/'.self::BAN_CFG, FTP_ASCII);
			
			foreach(file($_SERVER['DOCUMENT_ROOT'].'/'.self::INFO_CACHE_FILE) as $index => $line)
			{
				if(substr($line, 0, 2) != '//' && trim($line) != null)
				{
					list(
					, ,
					$this->cache['bans'][$index]['steamid']
					) = array_map('trim', explode(' ', $line));
					
					#$this->cache['bans'][$index]['friendid'] = trim(shell_exec('resources/steam.exe '.escapeshellcmd($this->cache['bans'][$index]['steamid'])));
					$this->cache['bans'][$index]['friendid'] = $this->GetFriendID($this->cache['bans'][$index]['steamid']);
					
					$ch = curl_init();
					curl_setopt($ch, CURLOPT_URL, self::STEAM_PROFILE_URL.'/'.$this->cache['bans'][$index]['friendid'].'?xml=1');
					curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
					$profile = curl_exec($ch);
					curl_close($ch);
					
					preg_match('/\<steamID\>\<\!\[CDATA\[(.*?)\]\]\>\<\/steamID\>/', $profile, &$steamid);
					$this->cache['bans'][$index]['nick'] = $steamid[1];
				}
			}
		}
		
		//write
		fclose($fh);
		$config_init->load($_SERVER['DOCUMENT_ROOT'].'/'.self::INFO_CACHE_FILE);
		$config_init->write($this->cache);
	}
}

secure::secure();
secure::restrict(alias::user(alias::USR_ADMINISTRATOR));

$gsc = new gameserver_cache($config_init->get_config('gameserver.address'),
							$config_init->get_config('gameserver.auth.username'),
							$config_init->get_config('gameserver.auth.password'));
$gsc->get_cache();

$config_init->load($_SERVER['DOCUMENT_ROOT'].'/'.gameserver_cache::INFO_CACHE_FILE);

print('Done.');
?>