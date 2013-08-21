<?php
include $_SERVER['DOCUMENT_ROOT'].'/config/lib.php';

class cfg
{
	const
		BACKUP_DIR = 'srcds_l/backup/update';
	
	private
		$ftp;
	
	public function __construct()
	{
		global $config_init;
		
		if($this->ftp = ftp_connect(reset(explode(':', $config_init->get_config('gameserver.address')))))
			print '<span style="color: green;">Connected</span>.<br />';
		else
			print '<span style="color: red;">Error connecting.</span>';
	}
	public function __destruct()
	{		
		ftp_close($this->ftp);
	}
	
	public function update_server()
	{
		global $config_init;
		
		if(ftp_login($this->ftp, $config_init->get_config('gameserver.auth.username'), $config_init->get_config('gameserver.auth.password')))
		{
			$config_init->load($_SERVER['DOCUMENT_ROOT'].'/bin/server/update.cfg');
			$files = $config_init->parse()->get_object()->files;
			
			foreach($files as $file) #list()
			{
				list($file_source, $file_destination) = $file;
				if(!strstr($file_destination, '.'))
					$file_destination = $file_destination.((substr($file_destination, strlen($file_destination) - 1, strlen($file_destination)) == '/') ? null : '/').basename($file_source);
				
				$file_exists	= in_array($file_destination, ftp_nlist($this->ftp, dirname($file_destination)));
				$upload			= (isset($_GET['upload']) && $_GET['upload']);
				
				if(!$upload)
					print sprintf('<span style="color: blue;">%s file &quot;%s&quot; to &quot;%s&quot;.</span><br />', (!$file_exists) ? 'Upload' : '<span style="color: purple;">Update</span>', $file_source, $file_destination); #printf
				else
				{
					if($file_exists)
					{
						$backup_file = self::BACKUP_DIR.'/'.str_replace('/', '_', $file_destination);
						
						if(ftp_rename($this->ftp, $file_destination, $backup_file))
							print sprintf('<span style="color: purple;">Backed up &quot;%s&quot; to &quot;%s&quot;.</span><br />', $file_destination, $backup_file); #printf
						else
							print sprintf('<span style="color: red;">Error backing up &quot;%s&quot;.</span><br />', $file_destination); #printf
					}
					
					if(ftp_fput($this->ftp, $file_destination, fopen($_SERVER['DOCUMENT_ROOT'].'/bin/server/mods/l4d/'.$file_source, 'r'), FTP_ASCII))
						print sprintf('<span style="color: blue;">Uploaded file &quot;%s&quot; to &quot;%s&quot;.</span><br />', $file_source, $file_destination); #printf
					else
						print sprintf('<span style="color: red;">Error uploading &quot;%s&quot;.</span><br />', $file_source); #printf
				}
			}
			
			if(!$upload)
				print '<a href="'.val::encode($_SERVER['PHP_SELF']).'?upload=1">Confirm</a>';
			else
				print '<span style="color: green;">Done.</span>';
		}
		else
			print '<span style="color: red;">Error authenticating.</span>';
			
	}
}

secure::secure(alias::user(alias::USR_ADMINISTRATOR));

$cfg = new cfg;
$cfg->update_server();
?>
