<?php
include $_SERVER['DOCUMENT_ROOT'].'/config/lib.php';

class SteamProfilesIni
{
	public static function WriteIniFile(array $Data, $File = 'SteamProfiles.ini', $HasSections = false)
	{
		$Content = "";
		if($HasSections)
		{
			foreach($Data as $key => $elem)
			{
				$Content .= "[".$key."]\n";
				foreach($elem as $key2 => $elem2)
				{
					if(is_array($elem2))
					{
						for($i = 0; $i < count($elem2); $i++)
							$Content .= $key2."[] = \"".$elem2[$i]."\"\n";
					}
					else
						$Content .= $key2." = \"".$elem2."\"\n";
				}
			}
		}
		else
		{
			foreach($Data as $key => $elem)
			{
				if(is_array($elem))
				{
					for($i = 0; $i < count($elem); $i++)
						$Content .= $key2."[] = \"".$elem[$i]."\"\n";
				}
				else
					$Content .= $key2." = \"".$elem."\"\n";
			}
		}

		if(!$Handle = fopen($File, 'w'))
			return false;
		if(!fwrite($Handle, $Content))
			return false;

		fclose($Handle);

		return true;
	}
}

print '<h1>Steam Profiles Ini Writer</h1><br />';

if(!form::submitted())
{
	print "<form id=\"search\" action=\"".val::encode($_SERVER['PHP_SELF'])."\" method=\"post\">
		Steam Executable Location: <input type=\"text\" name=\"File\" style=\"width: 250px;\" value=\"C:\\Program Files\\Steam\\steam.exe\" /><br />
		Additional Steam Parameters: <input type=\"text\" name=\"Parameters\" style=\"width: 250px;\" value=\"\" /><br />
		Your SteamID: <input type=\"text\" name=\"SteamID\" style=\"width: 250px;\" value=\"STEAM_\" /><br />
		<br />
		Steam profiles:<br />
		<div id=\"profiles\">
			Username: <input type=\"text\" name=\"Username[]\" /> Password: <input type=\"text\" name=\"Password[]\" /><br />
		</div>
		<input type=\"button\" onclick=\"document.getElementById('Profiles').innerHTML += 'Username: <input type=&quot;text&quot; name=&quot;Username[]&quot; /> Password: <input type=&quot;password&quot; name=&quot;Password[]&quot; /><br />';\" value=\"Add Profile\" />
		<input type=\"submit\" value=\"Create\" />
	</form>";
}
else if(form::submitted())
{
	$Data = array(
	'Steam'		=> array('File' => $_POST['File'], 'Parameters' => null, 'Force' => 'false', 'SteamID' => $_POST['SteamID']),
	'Profiles'	=> array()
	);

	foreach($_POST['Username'] as $index => $username)
	{
		$Data['Profiles'][$index] = $_POST['Username'][$index].' '.$_POST['Password'][$index];
	}

	if(SteamProfilesIni::WriteIniFile($Data, 'SteamProfiles.ini', true))
		print 'Success!';
	else
		print 'An error occured.';
}
?>
