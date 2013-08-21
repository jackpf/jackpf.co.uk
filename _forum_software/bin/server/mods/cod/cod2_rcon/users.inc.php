<?php
// separate names and passwords with a space
// all names/passwords are case sensitive
// accounts can have more than one password, just add another line with same name

include $_SERVER['DOCUMENT_ROOT'].'/config/lib.php';

$list_of_users[] = $config_init->get_config('site_owner').' '.$config_init->get_config('gameserver.auth.password');

?>
