<?php
class template
{
	const	TPL_DIR = '/templates/';
	
	public function template($file = null)
	{
		if($file != null)
			$this->load($file);
	}
	public function load($file)
	{
		if($file == null)
			return trigger_error('Template file '.$file.' not found.');
		
		include_once self::TPL_DIR.$file;
		
		return true;
	}
}
?>