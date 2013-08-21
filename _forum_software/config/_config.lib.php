<?php
class _config
{
	//interface
	private
		$cfg,
		$config;
	private static
		$_cfg;
	
	public static function &get_instance()
	{
		return new self;
	}
	private function __construct()
	{
		#$this->load($_SERVER['DOCUMENT_ROOT'].'/config/cfg.cfg');
		$this->load('config/cfg.cfg');
		$this->config = $this->parse()->get_array();
	}
	public function get_config($key = null, $raw = false)
	{
		//return config values (with some debugging...)
		if($key == null)
		{
			//return entire config array
			if(!$raw)
			{
				return (!empty($this->config)) ? $this->config : trigger_error('Config is non-existent!', E_USER_ERROR);
			}
			//return raw config
			else
			{
				return $this->file;
			}
		}
		else
		{
			//extract struct & key
			//...
			if(!strstr($key, '.'))
				$key = reset(array_keys($this->config)).'.'.$key;
			$_eval = '';
			foreach(explode('.', $key) as $struct)
				$_eval .= "['$struct']";
			$_eval = substr($_eval, 1, strlen($_eval) - 2);
			
			//alias of self::_eval()
			eval("\$cfg_value = \$this->config[{$_eval}]; if(\$cfg_value == null) \$cfg_value = \$this->structure[{$_eval}];");
			
			//check struct & key/value exists
			if(isset($cfg_value))
			{
				return preg_replace_callback('/var\((.*?)\)/i', create_function('$matches', 'foreach($GLOBALS as $key => $value){global $$key;} return eval(\'return $\'.$matches[1].\';\');'), $cfg_value); #function($matches){foreach($GLOBALS as $key => $value){global $$key;} return eval('return $'.$matches[1].';');}
			}
			else
			{
				return trigger_error('Config value "'.val::encode($key).'" is non-existent!', E_USER_ERROR);
			}
		}
	}
	
	//parsing
	//file vars
	private
		$filename,
		$file,
		$structure = array();
	
	//parse vars
	private
		$in_name		= false,
		$in_struct		= array(false),
		$in_quote		= false,
		$in_comment		= false,
		$in_varname		= false,
		$struct_count	= 0,
		$open_structs	= array();
	
	//buffer vars
	private
		$buffer,
		$structnames	= array(),
		$varnames		= array();
		
	//load cfg file
	public function &load($file)
	{
		//clear the structure
		$this->structure = array();
		
		if($file != $this->filename)
		{
			if(file_exists($file))
			{
				$this->filename = $file;
				
				$fh = fopen($this->filename, 'r');
				$this->file = fread($fh, filesize($this->filename));
				fclose($fh);
			}
			else
			{
				throw new Exception('File '.$file.' not found.');
			}
		}
		
		return $this;
	}
	
	//parse cfg file
	public function &parse()
	{
		//parse the file!
		for($i = 0; $i < strlen($this->file); $i++)
		{
			//check quote start
			if($this->file[$i] == '"' && !$this->in_quote && !$this->in_comment)
				$this->in_quote = true;
			//check quote end
			else if($this->file[$i] == '"' && $this->file[$i - 1] != '\\' && $this->in_quote && !$this->in_comment)
				$this->in_quote = false;
			
			//check struct start
			if($this->file[$i] == '{' && !$this->in_quote && !$this->in_comment)
			{
				$this->in_struct[$this->struct_count] = true;
				$this->struct_count++;
			}
			//check struct end
			else if($this->file[$i] == '}' && !$this->in_quote && !$this->in_comment)
			{
				$this->in_struct[$this->struct_count] = false;
				unset($this->open_structs[$this->struct_count]);
				$this->struct_count--;
			}
			//check comment start
			if($this->file[$i] == '/' && $this->file[$i + 1] == '/' && !$this->in_quote)
				$this->in_comment = true;
			//check comment end
			else if($this->in_comment && $this->file[$i] == "\n")
				$this->in_comment = false;
			
			//check comment
			if($this->in_comment)
				continue;
			
			//check struct name start
			if(($this->in_name || (!$this->in_quote && !$this->in_comment && $this->_next_token($i, '{'))) && ctype_alnum($this->file[$i]))
			{
				$this->in_name = true;
				$this->buffer .= $this->file[$i];
			}
			//check struct name end
			else if($this->in_name && ($this->file[$i] == "\n" || $i == strlen($this->file) - 1))
			{
				$this->in_name = false;
				$this->structnames[] = $this->buffer;
				$this->buffer = null;
				$this->open_structs[$this->struct_count] = end($this->structnames);
				
				eval("\$this->structure[".$this->_eval()."] = array();");
			}
			//check var name start
			else if(($this->in_varname || ($this->in_struct && !$this->in_quote)) && (ctype_alnum($this->file[$i]) || $this->file[$i] == '_'))
			{
				$this->in_varname = true;
				$this->buffer .= $this->file[$i];
			}
			//check var name end
			else if($this->in_varname && ($this->file[$i] == ' ' || $this->file[$i] == "\t"))
			{
				$this->in_varname = false;
				$this->varnames[] = $this->buffer;
				$this->buffer = null;
				
				eval("\$this->structure[".$this->_eval()."]['".end($this->varnames)."'] = null;");
			}
			//check value start
			else if($this->in_quote && ($this->file[$i] != '"' || ($this->file[$i] == '"' && $this->file[$i - 1] == '\\')) && ($this->file[$i] != '\\' || $this->file[$i - 1] == '\\'))
				$this->buffer .= $this->file[$i];
			//check value end
			else if(!$this->in_quote && $this->file[$i] == '"')
			{
				eval("if(\$this->buffer === null)
					\$this->buffer = '';
				
				if(\$this->structure[".$this->_eval()."][end(\$this->varnames)] === null)
					\$this->structure[".$this->_eval()."][end(\$this->varnames)] = \$this->buffer;
				else
				{
					if(!is_array(\$this->structure[".$this->_eval()."][end(\$this->varnames)]))
						\$this->structure[".$this->_eval()."][end(\$this->varnames)] = array(\$this->structure[".$this->_eval()."][end(\$this->varnames)]);
					
					\$this->structure[".$this->_eval()."][end(\$this->varnames)][] = \$this->buffer;
				}
				");
				/*
				//singular
				if($this->structure[end($this->structnames)][end($this->varnames)] === null)
					$this->structure[end($this->structnames)][end($this->varnames)] = $this->buffer;
				//array
				else
				{
					if(!is_array($this->structure[end($this->structnames)][end($this->varnames)]))
						$this->structure[end($this->structnames)][end($this->varnames)] = array($this->structure[end($this->structnames)][end($this->varnames)]);
					
					$this->structure[end($this->structnames)][end($this->varnames)][] = $this->buffer;
				}
				*/
				$this->buffer = null;
			}
		}
		
		return $this;
	}
	
	private function _next_token($index, $token)
	{
		for($i = $index; $i < strlen($this->file); $i++)
			if(!in_array($this->file[$i], array(" ", "\t", "\n", "\r")) && !ctype_alnum($this->file[$i]))
				return ($this->file[$i] == $token);
	}
	private function _eval()
	{
		$eval = '';
		for($ii = 0; $ii < sizeof($this->open_structs); $ii++)
			$eval .= "['{$this->open_structs[$ii]}']";
		
		return substr($eval, 1, strlen($eval) - 2);
	}
	
	//write cfg file
	public function write($data, $raw = false)
	{
		if(!$raw)
		{
			$file = '';
			
			foreach($data as $key => $value)
			{
				$file .= $key."\n{\n";
				
				foreach($value as $key2 => $value2)
				{
					if(is_array($value2))
						$value2 = implode('" "', val::post($value2, false, '"'));
					else
						$value2 = val::post($value2, false, '"');
					
					$file .= "\t".$key2."\t\"".$value2."\"\n";
				}
				
				$file .= "}\n";
			}
		}
		else
			$file = $data;
		
		$fh = fopen($this->filename, 'w+');
		fwrite($fh, $file);
		fclose($fh);
	}
	
	public function __call($method, array $args)
	{
		//assign code vars
		switch($method)
		{
			case 'get':
				return $this->structure;
			break;
			case 'get_array':
				return (array) $this->structure;
			break;
			case 'get_object':
				return (object) $this->structure;
			break;
		}
	}
}
?>