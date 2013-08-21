<?php
class captcha
{
	private
		//img resource
		$img,
		//img dimensions
		$dimensions = array('width' => 250, 'height' => 100),
		//string length
		$length = 5,
		//string
		$string = array();
	
	const
		//this file
		CAPTCHA_FILENAME = './bin/captcha.class.php';
	
	//create img
	public function create()
	{
		global $config_init;
		
		//create img
		$this->img = imagecreatetruecolor($this->dimensions['width'], $this->dimensions['height']);
		
		//create a transparent background
		imagecolortransparent($this->img, imagecolorallocate($this->img, 0, 0, 0));
		
		//fill background
		#$background = array(rand(0, 2) => rand(1, 255));
		#
		#for($i = 0; $i <= 255; $i++)
		#{
		#	for($ii = 0; $ii < 3; $ii++)
		#	{
		#		if($ii != reset(array_keys($background)))
		#		{
		#			$background[$ii] = floor($i * 255 / $this->dimensions['height']);
		#		}
		#	}
		#	
		#	imageline($this->img, 0, $i, $this->dimensions['width'], $i, imagecolorallocate($this->img, $background[0], $background[1], $background[2]));
		#}
		
		//populate img
		$this->create_string();
		
		//set validation cookie
		header::setcookie($config_init->get_config('cookie_prefix').'captcha', sha1(implode($this->string)), 0);
	}
	//create string
	private function create_string()
	{
		//create string
		for($i = 97, $ii = 1; $i < $this->length + 97; $i++, $ii++)
		{
			$char = chr($i + rand(0, 25 - ($i - 97)));
			$this->string[] = $char;
			
			imagettftext($this->img,
						25,
						rand(-30, 30),
						$ii * ($this->dimensions['width'] - 60) / $this->length,
						($this->dimensions['height'] / 2) - rand(0, 5),
						imagecolorallocate($this->img, rand(0, 255), rand(0, 255), rand(0, 255)),
						'arial.ttf',
						$char);
		}
		
		//create noise
		for($i = 0; $i < 10; $i++)
		{
			switch(rand(1, 10))
			{
				case 1: case 2: case 3: case 4: case 5: case 6:
					imagestring($this->img, 1, rand(1, imagesx($this->img)), rand(1, imagesy($this->img)), '.', imagecolorallocate($this->img, rand(1, 255), rand(1, 255), 0));
				break;
				case 7: case 8:
					imageline($this->img, rand(1, $this->dimensions['width']), rand(1, $this->dimensions['height']), rand(1, $this->dimensions['width']), rand(1, $this->dimensions['height']), imagecolorallocate($this->img, rand(1, 255), rand(1, 255), 0));
				break;
				case 9:
					imageellipse($this->img, rand(0, $this->dimensions['width']), rand(0, $this->dimensions['height']), rand(0, $this->dimensions['width'] / 2), rand(0, $this->dimensions['height'] / 2), imagecolorallocate($this->img, rand(1, 255), rand(1, 255), 0));
				break;
				case 10:
					imagearc($this->img, rand(0, $this->dimensions['width']), rand(0, $this->dimensions['height']), rand(0, $this->dimensions['width'] / 2), rand(0, $this->dimensions['height'] / 2), rand(1, 360), rand(1, 360), imagecolorallocate($this->img, rand(1, 255), rand(1, 255), 0));
				break;
			}
		}
	}
	//send img
	public function send($state = 1)
	{
		if($state === 0)
		{
			//send img
			header('Content-type: image/png');
			imagepng($this->img);
			imagedestroy($this->img);
			
			return null;
		}
		else if($state === 1)
		{
			//link to img
			return '<img style="width: '.$this->dimensions['width'].'px; height: '.$this->dimensions['height'].'px;" src="'.self::CAPTCHA_FILENAME.'?captcha=1&amp;i='.rand().'" alt="Captcha" />';
		}
	}
	//validate captcha
	public function validate($captcha)
	{
		global $config_init;
		
		//check the hash of the verification against the hashed captcha cookie
		return (isset($captcha)
				&& isset($_COOKIE[$config_init->get_config('cookie_prefix').'captcha'])
				&& sha1($captcha) === $_COOKIE[$config_init->get_config('cookie_prefix').'captcha']) ?
		true : false;
	}
}

//create img: include config & send the image
if(isset($_GET['captcha']) && $_GET['captcha'] == 1)
{
	include_once '../config/lib.php';
	
	$captcha = new captcha;
	$captcha->create();
	
	echo $captcha->send((!isset($_GET['state'])) ? 0 : 1);
}
?>