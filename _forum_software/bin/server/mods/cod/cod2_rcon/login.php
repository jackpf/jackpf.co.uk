<?php
session_name('php_cod2_rcon'); session_set_cookie_params(0,'/','',false);
session_start(); header('P3P: CP="NOI ADM DEV PSAi COM NAV OUR OTRo STP IND DEM"');
error_reporting(E_ALL & ~E_NOTICE);

include 'language.inc.php';

echo '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=windows-1250">
<meta http-equiv="Cache-Control" content="no-store,max-age=-1">
<title>Login</title>
<link rel="stylesheet" type="text/css" href="css.css">
<base target="_self">
</head><body class="padded">
<h1>Login</h1>';

if ($_GET['logoff'] == '1')
	{$_SESSION['hasadminrights'] = 0;
	session_destroy();}

$user = $_POST['user'];

if ($user != '')
	{
	$_SESSION['hasadminrights'] = 0;
	$pass = $_POST['pass'];
	require 'users.inc.php';
	foreach ($list_of_users as $cur)
	    {
		$cur = explode(' ',$cur);
		if (($user == $cur[0]) && ($pass == $cur[1]))
		    {
			$_SESSION['user'] = $user;
			$_SESSION['hasadminrights'] = 1;
			break;
			}
		}
	}

if ($_SESSION['hasadminrights'] > 0)
	{
	function InsertLink($name, $link)
		{
		echo '<a href="'.$link.'">'.$name.'</a><br>';
		}
	echo '<h2>'.$lang['login_logged_as'].': &nbsp; &nbsp; '.$_SESSION['user']
		.' &nbsp; | &nbsp; <a href="'.$_SERVER[PHP_SELF].'?logoff=1">['.$lang['login_logout'].']</a>'
		.'</h2><br>';
		InsertLink('COD2 RCon','index.php');
		} else {
	
	echo '
<h2>'.$lang['login_please_enter'].'.</h2>
<form action="'.$_SERVER['PHP_SELF'].'" method="POST">
<table><tr>
<td width="60">'.$lang['login_name'].':</td>
<td><input class=query type="text" name="user" size="25"></td>
<td width="40">&nbsp;</td>
</tr><tr>
<td>'.$lang['login_password'].':</td>
<td><input class=query type="password" name="pass" size="25"></td>
<td width="40">&nbsp;</td>
</tr><tr>
<td colspan="3" align="right"><input class="button" type="submit" value="'.$lang['confirm'].'"></td>
</tr></table></form>
';
	}

?>
</body>
</html>
