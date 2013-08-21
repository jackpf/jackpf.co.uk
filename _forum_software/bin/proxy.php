<?php
include $_SERVER['DOCUMENT_ROOT'].'/config/lib.php';

class proxy
{
	private
		$ch,
		$url,
		$domain;
	
	public function set_url($url)
	{
		$this->url = preg_replace('/http\:\/\//i', null, urldecode($url));
		$this->domain = reset(explode('/', $this->url));
	}
	public function exec()
	{
		$this->ch = curl_init();
		
		if($this->domain == 'steamcommunity.com')
			$this->url = 'https://'.$this->url;
		
		curl_setopt($this->ch, CURLOPT_URL, $this->url);
		curl_setopt($this->ch, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($this->ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
		curl_setopt($this->ch, CURLOPT_SSL_VERIFYPEER, false);
		if(form::submitted())
		{
			curl_setopt($this->ch, CURLOPT_POST, true);
			curl_setopt($this->ch, CURLOPT_POSTFIELDS, $_POST);
		}
		$cookies = 'proxy_cookies.cfg'; #tempnam('/tmp', 'proxy');
		curl_setopt($this->ch, CURLOPT_COOKIEJAR, $cookies);
		curl_setopt($this->ch, CURLOPT_COOKIEFILE, $cookies);
		
		$page = curl_exec($this->ch);
		$pageinfo = (object) curl_getinfo($this->ch);
		
		header('Content-Type: '.$pageinfo->content_type);
		
		#$page = preg_replace('/(href|src|action|value)="(.*?)"/ie', '\'$1="?url=\'.urlencode(html_entity_decode(str_replace(\'http://\', null, ((!strstr(\'$2\', \''.$this->domain.'\')) ? \''.$this->domain.'/\'.((strstr(\'forums.steampowered.com\', \''.$this->domain.'\')) ? \'/forums/\' : null) : null).\'$2\'))).\'"\'', $page);
		$page = preg_replace_callback('/(href|src|action|name="subURL" value)="(.*?)"/i', create_function('$matches', ';$link = $matches[1].\'="?url={0}"\'; $domain = \''.$this->domain.'\'; if(strstr($matches[2], \'javascript:\')){ $domain = $matches[2]; $link = str_replace(array(\'?url=\', \'{0}\'), array(null, $domain), $link); return $link; } else if($matches[2][0] == \'/\') $domain = reset(explode(\'/\', $domain)).\'/\'.$matches[2]; else if(substr($matches[2], 0, 2) == \'./\' || $matches[2][0] == \'?\' || $matches[2][0] == \'#\' || ctype_alnum($matches[2][0])) $domain /*.*/= (ctype_alnum($matches[2][0])) ? /*\'/\'.*/$matches[2] : $matches[2]; else if(strstr($matches[2], \'http://\')) $domain = $matches[2]; else $domain = $matches[2]; $link = str_replace(\'{0}\', urlencode(html_entity_decode(str_replace(array(\'http://\', \'https://\'), null, $domain))), $link); return $link;'), $page); 
		
		return $page;
	}
}

secure::secure();
secure::restrict(alias::user(alias::USR_ADMINISTRATOR));

$proxy = new proxy;
$proxy->set_url($_GET['url']);
echo $proxy->exec();
?>