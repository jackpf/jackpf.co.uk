<?
// PHP COD2 RCon 1.1
// Created by Ashus in 2007

require 'config.inc.php';
include 'language.inc.php';

$table_color_1 = '444444';
$table_color_2 = '333333';


echo '<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=windows-1250">
<meta http-equiv="Cache-Control" content="no-store,max-age=-1">';

if ($refresh_rate !== false) {echo '<meta http-equiv="refresh" content="'.$refresh_rate.'">';}
echo '<title>COD2 RCon</title>
<link rel="stylesheet" type="text/css" href="css.css">
<base target="_self">
<script type="text/javascript">
<!--
function removec(s, t)
	{
	i = s.indexOf(t);
	r = "";
	if (i == -1) return s;
	r += s.substring(0,i) + removec(s.substring(i + t.length), t);
	return r;
	}

function Mail(n)
	{
	text = prompt("'.$lang['enter_message'].' "+n,"");
	if (! text) return false;
	text = removec(text,"\"");
	var elem = document.getElementById(\'cmdbox\');
	if (n == "all")
		{elem.value = "say \"^6'.$admin_name.' (All): ^7"+text+"\"";} else
		{elem.value = "tell "+n+" \"^6'.$admin_name.' (Priv.): ^7"+text+"\"";}
	document.cmd_form.submit();
	}

function Kick(n)
	{
	var elem = document.getElementById(\'cmdbox\');
	elem.value = "clientkick "+n;
	document.cmd_form.submit();
	}
-->
</script>
</head><body class="padded">
<h1><a href="login.php">Login</a> / COD2 RCon</h1>';
ob_flush();flush(); // display header before contacting target server

$server_addr = "udp://" . $server_ip;
@$connect = fsockopen($server_addr, $server_port, $re, $errstr, 2);
if (! $connect) { die('Can\'t connect to COD gameserver.'); }
socket_set_timeout ($connect, 2);
$send = "\xff\xff\xff\xff" . 'rcon "' . $server_rconpass . '" status'."\n";
fputs($connect, $send);

//$output = fread ($connect, 1);
//if (! empty ($output)) {
	do {
	$status_pre = socket_get_status ($connect);
	$output = $output . fread ($connect, 1024);
	$status_post = socket_get_status ($connect);
	} while ($status_pre['unread_bytes'] != $status_post['unread_bytes']);
//};

fclose($connect);

function ColorizeName($s) {
	$pattern[0]="^0";	$replacement[0]='</font><font color="black">';
	$pattern[1]="^1";	$replacement[1]='</font><font color="red">';
	$pattern[2]="^2";	$replacement[2]='</font><font color="lime">';
	$pattern[3]="^3";	$replacement[3]='</font><font color="yellow">';
	$pattern[4]="^4";	$replacement[4]='</font><font color="blue">';
	$pattern[5]="^5";	$replacement[5]='</font><font color="aqua">';
	$pattern[6]="^6";	$replacement[6]='</font><font color="#FF00FF">';
	$pattern[7]="^7";	$replacement[7]='</font><font color="white">';
	$pattern[8]="^8";	$replacement[8]='</font><font color="white">';
	$pattern[9]="^9";	$replacement[9]='</font><font color="gray">';
	$pattern[10]="ˇ!ˇ";	$replacement[10]='<span style="background-color: yellow; color: black">&nbsp;</span>';

	$s = str_replace($pattern, $replacement, htmlspecialchars($s));
	$i = strpos($s, '</font>');
	if ($i !== false)
		{return substr($s, 0, $i) . substr($s, $i+7, strlen($s)) . '</font>';}
	else
		{return $s;}
}

$output = explode ("\xff\xff\xff\xffprint\n", $output);
unset($output[0]);
$output = implode ('ˇ!ˇ', $output);

$output = ColorizeName($output);
$output = explode ("\n", $output);
$color2 = false;
$cnt = count($output)-2;

$list_of_gtypes = array();
$list_of_maps = array();
include 'maplist.inc.php';

$curmap = substr($output[0], 5);
$curmap_orig = $curmap;

foreach ($list_of_maps as $map)
	{
    $t = explode(' ',$map,2);
    if ($t[0] == $curmap)
		{
		$curmap = $t[1];
		break;
		}
	}

echo '<table><tr bgcolor="#'.(($color2) ? $table_color_1 : $table_color_2).'">
<td align=center width="80">'. $curmap .'</td>
<td align=center width="80"><a href="#" onclick="Mail(\'all\'); return false">'.$lang['say'].'</a></td>
<td><pre>'.$output[1]."</pre></td></tr>\n";
for($i=3; $i<$cnt; $i++)
	{
	$line = $output[$i];
	$pat[0] = "/^\s+/";
	$pat[1] = "/\s{2,}/";
	$pat[2] = "/\s+\$/";
	$rep[0] = "";
	$rep[1] = " ";
	$rep[2] = "";
	$t = preg_replace($pat,$rep,$line);

	$t = explode(' ', $t, 2);
    if (strpos($t[0], '!') !== false) $t[0] = '';
    $color2 = ! $color2;
    $is_num = is_numeric($t[0]);
	echo '<tr bgcolor="#'.(($color2) ? $table_color_1 : $table_color_2).'"><td align=center>'
		.(($is_num)?'<a href="#" onclick="Kick(\''.$t[0].'\'); return false">'.$lang['kick'].'</a>':'').'</td><td align=center>'
		.(($is_num)?'<a href="#" onclick="Mail(\''.$t[0].'\'); return false">'.$lang['whisper'].'</a>':'').'</td><td><pre>'
		.$line."</pre></td></tr>\n";
	}

$lastcmd = rawurldecode($_GET['lastcmd']);
$lastres = rawurldecode($_GET['lastres']);

if ($lastres != '')
	{
	if ($lastres != "\xff\xff\xff\xffprint\n")
	    {
	$lastres = explode ("\xff\xff\xff\xffprint\n", $lastres);
	unset($lastres[0]);
	$lastres = implode ('ˇ!ˇ', $lastres);
	    } else
	    {
		$lastres = 'OK'; // if result value is empty, but valid, return OK as default
		}
	echo '<tr bgcolor="#'.((! $color2) ? $table_color_1 : $table_color_2).'"><td align=center>'.$lang['result'].':</td><td colspan=2><pre>';
	echo (($_GET['colors'] == '1')?ColorizeName($lastres):htmlspecialchars($lastres));
	echo '</pre></td></tr>';
}
echo '</table><br>';

echo '<table><tr><td>'.$lang['command'].':</td><td><form method="POST" name="cmd_form" action="action.php?a=cmd">
<input class="query" ID="cmdbox" type="text" name="cmd" size="80" value="'.htmlspecialchars($lastcmd).'">
<input class="button" type="submit" value="'.$lang['confirm'].'">
<input type="checkbox" id="colors" name="colors" value="1"'.(((! isset($_GET['colors']))||((int) $_GET['colors'] != 0))?' checked':'').'><label for="colors"> '.$lang['colorized_output'].'</label>
</form></td></tr>';

echo '<tr><td>'.$lang['game_type'].':</td><td><form method="POST" action="action.php?a=gtype">
<select name="gtype" class="dropdown">
<option value="" selected>&nbsp;</option>';

foreach ($list_of_gtypes as &$gtype)
	{
	$t = explode(' ',$gtype,2);
	echo '<option value="'.$t[0].'">'.$t[1].'</option>';
	}

echo '</select>
<input class="button" type="submit" value="'.$lang['apply_after_map'].'"> <input class="button" name="now" type="submit" value="'.$lang['apply_now'].'">
</form></td></tr>

<tr><td>'.$lang['map'].':</td><td>
<form method="POST" action="action.php?a=map">
<select name="map" class="dropdown">
<option value="" selected>&nbsp;</option>';

foreach ($list_of_maps as &$map)
	{
	$t = explode(' ',$map,2);
	echo '<option value="'.$t[0].'"'.(($curmap_orig==$t[0])?' selected':'').'>'.$t[1].'</option>';
	}

echo '</select>
<input class="button" type="submit" value="'.$lang['apply_now'].'">
</form></td></tr>

<tr><td>'.$lang['settings'].':</td><td>
<form method="POST" action="action.php?a=set">
<input type="hidden" name="what" value="weap_snipers">
<input class="button" type="submit" name="set_0" value="'.$lang['turn_off'].'"><input class="button" type="submit" name="set_1" value="'.$lang['turn_on'].'">
'.$lang['weap_snipers'].'<br></form>
<form method="POST" action="action.php?a=set">
<input type="hidden" name="what" value="weap_shotgun">
<input class="button" type="submit" name="set_0" value="'.$lang['turn_off'].'"><input class="button" type="submit" name="set_1" value="'.$lang['turn_on'].'">
'.$lang['weap_shotgun'].'<br></form>
<form method="POST" action="action.php?a=set">
<input type="hidden" name="what" value="weap_smoke_grenades">
<input class="button" type="submit" name="set_0" value="'.$lang['turn_off'].'"><input class="button" type="submit" name="set_1" value="'.$lang['turn_on'].'">
'.$lang['weap_smoke_grenades'].'<br></form>
</td></tr>

</tr></table>';

echo '<br><small>Created by Ashus in 2007</small>
</body>
</html>';
?>
