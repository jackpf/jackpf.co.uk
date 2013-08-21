<?php
include_once $_SERVER['DOCUMENT_ROOT'].'/config/lib.php';
include '../resources/Steam.class.php';

class steam_friends extends Steam
{
	private
		$steamid,
		$profile;
	
	public function check_friends($steamID)
	{
		global $config_init;
		
		$config_init->load($_SERVER['DOCUMENT_ROOT'].'/bin/server/steam/friends.cfg');
		$data = $config_init->parse()->get_array();
		
		$this->steamid = $steamID;
		
		if($this->steamid == null)
			return;
		
		$this->profile = $this->GetProfile($this->steamid, '/friends?xml=1');
		
		#$profile = $this->ProfileXMLElement($this->profile);
		
		$matches = array();
		preg_match_all('/\<friend\>([0-9]+)\<\/friend\>/', $this->profile, &$matches);
		
		$data['steam_friends'][time()] = $matches[1];
		
		$config_init->write($data);
		
		foreach($data['steam_friends'] as $time => $friends)
		{
			if(!isset($previous_friends))
				$previous_friends = $friends;
			else
			{
				foreach($previous_friends as $friend)
				{
					if(!in_array($friend, $friends))
					{
						$this->profile = $this->GetProfile($friend, '?xml=1');
						
						$matches = array();
						preg_match('/\<steamID\>\<\!\[CDATA\[(.*?)\]\]\>\<\/steamID\>/', $this->profile, &$matches);
						$name = $matches[1];
						
						echo '<a href="http://'.steam::STEAMURL.'/profiles/'.$friend.'">'.$name.'</a> was removed on '.val::unix($time).'<br />';
					}
				}
				/*foreach($friends as $friend)
				{
					//...
				}*/
			}
			$previous_friends = $friends;
		}
	}
}

secure::secure(alias::user('administrator'));

$steam_friends = new steam_friends;
$steam_friends->check_friends($_GET['id']);
?>