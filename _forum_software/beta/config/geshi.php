<?php
final class code extends _code
{
	public $code_vars = array();
	
	public function __call($method, array $args)
	{
		//assign code vars
		switch($method)
		{
			case 'set_code_var':
				$this->code_vars[$args[0]] = $args[1];
			break;
			case 'set_code_vars':
				$this->code_vars += $args[0];
			break;
		}
	}
	public function geshi($code)
	{
		//init code
		list($code, $code_type) = array($code[3], $code[2]);
		
		/*//check parser
		if($code_type == 'php')
		{
			//decode html entities, escape backslashes & trim()
			$code = ($this->code_vars['parse_html']) ? $code : val::decode($code);
			$code = str_replace('\\', '\\\\', trim($this->strip_code_tags($code)));
			
			//highlight!
			$code = highlight_string($code, true);
			
			//remove line breaks highlight_string() appends
			$code = str_replace(array('<br />', "\n"), null, $code);
		}
		else
		{
			//encode
			$code = ($this->code_vars['parse_html']) ? val::encode(str_replace(';', '&#59;', $code)) : val::encode(str_replace(';', '&#59;', val::decode($code)));
			//replace special entities
			$code = str_replace(array(/\*';'*\/'&amp;#59;', ':', '=', '\\&#039;', '\\&quot;'), array(/\*'&#59;'*\/'&#59;', '&#58;', '&#61;', '__SINGLEQUOTE__', '__DOUBLEQUOTE__'), $code);
			
			$syntax = array(
			'/\&\#039\;(.*?)\&\#039\;/s'                  => '<span style="color: #4D4D4D;">&#039;$1&#039;</span>',
			'/\&quot;(.*?)\&quot;/s'                      => '<span style="color: #4D4D4D;">&quot;$1&quot;</span>',
			'/\&lt\;\&lt\;\&lt\;([a-zA-Z]+)(.*?)(\1|$)/s' => '&lt;&lt;&lt;$1<span style="color: #4D4D4D;">$2</span>$3',
			'/\?/'                                        => '<span style="color: blue;">?</span>',
			'/(\&lt\;|\&gt\;)/i'                          => '<span style="color: blue;">$1</span>',
			'/(\&amp\;|[^\/]\*[^\/]|\|)/'                 => '<span style="color: blue;">$1</span>',
			'/(\(|\)|\{|\}|\[|\])/'                       => '<span style="color: red;">$1</span>',
			'/(\&\#039\;|\&quot\;)/'                      => '<span style="color: #4D4D4D;">$1</span>',
			'/(\.|\,)/'                                   => '<span style="color: purple;">$1</span>',
			'/&#58;/'                                     => '<span style="color: purple;">:</span>',
			'/(\$|var )([a-zA-Z0-9\_]+)/'                 => '<span style="color: #68228B;">$1$2</span>',
			'/\&\#59\;/i'                                 => '<span style="color: purple;">;</span>',
			'/!/'                                         => '<span style="color: blue;">!</span>',
			'/&\#61\;/'                                   => '<span style="color: blue;">=</span>',
			'/\/\/(.*?)(\n|$)/'                           => '<span style="color: green; font-style: italic;">//$1$2</span>',
			'/\/\*(.*?)\*\//s'                            => '<span style="color: green; font-style: italic;">/\*$1*\/</span>',
			'/[^\: |\&]\#(.*?)\n/i'                       => "<span style=\"color: brown;\">#\$1\n</span>",
			'/^\#(.*?)\n/i'                               => "<span style=\"color: brown;\">#\$1\n</span>" //duplicate of above - for start of string
			);
			
			$code = preg_replace(array_keys($syntax), $syntax, $code);
			
			//defined syntax
			$syntax_defined = array('this', 'echo', 'print', 'if', 'else', 'for', 'do', 'while', 'include', 'array', 'object', 'define', 'const', 'int', 'signed', 'unsigned', 'short', 'long', 'char', 'string', 'bool', 'float', 'double', 'void', 'function', 'class', 'new', 'private', 'public', 'protected', 'static', 'var', 'global', 'return', 'using', 'true', 'false', 'null', 'namespace', 'use', 'as', 'or', 'struct', 'typedef', 'extern', 'switch', 'case', 'break', 'continue', 'default', 'friend', 'goto');
			foreach($syntax_defined as $value)
			{
				$code = preg_replace('/\b'.$value.'\b/i', '<span style="color: #0000FF;">'.$value.'</span>', $code);
			}
			
			//remove highlighting from quotes & comments
			$syntax_extract = array_keys($syntax);
			$code = preg_replace_callback(array($syntax_extract[0], $syntax_extract[1], $syntax_extract[15], $syntax_extract[16]), create_function('$matches', 'return code::strip_code_tags($matches[0], \'HTML\');'), $code); #function($matches){return code::strip_code_tags($matches[0], 'HTML');}
			
			//restore special entities
			$code = str_replace(array('__SINGLEQUOTE__', '__DOUBLEQUOTE__'), array('\\&#039;', '\\&quot;'), $code);
			
			//append line numbers
			#$lines = explode("\n", $code);
			#$code = array();
			#foreach($lines as $key => $value)
			#{
			#	$key += 1;
			#	$code[] = "$key.&nbsp;&nbsp;$value\n";
			#}
			#$code = implode($code);
		}
		
		return $this->codinate(trim($code), $code_type);*/
		
		include_once $_SERVER['DOCUMENT_ROOT'].'/bin/geshi/geshi.php';
		static $geshi;
		
		$code = ($this->code_vars['parse_html']) ? $code : val::decode($code);
		$code_type = ($code_type != null) ? $code_type : 'php';
		
		if(!$geshi instanceof GeSHi)
		{
			$geshi = new GeSHi($code, $code_type);
		}
		else
		{
			$geshi->set_source($code);
			$geshi->set_language($code_type);
		}
		
		$geshi->set_header_type(GESHI_HEADER_PRE_VALID);
		$geshi->enable_line_numbers(GESHI_NORMAL_LINE_NUMBERS);
		$geshi->enable_keyword_links(false);
		
		return $this->codinate($geshi->parse_code(), $code_type);
	}
	public function parse_code($code, $parse_code_ws = false)
	{
		global $config_init;
		
		//noparse
		$code = preg_replace('/\[noparse\](.*?)(\[\/noparse\]|$)/isSe', 'str_replace(array(\'[\', \']\'), array(\'|jl;\', \'|jg;\'), stripslashes('.(($this->code_vars['parse_html']) ? 'val::encode(\'$1\')' : '\'$1\'').'))', $code);
		//code
		$code = preg_replace_callback('/\[code(\=(.*?))?\](.*?)(\[\/code\]|$)/isS', array($this, 'geshi'), $code);
		//uncoded urls
		$code = preg_replace('/(?<!\[url\]|\[url=|\[img=|\[video=|\[music=)\b(?>((?:https?|ftp):\/\/[-A-Z0-9+&@\#\/%?=~_|$!:,.;]*[A-Z0-9+&@\#\/%=~_|$]))(?!\[\/url\])/i', '[url]$1[/url]', $code);
		//magic_urls
		$code = preg_replace_callback('/\[url\](.*?)(\[\/url\]|$)/isS', array($this, 'magic_url'), $code);
		
		//code
		$code_parse = array(
		//quotes
		'/\[quote(\=(.*?))?(\surl=(.*?))?\](.*?)(\[\/quote\]|$)/isS' => '<div style="-moz-border-radius: 5px; -khtml-border-radius: 5px; -webkit-border-radius: 5px; background-color: #CADCEB; padding: 2.5px;"><cite><span style="font-weight: bold;">Quote</span> '.profile::profile_link('$2', 'style="border-bottom: 1px dotted blue;"').' <a href="$4"><img src="./templates/css/img/last_post.gif" alt="$4" /></a></cite><br /><blockquote style="padding: 0 20pt 0 20pt; margin-left: 10px; background: url(./templates/css/img/icons/quote.gif) no-repeat;">$5</blockquote></div>',
		//alias (alias of profile::profile_link())
		'/\[alias\=(.*?)\](.*?)(\[\/alias\]|$)/isS'                  => '<a href="'.val::encode($_SERVER['PHP_SELF']).'?action=profile&amp;status=profile&amp;profile=index&amp;alias=$1">$2</a>',
		//links
		'/\[url\](.*?)(\[\/url\]|$)/isS'                             => '<a style="border-bottom: 1px dotted purple;" href="$1" onclick="if(!this.href.match(/'.str_replace('.', '\.', $config_init->get_config('site_domain')).'/) && this.href.substr(1) != \'/\' && this.href.substr(2) != \'./\'){this.target = \'_blank\';}">$1</a>',
		'/\[url\=(.*?)\](.*?)(\[\/url\]|$)/isS'                      => '<a style="border-bottom: 1px dotted purple;" href="$1" onclick="if(!this.href.match(/'.str_replace('.', '\.', $config_init->get_config('site_domain')).'/) && this.href.substr(1) != \'/\' && this.href.substr(2) != \'./\'){this.target = \'_blank\';}">$2</a>',
		//images/music/video
		'/\[img\=(.*?)\]/isS'                                        => '<img style="max-width: 480px; max-height: 360px;" src="$1" alt="$1" />',
		'/\[video\=(.*?)\]/isS'                                      => '<object width="425px" height="344px"><param name="movie" value="$1"></param><param name="allowFullScreen" value="true"></param><param name="allowscriptaccess" value="always"></param><embed src="$1" type="application/x-shockwave-flash" allowscriptaccess="always" allowfullscreen="true" width="425px" height="344px"></embed></object>',
		'/\[music\=(.*?)\]/isS'                                      => '<object><embed src="$1" autostart="false" style="height: 20px;" /></object>',
		//text
		'/\[u\](.*?)(\[\/u\]|$)/isS'                                 => '<span style="text-decoration: underline;">$1</span>',
		'/\[b\](.*?)(\[\/b\]|$)/isS'                                 => '<span style="font-weight: bold;">$1</span>',
		'/\[i\](.*?)(\[\/i\]|$)/isS'                                 => '<span style="font-style: italic;">$1</span>',
		'/\[s\](.*?)(\[\/s\]|$)/isS'                                 => '<span style="text-decoration: line-through;">$1</span>',
		'/\[colo(u)?r\=(.*?)\](.*?)(\[\/color\]|$)/isS'              => '<span style="color: $2">$3</span>',
		'/\[size\=(.*?)\](.*?)(\[\/size\]|$)/isS'                    => '<span style="font-size: $1em;">$2</span>',
		'/\[list\](.*?)(\[\/list\]|$)/isSe'                          => '\'<ul style="list-style: disc; margin-left: 15px;"><li>\'.str_replace("\n", \'</li><li>\', \'\1\').\'</li></ul>\''
		);
		
		$code = $this->preg_replace_recursive($code, $code_parse);
		
		//white space
		$code = ($parse_code_ws === true) ? $this->parse_code_ws($code) : $code;
		
		//return code entities
		$code = str_replace(array('|jl;', '|jg;'), array('[', ']'), $code);
		
		return $code;
	}
	public function parse_code_ws($code)
	{
		if(!is_array($code))
		{
			//init callback: void html tags
			$code = preg_replace_callback('/(^|>)[^<]+/', array($this, 'parse_code_ws'), nl2br($code));
			
			return $code;
		}
		else
		{
			$ws_code_parse = array(
			'  '  => ' &nbsp;',
			'	' => '&nbsp; &nbsp; '
			);
			
			return str_replace(array_keys($ws_code_parse), $ws_code_parse, reset($code));
		}
	}
	public function codinate($code, $code_type = null)
	{
		//return formatted code
		return '<div style="max-height: 350px; overflow: auto; border: 1px solid gray; /*color: black;*/"><div style="margin-bottom: 10px; float: left; border-bottom: 1px dotted gray;">Code'.(($code_type != null) ? ' ('.$code_type.')' : null).':</div><!--code--><div style="clear: both; white-space: nowrap;">'.trim($code).'</div></div>';
	}
	//smileys
	public function smiley($code)
	{
		global $config_init;
		
		static $smileys = array();
		
		//get smileys from smileys dir
		if($smileys == array())
		{
			$dir = opendir($_SERVER['DOCUMENT_ROOT'].'/templates/css/img/icons/smileys');
			while($file = readdir($dir))
			{
				if(!in_array($file, array('.', '..')))
				{
					$smiley = str_replace('.gif', null, $file);
					
					$smileys[] = $smiley;
					//html encoded version
					if(val::encode($smiley) != $smiley)
					{
						$smileys[] = val::encode($smiley);
					}
				}
			}
			closedir($dir);
			
			//preg quote smileys
			$smileys = array_map(create_function('$smiley', 'return preg_quote($smiley, \'/\');'), $smileys); #function($smiley){return preg_quote($smiley, '/');}
		}
		
		$code = preg_replace('/(^|\s)('.implode('|', $smileys).')/', '$1<img src="/templates/css/img/icons/smileys/$2.gif" alt="$2" />', $code);
		
		return $code;
	}
	protected function preg_replace_recursive($code, $code_parse)
	{
		$coded = false;
		while($coded === false)
		{
			$code = preg_replace(array_keys($code_parse), $code_parse, $code);
			
			$coded = true;
			foreach($code_parse as $key => $value)
			{
				if(preg_match($key, $code))
				{
					$coded = false;
				}
				else
				{
					#goto preg_replace_recursive_return;
				}
			}
		}
		
		#preg_replace_recursive_return:
		return $code;
	}
	public function strip_code_tags($code, $tags = '\[.*?\]')
	{
		return (strtoupper($tags) == 'HTML') ? strip_tags($code) : preg_replace('/'.$tags.'/is', null, $code);
	}
	public function parse_code_php($code)
	{
		return preg_replace_callback('/(\[php\]|\<\?php)(.*?)(\?\>|\[\/php\])/isS', create_function('$matches', 'return eval(code::strip_code_tags($matches[0], \'(\[php\]|\[\/php\]|\<\?php|\?\>)\'));'), $code); #function($matches){return eval(code::strip_code_tags($matches[0], '(\[php\]|\[\/php\]|\<\?php|\?\>)'));}
	}
	protected function magic_url($url)
	{
		list($url, $url_origin) = array($this->strip_code_tags($url[1]), reset($url));
		
		$ch = curl_init();
		
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 1);
		
		$cURL = curl_exec($ch);
		curl_close($ch);
		
		$dom = new DOMDocument;
		@$dom->loadHTML($cURL);
		$title = preg_replace('/\s+/', ' ', $dom->getElementsByTagName('title')->item(0)->nodeValue);
		
		return ($title != null) ? '[url='.$url.']'.$title.'[/url]' : $url_origin;
	}
}
?>