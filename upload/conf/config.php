<?php
	define('server', 'localhost');
	define('admin', 'jackpf_root');
	define('pass', 'death666');
	define('db', 'jackpf_Website2');
	define('tb', 'Alias');
	ini_set('display_errors', 0);
	
	class server
	{
		public $dir, $public;
		function server()
		{
			if(isset($_SESSION['Alias']))
			{
				$this->dir = 'files/'.$_SESSION['Alias'];
			}
			else
			{
				$this->dir = 'files/public';
				$this->public = '<br /><br /><span style="font-size: 10px;">Warning - you are not logged in. This file will be uploaded to a public directory.</span>';
			}
			$_FILES = (isset($_FILES['file'])) ? $_FILES['file'] : null;
		}
		function respond($response)
		{
			echo '<script type="text/javascript">
				/*<![CDATA[*/
					window.onload = function()
					{
						window.parent.window.response(\''.$response.'\');
					}
				/*]]>*/
			</script>';
			die($response);
		}
		function fname_check($file, $i = 0)
		{
			if(file_exists($this->dir.'/'.$file))
			{
				$file = explode('.', $file);
				$key = count($file) - 2;
				$file[$key] = preg_replace('/\[[0-9]{0,}\]/', null, $file[$key]);
				$file[$key] .= '['.$i.']';
				$file = implode('.', $file);
			}
			if(file_exists($this->dir.'/'.$file))
			{
				$file = $this->fname_check($file, $i + 1);
			}
			$file = str_replace(array('\'', '"', '\\'), null, $file);
			return $file;
		}
		function val($filename)
		{
			$allowed = array(
			'jpg',
			'png',
			'gif',
			'mp3',
			'txt',
			'zip',
			'html'
			);
			if(!in_array(strtolower(end(explode('.', $filename))), $allowed))
			{
				$this->respond('An error has occured.');
			}
		}
		function aval($alias)
		{
			return (preg_match('/^[\w\d_-]+$/i', $alias)) ? $alias : false;
		}
		function mime($ext)
		{
			switch($ext)
			{
				case 'jpg': case 'gif': case 'png':
					$mime = 'image/'.$ext;
					#stupid IE bug fix
					if(preg_match('/msie/i', $_SERVER['HTTP_USER_AGENT']) && $ext == 'jpg')
					{
						$mime = 'image/pjpeg';
					}
				break;
				case 'mp3':
					$mime = 'audio/mpeg';
				break;
				default:
					$mime = 'text/plain';
				break;
			}
			return $mime;
		}
		function trim($str, $limit)
		{
			if(strlen(strip_tags($str)) > $limit)
			{
				return substr($str, 0, $limit).'...';
			}
			else
			{
				return $str;
			}
		}
		function secure()
		{
			if(!isset($_SESSION['Alias']))
			{
				header('Location: '.$_SERVER['PHP_SELF'].'?action=userpanel&status=logout');
				die();
			}
		}
		function dir($file)
		{
			return (is_dir($file) || !preg_match('/\./', $file)) ? true : false;
		}
	}
?>