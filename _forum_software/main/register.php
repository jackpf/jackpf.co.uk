<?php
$db = new connection;
$alias_init = new alias;
$tpl = new template('register');

//mail
if(form::submitted())
{
	include 'bin/mail.class.php';
	$mail = new mail($config_init->get_config('site_email'), 'localhost');
}

$status = (isset($_GET['status'])) ? $_GET['status'] : 'register';

if($status == 'register')
{
	//get max lengths for columns
	$register_data = profile::clm_len($db->tb->Alias);
	
	//captcha
	include 'bin/captcha.class.php';
	$captcha = new captcha;
	
	if(!form::submitted())
	{
		$tpl->assign_vars(array(
		'register_data'    => $register_data,
		'terms'            => $config_init->get_config('site_terms'),
		'terms_footer'     => $config_init->get_config('site_domain'),
		'captcha'          => $captcha->send(),
		'captcha_filename' => captcha::CAPTCHA_FILENAME
		));
		$tpl->buffer($tpl->compile('register_index'));
	}
	else if(form::submitted())
	{
		$name     = val::post(trim($_POST['name']));
		$email    = val::post(val::email_val(trim($_POST['email'])));
		$alias    = val::post(val::alias_val(trim($_POST['alias'])));
		$password = val::post($_POST['password']);
		$terms    = $_POST['terms'];
		
		//check alias & email does not already exist
		$register_data['Alias'] = $db->query("SELECT null FROM `{$db->tb->Alias}` WHERE `Alias`='$alias';");
		$register_data['Email'] = $db->query("SELECT null FROM `{$db->tb->Alias}` WHERE `Email`='$email';");
		
		$errors = array();
		
		if($name == null || strlen(stripslashes($name)) > $register_data['clm_Name'])
			$errors[] = 'invalid name';
		if($email == null || strlen(stripslashes($email)) > $register_data['clm_Email'])
			$errors[] = 'invalid email address';
		if($alias == null || strlen(stripslashes($alias)) > $register_data['clm_Alias'])
			$errors[] = 'invalid alias';
		if($password == null || strlen(stripslashes($password)) > $register_data['clm_Password'])
			$errors[] = 'invalid password';
		if($terms != 1)
			$errors[] = 'terms were not checked';
		if($db->count_rows($register_data['Alias']) > 0)
			$errors[] = 'this alias already exists';
		if($db->count_rows($register_data['Email']) > 0)
			$errors[] = 'this email address already exists';
		if(!$captcha->validate($_POST['captcha']))
			$errors[] = 'captcha failed';
		
		if(count($errors) > 0)
			trigger_error('Register failed: '.implode(', ', $errors).'.');
		
		$db->query("INSERT INTO `{$db->tb->Alias}` (`ID`, `Name`, `Email`, `Alias`, `Password`, `User`, `Profile`, `Picture`, `Signature`, `Status`, `Unix`) VALUES (null, '$name', '$email', '$alias', '$password', '', '', '', '', '0.0.1.1.1.0', UNIX_TIMESTAMP());");
		
		$subject = 'Profile Authentication';
		$message = "Follow this link to authenticate your profile.\nhttp://".$config_init->get_config('site_domain')."{$_SERVER['PHP_SELF']}?action=register&status=authentication&authid=".sha1($email);
		$mail->send($email, $subject, $message);
		
		$tpl->assign_vars(array(
		'email' => end(explode('@', $email))
		));
		$tpl->buffer($tpl->compile('register_success'));
	}
}
else if($status == 'authentication')
{
	$crypt = val::post($_GET['authid']);
	
	$sql = $db->query("SELECT `User` FROM `{$db->tb->Alias}` WHERE SHA1(`Email`)='$crypt';");
	
	if($db->count_rows($sql) == 0)
	{
		trigger_error('This auth ID does not exist.');
	}
	
	$fetch = $db->fetch_array($sql);
	
	if($fetch['User'] != null)
	{
		trigger_error('This profile has already been authenticated.');
	}
	
	$db->query("UPDATE `{$db->tb->Alias}` SET `User`='User' WHERE SHA1(`Email`)='$crypt';");
	
	$tpl->buffer($tpl->compile('register_authenticate'));
}
else if($status == 'help')
{
	$help = (isset($_GET['help'])) ? $_GET['help'] : null;
	
	switch($help)
	{
		case 'details':
			if(!form::submitted())
			{
				$tpl->buffer($tpl->compile('register_help_details'));
			}
			else if(form::submitted())
			{
				$email = val::post($_POST['email']);
				
				$sql = $db->query("SELECT * FROM `{$db->tb->Alias}` WHERE `Email`='$email';");
				
				if($db->count_rows($sql) == 0)
				{
					trigger_error('This email address does not exist.');
				}
				
				$fetch = $db->fetch_array($sql);
				
				$email = $fetch['Email'];
				$alias = $fetch['Alias'];
				$password = $fetch['Password'];
				
				$subject = 'Requested Details';
				$message = "Requested details are as follows.\nAlias: $alias\nPassword: $password";
				$mail->send($email, $subject, $message);
				
				$tpl->assign_vars(array(
				'email' => end(explode('@', $email))
				));
				$tpl->buffer($tpl->compile('register_help_details'));
			}
		break;
		case 'authentication':
			if(!form::submitted())
			{
				$tpl->buffer($tpl->compile('register_help_authentication'));
			}
			else if(form::submitted())
			{
				$email = val::post($_POST['email']);
				
				$sql = $db->query("SELECT * FROM `{$db->tb->Alias}` WHERE `Email`='$email';");
				
				if($db->count_rows($sql) == 0)
				{
					trigger_error('This email address does not exist.');
				}
				
				$fetch = $db->fetch_array($sql);
				
				$email = $fetch['Email'];
				$crypt = sha1($email);
				$user = $fetch['User'];
				
				if($user != null)
				{
					trigger_error('This profile is already authentic.');
				}
				
				$subject = 'Profile Authentication';
				$message = "Follow this link to authenticate your profile.\nhttp://".$config_init->get_config('site_domain')."{$_SERVER['PHP_SELF']}?action=register&status=authentication&authid=$crypt";
				$mail->send($email, $subject, $message);
				
				$tpl->assign_vars(array(
				'email' => end(explode('@', $email))
				));
				$tpl->buffer($tpl->compile('register_help_authentication'));
			}
		break;
	}
}
else if($status == 'unregister')
{
	secure::secure();
	
	if(!form::submitted())
	{
		$tpl->buffer($tpl->compile('register_unregister'));
	}
	else if(form::submitted())
	{
		$alias = '/'.$alias_init->alias.'/';
		
		//delete user
		$db->query("DELETE FROM `{$db->tb->Alias}` WHERE `Alias`='".val::post($alias_init->alias)."';");
		
		//delete & negate posts & messages
		$db->query("DELETE FROM `{$db->tb->Message}` WHERE `Alias`='".val::post($alias_init->alias)."';");
		$db->query("UPDATE `{$db->tb->Message}` `M`, `{$db->tb->Forum}` `F` SET `M`.`Author`='".val::post($alias)."', `F`.`Author`='".val::post($alias)."' WHERE `F`.`Author`='".val::post($alias_init->alias)."' AND `M`.`Author`='".val::post($alias_init->alias)."';");
		
		header::location($_SERVER['PHP_SELF'].'?action=login&status=logout');
	}
}

$tpl->assign_vars(array(
'register_main' => $tpl->buffer()
));
return $tpl->compile('register_main');
?>