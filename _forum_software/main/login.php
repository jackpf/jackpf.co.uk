<?php
$db = new connection;
$alias_init = new alias;
$tpl = new template('login');

$status = (isset($_GET['status'])) ? $_GET['status'] : null;

class login
{
	public
		$status = 1,
		$message = array(),
		$alias_init;
	
	private
		$stat,
		$limit;
	
	public function __construct()
	{
		global $alias_init;
		
		$this->alias_init = $alias_init; //new alias;
		$this->stat       = new stat;
	}
	public function login()
	{
		global $config_init, $db, $alias, $usergroup, $crypt;
		$this->alias_init->usergroup = $usergroup;
		$this->alias_init->alias     = $alias;
		
		//force logout
		$db->query("UPDATE `{$db->tb->Alias_Stats}` SET `Online`='0' WHERE `Alias`='".val::post($this->stat->alias_init->alias)."';");
		
		//check if usergroup exists
		if($this->check_usergroup())
		{
			global $expire;
			
			header::setcookie($config_init->get_config('cookie_prefix').'Alias', $this->alias_init->alias, $expire);
			header::setcookie($config_init->get_config('cookie_prefix').'Alias(User)', $this->alias_init->usergroup, $expire);
			header::setcookie($config_init->get_config('cookie_prefix').'Alias(Crypt)', $crypt, $expire);
			
			//force login
			$db->query("UPDATE `{$db->tb->Alias_Stats}` SET `Online`='1' WHERE `Alias`='".val::post($this->alias_init->alias)."';");
		}
		else
		{
			$this->logout();
			$this->status = 3;
		}
	}
	public function logout()
	{
		global $config_init, $db;
		
		//force logout
		$db->query("UPDATE `{$db->tb->Alias_Stats}` SET `Online`='0' WHERE `Alias`='".val::post($this->stat->alias_init->alias)."';");
		//force stat update (login)
		#$this->stat->update();
		
		header::unsetcookie($config_init->get_config('cookie_prefix').'Alias');
		header::unsetcookie($config_init->get_config('cookie_prefix').'Alias(User)');
		header::unsetcookie($config_init->get_config('cookie_prefix').'Alias(Crypt)');
	}
	public function check_usergroup()
	{
		global $config_init, $db;
		
		if(array_key_exists($this->alias_init->usergroup, $this->alias_init->user))
		{
			//usergroup exists
			return true;
		}
		else if(reset(explode(':', preg_replace('/\s/', null, $this->alias_init->usergroup))) == 'Banned')
		{
			//user is banned
			global $tpl;
			
			//init message
			$ban = explode(':', $this->alias_init->usergroup); //extract ban elements
			array_shift(&$ban); //remove usergroup ("Banned")
			$ban = explode('(', implode(':', $ban)); //recompile ban elements
			$ban_expiry = array_shift(&$ban); //extract ban expiry
			$message = implode('(', $ban); //recompile message
			$message = ($message[strlen($message) - 1]) == ')' ? substr($message, 0, strlen($message) - 1) : $message; //remove trailing closing parenthesis from message
			
			array_push($this->message, 'You have been banned.', val::encode($message));
			
			$tpl->assign_vars(array(
			'login_message_type' => 'ban',
			'message'            => val::encode($message),
			'ban_expiry'         => $ban_expiry,
			'ban_expiry_date'    => val::unix($ban_expiry)
			));
			$this->message($tpl->compile('login_messages'));
			
			//check if ban has expired
			if($ban_expiry == 0)
			{
				return false;
			}
			else
			{
				//check if ban is still in effect
				if($ban_expiry <= time())
				{
					//ban has expired
					//update usergroup
					$this->alias_init->usergroup = 'User';
					$db->query("UPDATE `{$db->tb->Alias}` SET `User`='{$this->alias_init->usergroup}' WHERE `Alias`='{$this->alias_init->alias}';");
					
					return true;
				}
				else
				{
					//ban is still in effect
					return false;
				}
			}
		}
		else
		{
			//usergroup non-existent
			return false;
		}
	}
	private function message($message)
	{
		global $config_init;
		
		header::setcookie($config_init->get_config('cookie_prefix').'login', base64_encode($message), time() + 10);
	}
}

//init login
$login = new login;

if(!form::submitted() && !isset($status))
{
	//define login data
	if(!isset($_COOKIE[$config_init->get_config('cookie_prefix').'login_remember']))
	{
		$login_data = array('alias' => null, 'remember' => null);
	}
	else
	{
		$login_data = array('alias' => val::encode($_COOKIE[$config_init->get_config('cookie_prefix').'login_remember']), 'remember' => true);
	}
	$login_data['hide'] = (isset($_COOKIE[$config_init->get_config('cookie_prefix').'login_hide'])) ? true : false;
	
	//get max lengths for columns
	$login_data = array_merge($login_data, profile::clm_len($db->tb->Alias));
	
	$tpl->assign_vars(array(
	'login_data'         => $login_data,
	//for login_messages()
	'login_message_type' => 'login_index',
	'referer'            => (isset($_GET['referer'])) ? true : false,
	//assign message
	'login_message'      => $tpl->compile('login_messages')
	));
	$tpl->buffer($tpl->compile('login_index'));
}
else if(form::submitted())
{
	$alias = val::post($_POST['alias']);
	$password = val::post($_POST['password']);
	
	if($_POST['remember'] == 1)
	{
		//cookie expiry date for login cookies
		$expire = 2 * time();
		
		header::setcookie($config_init->get_config('cookie_prefix').'login_remember', $alias, $expire);
	}
	else
	{
		$expire = 0;
		
		header::unsetcookie($config_init->get_config('cookie_prefix').'login_remember');
	}
	if($_POST['hide'] == 1)
	{
		header::setcookie($config_init->get_config('cookie_prefix').'login_hide', $alias, 0);
	}
	else
	{
		header::unsetcookie($config_init->get_config('cookie_prefix').'login_hide');
	}
	
	$sql = $db->query("SELECT * FROM `{$db->tb->Alias}` WHERE `Alias`='$alias' and `Password`='$password';");
	
	if($db->count_rows($sql) == 1)
	{
		$fetch = $db->fetch_array($sql);
		
		$usergroup = $fetch['User'];
		$id = $fetch['ID'];
		$alias = $fetch['Alias'];
		$password = $fetch['Password'];
		$crypt = sha1($id);
		
		//check for hidden status (define userlevel)
		$login->alias_init->userlevel = (array_key_exists($usergroup, $login->alias_init->user)) ? $login->alias_init->user[$usergroup]['Userlevel'] : 0;
		if($_POST['hide'] == 1 && !stat::hidden(true))
		{
			$login->status = 4;
		}
		
		$login->logout();
		$login->login();
	}
	else
	{
		$login->logout();
		$login->status = 2;
	}
	
	header::location(reset(explode('&status', $_SERVER['HTTP_REFERER'])).'&status='.$login->status);
}
else if(is_numeric($status))
{
	//status legend
	$login_status_legend = array(
	'success' => array(1, 4),
	'fail'    => array(2, 3)
	);
	
	//assign some login_message() vars
	$tpl->assign_vars(array(
	'login_message_type'  => 'login_process',
	'referer'             => (isset($_GET['referer'])) ? true : false
	));
	
	if(in_array($status, $login_status_legend['success']) && isset($login->alias_init->alias))
	{
		//successful login
		$tpl->assign_vars(array(
		'login'           => true,
		'warning_message' => ($status == 4) ? true : false
		));
	}
	else if(in_array($status, $login_status_legend['success']) && !isset($login->alias_init->alias))
	{
		//erroneous login
		$tpl->assign_vars(array(
		'login'      => false,
		'error_type' => 'error'
		));
	}
	else if(in_array($status, $login_status_legend['fail']))
	{
		//failed login
		$tpl->assign_vars(array(
		'login'      => false,
		'error_type' => 'failure'
		));
		
		if($status == 2)
		{
			//unknown credentials
			$tpl->assign_vars(array(
			'failure_type' => 'credentials'
			));
		}
		else if($status == 3)
		{
			//inactive account
			$tpl->assign_vars(array(
			'failure_type' => 'inactive'
			));
			
			//login message
			if(isset($_COOKIE[$config_init->get_config('cookie_prefix').'login']))
			{
				$tpl->assign_vars(array(
				'login_message' => base64_decode($_COOKIE[$config_init->get_config('cookie_prefix').'login'])
				));
			}
		}
	}
	
	$tpl->buffer($tpl->compile('login_messages'));
}
else if($status == 'logout')
{
	$login->logout();
	header::location(($_SERVER['HTTP_REFERER'] != null) ? $_SERVER['HTTP_REFERER'] : $_SERVER['PHP_SELF']);
}

$tpl->assign_vars(array(
'login_main' => $tpl->buffer()
));
return $tpl->compile('login_main');
?>