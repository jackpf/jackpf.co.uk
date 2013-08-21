<?
require ('validate.inc.php');
include $_SERVER['DOCUMENT_ROOT'].'/config/lib.php';

$server_ip = reset(explode(':', $config_init->get_config('gameserver.address')));
$server_port = end(explode(':', $config_init->get_config('gameserver.address')));
$server_rconpass = $config_init->get_config('gameserver.auth.password');

$admin_name = $_SESSION['user'];	// used as a prefix of messages by you
$refresh_rate = false;				// enter a number of seconds to automatically refresh the window in or false to disable

?>
