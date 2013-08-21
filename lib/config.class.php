<?php
class config extends parse
{
	private
		$config = array();
	
	public function __construct($file = null)
	{
		if($file != null)
			$this->load($file);
	}
	public function get_key($key)
	{
		$values = $this->get_array();
		
		return $values[$key];
	}
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
}
	
class parse
{
	private
		$filename,
		$file,
		$structure = array();
	
	private
		$in_name		= false,
		$in_struct		= array(false),
		$in_quote		= false,
		$in_comment		= false,
		$in_varname		= false,
		$struct_count	= 0,
		$open_structs	= array();
	
	private
		$buffer,
		$structnames	= array(),
		$varnames		= array();
		
	public function &load($file)
	{
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
				return trigger_error('File '.$file.' not found.');
			}
		}
		
		return $this;
	}

	public function &parse()
	{
		for($i = 0; $i < strlen($this->file); $i++)
		{
			if($this->file[$i] == '"' && !$this->in_quote && !$this->in_comment)
				$this->in_quote = true;
			else if($this->file[$i] == '"' && $this->file[$i - 1] != '\\' && $this->in_quote && !$this->in_comment)
				$this->in_quote = false;
			
			if($this->file[$i] == '{' && !$this->in_quote && !$this->in_comment)
			{
				$this->in_struct[$this->struct_count] = true;
				$this->struct_count++;
			}
			else if($this->file[$i] == '}' && !$this->in_quote && !$this->in_comment)
			{
				$this->in_struct[$this->struct_count] = false;
				unset($this->open_structs[$this->struct_count]);
				$this->struct_count--;
			}
			if($this->file[$i] == '/' && $this->file[$i + 1] == '/' && !$this->in_quote)
				$this->in_comment = true;
			else if($this->in_comment && $this->file[$i] == "\n")
				$this->in_comment = false;
			
			if($this->in_comment)
				continue;
			
			if(($this->in_name || (!$this->in_quote && !$this->in_comment && $this->_next_token($i, '{'))) && ctype_alnum($this->file[$i]))
			{
				$this->in_name = true;
				$this->buffer .= $this->file[$i];
			}
			else if($this->in_name && ($this->file[$i] == "\n" || $i == strlen($this->file) - 1))
			{
				$this->in_name = false;
				$this->structnames[] = $this->buffer;
				$this->buffer = null;
				$this->open_structs[$this->struct_count] = end($this->structnames);
				
				eval("\$this->structure[".$this->_eval()."] = array();");
			}
			else if(($this->in_varname || ($this->in_struct && !$this->in_quote)) && (ctype_alnum($this->file[$i]) || $this->file[$i] == '_'))
			{
				$this->in_varname = true;
				$this->buffer .= $this->file[$i];
			}
			else if($this->in_varname && ($this->file[$i] == ' ' || $this->file[$i] == "\t"))
			{
				$this->in_varname = false;
				$this->varnames[] = $this->buffer;
				$this->buffer = null;
				
				eval("\$this->structure[".$this->_eval()."]['".end($this->varnames)."'] = null;");
			}
			else if($this->in_quote && ($this->file[$i] != '"' || ($this->file[$i] == '"' && $this->file[$i - 1] == '\\')) && ($this->file[$i] != '\\' || $this->file[$i - 1] == '\\'))
				$this->buffer .= $this->file[$i];
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
}
?>