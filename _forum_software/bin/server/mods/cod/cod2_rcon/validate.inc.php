<?php
session_name('php_cod2_rcon'); session_set_cookie_params(0,'/','',false);
session_start(); header('P3P: CP="NOI ADM DEV PSAi COM NAV OUR OTRo STP IND DEM"');

if ($_SESSION['hasadminrights'] < 1)
	{header ('Location: login.php');}

error_reporting(E_ALL & ~E_NOTICE); // leave this as it is
?>
