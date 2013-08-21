<?php
include_once $_SERVER['DOCUMENT_ROOT'].'/config/lib.php';
include $_SERVER['DOCUMENT_ROOT'].'/bin/server/resources/RCon.class.php';

secure::secure();
secure::restrict(alias::user(alias::USR_ADMINISTRATOR));

$server = array(
'rcon_host' => $config_init->get_config('gameserver.address'),
'rcon_fork' => val::array_explode(',', $config_init->get_config('gameserver.forks')),
'rcon_user' => $config_init->get_config('gameserver.auth.username'),
'rcon_pass' => $config_init->get_config('gameserver.auth.password')
);

switch((isset($_GET['status'])) ? $_GET['status'] : 'connect')
{
	default:
		print '<script src="../../templates/js/ajax.js"></script>
		<form id="command" onsubmit="AJAX(\'command\', \'cmd_display\', \''.val::encode($_SERVER['PHP_SELF']).'?status=cmd\'); return false;">
			Console: <input type="text" name="com" style="width: 235px;" /><br />
			<input type="submit" value="Send" />
			Fork: <select name="fork">
				<option value="">'.$server['rcon_host'].'</option>';
				foreach($server['rcon_fork'] as $index => $fork):
					print '<option value="'.$index.'">'.$fork.'</option>';
				endforeach;
			print '</select>
		</form>
		<div id="cmd_display" style="width: 350px; border: 1px solid black; padding: 2px;">
			rcon_password '.str_repeat('*', strlen($server['rcon_pass'])).' &gt;&gt;
		</div>';
	break;
	case 'cmd':
		try
		{
			$rc = new RCon(($_POST['fork'] == null) ? $server['rcon_host'] : $server['rcon_fork'][$_POST['fork']], $server['rcon_pass']);
			print nl2br(val::encode($_POST['com']).' &gt;&gt; '.val::encode($rc->rconCommand($_POST['com']))."\n");
		}
		catch(Exception $e)
		{
			print $e->getMessage();
		}
	break;
}
?>