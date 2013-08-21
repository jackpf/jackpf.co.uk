<?php
include_once $_SERVER['DOCUMENT_ROOT'].'/config/lib.php';
include '../resources/Steam.class.php';

class img extends Steam
{
	const
		IMG_WIDTH	= 256,
		IMG_HEIGHT	= 64;
	
	private
		$steamid,
		$game,
		$profile,
		$hours,
		$hours_total = 0,
		$hours_week = 0,
		$nick;
	
	public function __construct($steamid, $game)
	{
		global $config_init;
		
		$this->steamid = $steamid;
		$this->profile = $this->GetProfile($this->steamid, '/games?xml=1');
		
		$config_init->load($_SERVER['DOCUMENT_ROOT'].'/bin/server/steam/img.cfg');
		$this->game = $config_init->parse()->get_object()->games[($game != null) ? $game : 'all'];
	}
	public function display()
	{
		$this->get_info();
		
		$this->create_img();
	}
	private function get_info()
	{
		#$profile = $this->ProfileXMLElement($this->profile);
		
		preg_match_all('/\<name\>\<\!\[CDATA\['.$this->game['stats'].'\]\]\>\<\/name\>.*?(\<hoursLast2Weeks\>([0-9\.]+)\<\/hoursLast2Weeks\>\s+)?<hoursOnRecord\>([0-9\.]+)\<\/hoursOnRecord\>/s', $this->profile, &$this->hours);
		
		foreach($this->hours[3] as $hours_total)
			$this->hours_total += round($hours_total);
		foreach($this->hours[2] as $hours_week)
			$this->hours_week += round($hours_week / 2);
		
		preg_match('/\<steamID\>\<\!\[CDATA\[(.*?)\]\]\>\<\/steamID\>/', $this->profile, &$this->nick);
		$this->nick = ($this->steamid != null && $this->nick[1] != null) ? $this->nick[1] : 'No one';
	}
	private function create_img()
	{
		$img = imagecreatetruecolor(self::IMG_WIDTH, self::IMG_HEIGHT);
		imagecolorallocate($img, 0, 0, 0);
		
		$logo = imagecreatefrompng($this->game['img']);
		imagecopy($img, $logo, self::IMG_WIDTH - imagesx($logo), 4, 0, 0, imagesx($logo), imagesy($logo));
		
		imagettftext($img, 16, 0, 4, 18, imagecolorallocate($img, 255, 64, 64), 'resources/fonts/game.ttf', ($this->game['name'] != null) ? val::str_trim($this->game['name'], 10) : 'Unknown game');
		imagettftext($img, 12, 0, strlen(($this->game['name'] != null) ? val::str_trim($this->game['name'], 10) : 'Unknown game') * 12, 18, imagecolorallocate($img, 255, 64, 64), 'resources/fonts/arial.ttf', ' : '.val::str_trim($this->nick, 8));
		
		if(!preg_match('/\<error\>\<\!\[CDATA\[(.*?)\]\]\>\<\/error\>/', $this->profile, &$error))
		{
			imagestring($img, 4, 16, 24, 'Hours this week: ', imagecolorallocate($img, 255, 255, 255));
			imagestring($img, 3, 192, 24 + 2, val::number_format($this->hours_week), imagecolorallocate($img, 112, 147, 219));
			imagestring($img, 4, 16, 38, 'Total hours: ', imagecolorallocate($img, 255, 255, 255));
			imagestring($img, 3, 192, 38 + 2, val::number_format($this->hours_total), imagecolorallocate($img, 112, 147, 219));
		}
		else
		{
			imagestring($img, 4, 16, 24, 'Error:', imagecolorallocate($img, 255, 255, 255));
			imagestring($img, 3, 72, 24 + 2, $error[1], imagecolorallocate($img, 112, 147, 219));
		}
		
		header('Content-Type: image/png');
		imagepng($img);
		imagedestroy($img);
	}
}

$img = new img($_GET['id'], $_GET['game']);
$img->display();
?>