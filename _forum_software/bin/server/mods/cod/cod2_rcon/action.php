<?
require 'config.inc.php';

$action = $_GET['a'];
$res = '';
$colors = '1';

$server_addr = "udp://" . $server_ip;
@$connect = fsockopen($server_addr, $server_port, $re, $errstr, 2);
if (! $connect) { die('Can\'t connect to COD gameserver.'); }
socket_set_timeout ($connect, 2);

function RequestToGame($cmd, $want_result=false)
	{
	global $server_rconpass, $connect;
	$send = "\xff\xff\xff\xff" . 'rcon "' . $server_rconpass . '" '.$cmd;
	fwrite($connect, $send);
    $output = '';
	if ($want_result)
		{
		//$output = fread ($connect, 1);
		//if (! empty ($output)) {
			do {
			$status_pre = socket_get_status ($connect);
			$output = $output . fread ($connect, 1024);
			$status_post = socket_get_status ($connect);
			} while ($status_pre['unread_bytes'] != $status_post['unread_bytes']);
		//	};
		}
	sleep(1);
	return $output;
	}

switch ($action)
	{
	case 'map':
		if ($_POST['map'] != '')
			RequestToGame('map ' . $_POST['map']);
		break;
	case 'gtype':
		if ($_POST['gtype'] != '')
			{
            $cmd = 'g_gametype ' . $_POST['gtype'];
			$res = RequestToGame($cmd, true);
			}
        if ($_POST['now'] != '')
			RequestToGame('map_restart');
		break;
	case 'cmd':
		if ($_POST['cmd'] != '')
		    {
		    $cmd = $_POST['cmd'];
			$res = RequestToGame($cmd, true);
			$colors = $_POST['colors'];
			}
		break;
	case 'set':
        if ($_POST['what'] == 'weap_snipers')
			{
			$s = (($_POST['set_1'] != '')?'1':'0');
			RequestToGame('scr_allow_enfieldsniper ' . $s);
			RequestToGame('scr_allow_kar98ksniper ' . $s);
			RequestToGame('scr_allow_nagantsniper ' . $s);
			$res = RequestToGame('scr_allow_springfield ' . $s, true);
			}
        elseif ($_POST['what'] == 'weap_shotgun')
			{
			$s = (($_POST['set_1'] != '')?'1':'0');
			$res = RequestToGame('scr_allow_shotgun ' . $s, true);
			}
        elseif ($_POST['what'] == 'weap_smoke_grenades')
			{
			$s = (($_POST['set_1'] != '')?'1':'0');
			$res = RequestToGame('scr_allow_smokegrenades ' . $s, true);
			}
		break;
	}

fclose($connect);
sleep(1);

header ('Location: index.php'.(($res!='')?'?lastcmd='.rawurlencode($cmd).'&colors='.$colors.'&lastres='.rawurlencode($res):''));
?>
