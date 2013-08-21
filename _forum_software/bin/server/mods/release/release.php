<?php
include $_SERVER['DOCUMENT_ROOT'].'/config/lib.php';

if(extension_loaded('zip'))
{
	$source = $_source = end(explode('/', $_GET['file']));
	$tmpname = '/tmp/release_'.sha1(microtime()).'.zip';
	
	if(file_exists($source))
	{
		$zip = new ZipArchive();
		
		if($zip->open($tmpname, ZIPARCHIVE::CREATE | ZIPARCHIVE::OVERWRITE))
		{
			$source = realpath($source);
			
			if(is_dir($source))
			{
				$files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($source), RecursiveIteratorIterator::SELF_FIRST);
				
				foreach($files as $file)
				{
					if(!in_array(basename($file), array('.', '..')))
					{
						$file = realpath($file);
						
						#if(is_dir($file))
							#$zip->addEmptyDir(str_replace($source.'/', '', $file.'/'));
						/*#else*/ if(is_file($file))
							$zip->addFromString(str_replace($source.'/', '', $file), file_get_contents($file));
					}
				}
			}
			else if(is_file($source))
				$zip->addFromString(basename($source), file_get_contents($source));
		}
		else
			print 'Error creating zip file.';

		$zip->close();
		
		header('Content-Type: application/zip');
		header('Content-Disposition: filename="'.$_source.'.zip"');
		header('Content-length: '.filesize($tmpname));
		echo file_get_contents($tmpname);
	}
	else
		print 'File does not exist.';
}
else
	print 'Error loading zip extension.';
?>