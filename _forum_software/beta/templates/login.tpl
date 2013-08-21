<?function login_main(stdclass $vars, stdclass $globals)
{?>
<div class="title">Login</div>

<?=$vars->login_main?>
<?}?>
<?function login_index(stdclass $vars, stdclass $globals)
{?>
<div class="f_header2">
	<a style="font-style: italic;" href="#register" onclick="fade('register');">Register &raquo;</a>
	<div id="register" class="box" style="display: none;">
		<a href="?action=register">Register</a>
		<br />
		<a href="<?=$globals->_SERVER['PHP_SELF']?>?action=register&amp;status=help&amp;help=details">Request Account Details</a>
		<br />
		<a href="<?=$globals->_SERVER['PHP_SELF']?>?action=register&amp;status=help&amp;help=authentication">Request Authentication</a>
	</div>
</div>

<form id="login" action="<?=$globals->_SERVER['REQUEST_URI']?>" method="post" enctype="application/x-www-form-urlencoded"><div>
	Alias:<br /><input type="text" name="alias" <?=(!empty($vars->login_data['alias'])) ? 'value="'.$vars->login_data['alias'].'"' : null?> maxlength="<?=$vars->login_data['clm_Alias']?>" /><br /><br />
	Password:<br /><input type="password" name="password" maxlength="<?=$vars->login_data['clm_Password']?>" /><br /><br />
	<dl class="justify">
		<dt><label for="remember">Remember</label></dt><dd><input type="checkbox" name="remember" id="remember" value="1" <?=($vars->login_data['remember']) ? 'checked="true"' : null?> /></dd>
		<dt><label for="hide">Hide</label></dt><dd><input type="checkbox" name="hide" id="hide" value="1" <?=($vars->login_data['hide']) ? 'checked="true"' : null?> /></dd>
	</dl>
	<input type="submit" class="post" value="Login" />
</div></form>

<?if($vars->login_message):
	$vars->login_message;
endif?>
<?}?>
<?
//...
?>
<?function login_messages(stdclass $vars, stdclass $globals)
{?>
<?switch($vars->login_message_type):
	case 'login_index':
		$message = ($vars->referer) ? '<br /><br /><span class="control">It\'s imperative that you are logged in...</span>' : null;
	break;
	case 'login_process':
		$message = '<ul>';
			if($vars->login):
				$message .= '<li style="color: #00AA00;">Login successful.</li>';
				if($vars->warning_message):
					$message .= '<li style="color: orange;">You do not possess a sufficient userlevel to hide yourself.</li>';
				endif;
				$message .= ($vars->referer) ? '<li><a class="control" href="'.val::encode(urldecode($_GET['referer'])).'">Continue</a></li>' : '<li><a class="control" href="'.$globals->_SERVER['PHP_SELF'].'">Index</a></li>';
			else:
				switch($vars->error_type):
					case 'error':
						$message .= '<li style="color: red;">An error has occurred.</li>';
					break;
					case 'failure':
						$message .= '<li style="color: red;">
							Login failed: ';
							switch($vars->failure_type):
								case 'credentials':
									$message .= 'unknown credentials.';
								break;
								case 'inactive':
									$message .= 'your account is currently disabled.';
									if(!empty($vars->login_message)):
										$message .= $vars->login_message;
									endif;
								break;
							endswitch;
						$message .= '</li>';
					break;
				endswitch;
				$message .= '<li><a class="control" href="'.$globals->_SERVER['HTTP_REFERER'].'">Back</a></li>';
			endif;
		$message .= '</ul>';
	break;
	case 'ban':
		$message = '<br /><span style="color: red;">
			You have been banned.<br />';
			if(!empty($vars->message)):
				$message .= $vars->message.'<br />';
			endif;
			if($vars->ban_expiry <= 0):
				$message .= 'Your ban is not set to expire.';
			else:
				$message .= 'Your ban is set to expire on '.$vars->ban_expiry_date.'.';
			endif;
		$message .= '</span>';
	break;
endswitch;

print $message?>
<?}?>