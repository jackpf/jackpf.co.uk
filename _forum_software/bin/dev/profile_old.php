<?php
$db = new connection;
$alias_init = new alias;
$tpl = new template('profile');

$status  = (isset($_GET['status'])) ? $_GET['status'] : 'profile_self';
$profile = (isset($_GET['profile'])) ? $_GET['profile'] : 'index';

class profile_init
{
	//class container for profile vars
	public
		$profile_self,
		$alias_init, $alias_init2,
		$uri, $uri2, $uri3;
	
	public function __construct()
	{
		global $alias_init, $status;
		
		$this->alias_init  = new stdclass;
		$this->alias_init2 =  clone $this->alias_init;
		
		$this->alias_init->alias     = $alias_init->alias;
		$this->alias_init->userlevel = $alias_init->userlevel;
		$this->alias_init->usergroup = $alias_init->usergroup;
		
		if($status == 'profile_self')
		{
			$this->profile_self = true;
			
			//alias is alias2
			$this->alias_init2->alias     = $this->alias_init->alias;
			$this->alias_init2->userlevel = $this->alias_init->userlevel;
			$this->alias_init2->usergroup = $this->alias_init->usergroup;
			
			//alias of profile::profile_link()
			$this->uri  = val::encode($_SERVER['PHP_SELF'].'?action=profile&status=profile_self');
			$this->uri2 = val::encode($_SERVER['PHP_SELF'].'?action=profile&status=profile&alias='.$alias_init->alias);
		}
		else if($status == 'profile')
		{
			$this->profile_self = false;
			
			//get profile alias
			$this->alias_init2->alias     = $_GET['alias'];
			$this->alias_init2->userlevel = $alias_init->alias_mod($this->alias_init2->alias, 'lite:Userlevel');
			$this->alias_init2->usergroup = $alias_init->alias_mod($this->alias_init2->alias, 'lite:Name');
			
			//alias of profile::profile_link()
			$this->uri  = val::encode($_SERVER['PHP_SELF'].'?action=profile&status=profile&alias='.$this->alias_init2->alias);
			$this->uri2 = val::encode($_SERVER['PHP_SELF'].'?action=profile&status=profile_self');
		}
		
		//alias of profile::profile_link()
		$this->uri3 = val::encode($_SERVER['PHP_SELF'].'?action=profile&status=profile');
	}
}
function profile_unix($unix)
{
	if($unix <= 0)
	{
		return array('unix' => 0, 'period' => '');
	}
	else if($unix / (60 * 60 * 24) >= 1)
	{
		$unix = floor($unix / (60 * 60 * 24));
		return array('unix' => $unix, 'period' => 'day'.(($unix == 1) ? null : 's'));
	}
	else if($unix / (60 * 60) >= 1)
	{
		$unix = floor($unix / (60 * 60));
		return array('unix' => $unix, 'period' => 'hour'.(($unix == 1) ? null : 's'));
	}
	else
	{
		return array('unix' => '< 1', 'period' => 'hour');
	}
}
function message_existence($type)
{
	//global sql used
	global $db, $sql;
	
	if($db->count_rows($sql) == 0)
	{
		trigger_error(sprintf('This %s does not exist.', $type));
	}
}
function email_array($query)
{
	global $db, $profile_init, $tpl;
	
	$id = array_map(create_function('$id', 'return (int) $id;'), val::post($_POST['email'])); #function($id){return (int) $id;}
	
	if(count($id) == 0 || !is_array($id))
	{
		trigger_error('You have not selected any emails.');
	}
	
	if(form::submitted() && !isset($_POST['_submit']))
	{
		$tpl->assign_vars(array(
		'emails' => $id
		));
		$tpl->buffer($tpl->compile('profile_email_array'));
	}
	else if(form::submitted() && isset($_POST['_submit']))
	{
		$_query = forum::search2("`ID`", $id); //_query must be after a where clause since it's prefixed with an "AND" clause
		
		//restrictions
		$sql = $db->query("SELECT `Alias` FROM `{$db->tb->Message}` WHERE `Type` IN('email', 'email2') $_query;");
		while($fetch = $db->fetch_array($sql))
		{
			if($profile_init->alias_init->alias_init->alias != $fetch['Alias'])
			{
				secure::restrict(alias::user(alias::USR_MODERRATOR));
			}
		}
		
		$db->query(sprintf($query, $_query));
		
		header::location($profile_init->uri.'&profile=email');
	}
}

//init profile
$profile_init = new profile_init;

$sql = $db->query("SELECT * FROM `{$db->tb->Alias}` WHERE `Alias`='".val::post($profile_init->alias_init2->alias)."';");
$fetch = $db->fetch_array($sql);

$profile_data = array(
'name'          => val::encode($fetch['Name']),
'email'         => val::encode($fetch['Email']),
'password'      => val::encode($fetch['Password']),
'p_status'      => profile::profile_status($fetch['Status']),
'p_profile'     => $fetch['Profile'],
'unix'          => $fetch['Unix'],
'register_date' => val::unix($fetch['Unix'], 'compressed'),
'picture'       => val::encode($fetch['Picture']),
'music'         => val::encode($fetch['Music']),
'signature'     => val::encode($fetch['Signature']),
'forumposts'    => forum::posts($profile_init->alias_init2->alias),
'online'        => stat::online($profile_init->alias_init2->alias),
'usergroup'     => (isset($alias_init->user[$profile_init->alias_init2->usergroup]['Name'])) ? array('name' => val::encode($alias_init->user[$profile_init->alias_init2->usergroup]['Name']), 'color' => val::encode($alias_init->user[$profile_init->alias_init2->usergroup]['Mod'])) : (($profile_init->alias_init2user->usergroup === false) ? 'hidden' : 'none')
);

//define/format some vars...
$profile_data['forumposts']['post_count_per_day']      = val::number_format($profile_data['forumposts']['post_count'] / ceil((time() - $profile_data['unix']) / (60 * 60 * 24)));
$profile_data['forumposts']['post_count']              = val::number_format($profile_data['forumposts']['post_count']);
$profile_data['forumposts']['active_forum']['subject'] = val::encode($profile_data['forumposts']['active_forum']['Subject']);

if(!$profile_data['online'] && !$profile_init->profile_self)
{
	$sql2 = $db->query("SELECT `Unix` FROM `{$db->tb->Alias_Stats}` WHERE `Alias`='".val::post($profile_init->alias_init2->alias)."';");
	$fetch2 = $db->fetch_array($sql2);
	
	$profile_data['last_online'] = profile_unix(time() - $fetch2['Unix']);
}

//get user's time online
$sql2 = $db->query("SELECT `Unix_Total` FROM `{$db->tb->Alias_Stats}` WHERE `Alias`='".val::post($profile_init->alias_init2->alias)."';");
$fetch2 = $db->fetch_array($sql2);

$profile_data['online_time'] = profile_unix($fetch2['Unix_Total']);

//restrictions/security/profile existence
if($profile_init->profile_self)
{
	//secure profile_self
	secure::secure();
}
if($profile_data['p_status']['profile'] == 1 && !$profile_init->profile_self && !isset($profile_init->alias_init->alias))
{
	//secure hidden profiles
	secure::secure();
}
if($db->count_rows($sql) == 0)
{
	//check profile existence
	trigger_error('This profile does not exist.');
}

//fetch unread email/message stats
if($profile_init->profile_self)
{
	$sql = $db->query("SELECT `Type`, COUNT(*) AS `Count` FROM `{$db->tb->Message}` WHERE (`Type`='message' OR `Type`='email') AND `Status`='0' AND `Alias`='".val::post($profile_init->alias_init2->alias)."' GROUP BY `Type` WITH ROLLUP;");
	
	$profile_data['messages_unread'] = array('message' => 0, 'email' => 0);
	
	while($fetch = $db->fetch_array($sql))
	{
		$profile_data['messages_unread'][$fetch['Type']] += $fetch['Count'];
	}
	
	//hack to update emails/messages upon opening
	$profile_data['messages_unread']['message'] = ($profile != 'message') ? $profile_data['messages_unread']['message'] : 0;
	$profile_data['messages_unread']['email']   = (!isset($_GET['email'])) ? $profile_data['messages_unread']['email'] : $profile_data['messages_unread']['email'] - 1;
}

$tpl->assign_vars(array(
'profile_init'  => $profile_init,
'profile_data'  => $profile_data,
'administrator' => ($profile_init->alias_init->userlevel >= alias::user(alias::USR_ADMINISTRATOR)) ? true : false,
'moderator'     => ($profile_init->alias_init->userlevel >= alias::user(alias::USR_MODERRATOR)) ? true : false
));

if($profile == 'index')
{
	//developer
	if($profile_data['p_status']['developer'] == 1)
	{
		//parse codex
		$profile_data['p_profile'] = ($profile_init->alias_init->userlevel2 >= alias::user(alias::USR_ADMINISTRATOR)) ? code::parse_code_php($profile_data['p_profile']) : (($profile_init->alias_init->userlevel2 >= alias::user(alias::USR_MODERRATOR)) ? $profile_data['p_profile'] : val::encode($profile_data['p_profile']));
	}
	else
	{
		//parse code
		$profile_data['p_profile'] = parse::parse($profile_data['p_profile'], parse::options_parse_code | parse::options_parse_code_ws | parse::options_parse_smiley);
	}
	
	$tpl->assign_vars(array(
	'profile_data' => $profile_data
	));
	$tpl->buffer($tpl->compile('profile_index'));
}
else if($profile == 'profile' && $profile_init->profile_self)
{
	$account = (isset($_GET['account'])) ? $_GET['account'] : 'profile';
	
	$tpl->assign_vars(array(
	'profile_type' => $account
	));
	
	//encode p_profile
	$profile_data['p_profile'] = val::encode($profile_data['p_profile']);
	
	if($account == 'profile')
	{
		$tpl->assign_vars(array(
		'profile_data' => $profile_data
		));
	}
	else if($account == 'account')
	{
		//get max lengths for columns
		$account_data = profile::clm_len($db->tb->Alias);
		
		$tpl->assign_vars(array(
		'account_data' => $account_data
		));
	}
	else if($account == 'cpanel')
	{
		//restrictions
		secure::restrict(alias::user(alias::USR_MODERRATOR));
	}
	
	$tpl->buffer($tpl->compile('profile_profile'));
}
else if($profile == 'update' && $profile_init->profile_self)
{
	$update = (isset($_GET['update'])) ? $_GET['update'] : null;
	
	if($update == 'account')
	{
		//check form has been submitted
		if(!form::submitted())
		{
			trigger_error('Nothing was posted.');
		}
		
		$name     = val::post($_POST['name']);
		$email    = val::post(val::email_val($_POST['email']));
		$alias   = val::post(val::alias_val($_POST['alias']));
		$password = val::post($_POST['password']);
		
		//get max lengths for columns
		$account_data = profile::clm_len($db->tb->Alias);
		
		if(trim($name) == null || trim($email) == null || trim($alias) == null || trim($password) == null || strlen($name) > $account_data['clm_Name'] || strlen($email) > $account_data['clm_Email'] || strlen($alias) > $account_data['clm_Alias'] || strlen($password) > $account_data['clm_Password'])
		{
			trigger_error('You cannot post empty details.');
		}
		
		//get email for email-edit check before update
		$sql = $db->query("SELECT `Email` FROM `{$db->tb->Alias}` WHERE `Alias`='".val::post($profile_init->alias_init2->alias)."';");
		$fetch = $db->fetch_array($sql);
		
		$email2 = $fetch['Email'];
		
		$query = ($profile_init->alias_init->userlevel > alias::user(alias::USR_MODERRATOR)) ? ", `Email`='$email'"/*, .`Alias`='$alias2/*$alias*\/' executed with checks*/."" : null;
		$db->query("UPDATE `{$db->tb->Alias}` SET `Name`='$name', `Password`='$password' $query WHERE `Alias`='".val::post($profile_init->alias_init2->alias)."';");
		
		//check if email has been edited (for users with userlevels < Techie's)
		if($email != $email2 && $profile_init->alias_init->userlevel < alias::user(alias::USR_MODERRATOR))
		{
			//check email does not exist
			$sql = $db->query("SELECT null FROM `{$db->tb->Alias}` WHERE `Email`='$email';");
			if($db->count_rows($sql) > 0)
			{
				trigger_error('This email address already exists.');
			}
			
			//unauth user
			$db->query("UPDATE `{$db->tb->Alias}` SET `Email`='$email', `User`='' WHERE `Alias`='".val::post($profile_init->alias_init2->alias)."';");
			
			trigger_error('You have changed your email address. In order to reauthenticate yourself, you must request a new authentication email on the login page.');
		}
		
		//check if alias has been edited
		if($profile_init->alias_init->alias != $alias && $profile_init->alias_init->userlevel >= alias::user(alias::USR_ADMINISTRATOR))
		{
			//check alias does not exist
			$sql = $db->query("SELECT null FROM `{$db->tb->Alias}` WHERE `Alias`='$alias';");
			if($db->count_rows($sql) > 0)
			{
				trigger_error('This alias already exists.');
			}
			
			//update alias
			$db->query("UPDATE IGNORE `{$db->tb->Alias}` `A`, `{$db->tb->Alias_Stats}` `AS` SET `A`.`Alias`='$alias', `AS`.`Alias`='$alias' WHERE `A`.`Alias`='".val::post($profile_init->alias_init->alias)."' AND `AS`.`Alias`='".val::post($profile_init->alias_init->alias)."';");
			//update posts' & messages' author
			$db->query("UPDATE `{$db->tb->Message}` `M`, `{$db->tb->Forum}` `F` SET `M`.`Author`='$alias', `F`.`Author`='$alias' WHERE `M`.`Author`='".val::post($profile_init->alias_init->alias)."' AND `F`.`Author`='".val::post($profile_init->alias_init->alias)."';");
			//update posts' & messages' edit
			$db->query("UPDATE `{$db->tb->Message}` `M`, `{$db->tb->Forum}` `F` SET `M`.`Edit`=IF(SUBSTRING_INDEX(`M`.`Edit`, ';', -1)='".val::post($profile_init->alias_init->alias)."', CONCAT_WS(';', SUBSTRING_INDEX(`M`.`Edit`, ';', 1), '$alias'), `M`.`Edit`), `F`.`Edit`=IF(SUBSTRING_INDEX(`F`.`Edit`, ';', -1)='".val::post($profile_init->alias_init->alias)."', CONCAT_WS(';', SUBSTRING_INDEX(`F`.`Edit`, ';', 1), '$alias'), `F`.`Edit`) WHERE SUBSTRING_INDEX(`M`.`Edit`, ';', -1)='".val::post($profile_init->alias_init->alias)."' OR SUBSTRING_INDEX(`F`.`Edit`, ';', -1)='".val::post($profile_init->alias_init->alias)."';");
			//update messages' alias pointer
			$db->query("UPDATE `{$db->tb->Message}` SET `Alias`='$alias' WHERE `Alias`='".val::post($profile_init->alias_init->alias)."';");
		}
	}
	else if($update == 'profile')
	{
		//check form has been submitted
		if(!form::submitted())
		{
			trigger_error('Nothing was posted.');
		}
		
		//AJAX
		val::AJAX_decode();
		
		$p_profile  = val::post($_POST['p_profile']);
		$picture    = val::post($_POST['picture']);
		$music      = val::post($_POST['music']);
		
		//permissions
		$p_status2 = array(
		'status'    => (int) $_POST['profile'],
		'developer' => (int) $_POST['developer'],
		'details'   => (int) $_POST['details'],
		'message'   => (int) $_POST['message'],
		'email'     => (int) $_POST['email'],
		'alias_mod' => (int) $profile_data['p_status']['alias_mod']
		);
		
		$profile_status = profile::compile_profile_status($p_status2);
		
		//signature
		$signature = val::post($_POST['signature']);
		
		$db->query("UPDATE `{$db->tb->Alias}` SET `Profile`='$p_profile', `Picture`='$picture', `Music`='$music', `Signature`='$signature', `Status`='$profile_status' WHERE `Alias`='".val::post($profile_init->alias_init2->alias)."';");
	}
	
	header::location(reset(explode('&update', $_SERVER['HTTP_REFERER'])).'&update=1');
}
else if($profile == 'message')
{
	if(!preg_match('/message\_/i', $_SERVER['QUERY_STRING']))
	{
		$page = new page;
		$page->display = 'numeric';
		$page->default = 'last';
		$sql = $page->page("SELECT * FROM `{$db->tb->Message}` WHERE `Alias`='".val::post($profile_init->alias_init2->alias)."' AND `Type`='message' ORDER BY `ID` ASC;", 10, &$pagination);
		
		$messages = array();
		
		while($fetch = $db->fetch_array($sql))
		{
			$author = $fetch['Author'];
			$unix = val::unix($fetch['Unix']);
			$alias = $fetch['Alias'];
			$options = $fetch['Options'];
			$message = parse::parse($fetch['Message'], $options);
			$id = $fetch['ID'];
			$status = $fetch['Status'];
			
			//set message status as read
			if($status == 0 && $profile_init->profile_self)
			{
				$db->query("UPDATE `{$db->tb->Message}` SET `Status`='1' WHERE `ID`='$id' AND `Status`='0' AND `Type`='message';");
			}
			
			$permission = array(
			'alias'  => ($profile_init->alias_init->alias == $alias && $profile_init->profile_self) ? true : false,
			'alias2' => ($profile_init->profile_self) ? true : false,
			'author' => ($profile_init->alias_init->alias == $author || $profile_init->alias_init->userlevel >= alias::user(alias::USR_MODERRATOR)) ? true : false
			);
			
			$messages[] = array(
			'id'         => $id,
			'author'     => array('link' => template::profile_link(val::encode($author), 'style="color: '.$alias_init->alias_mod($author, 'lite:Mod').';"').$alias_init->alias_mod($author, 'full'), 'author' => val::encode($author)),
			'message'    => $message,
			'date'       => $unix,
			'permission' => $permission
			);
		}
		
		$tpl->assign_vars(array(
		'messages'   => $messages,
		'pagination' => $pagination
		));
		$tpl->buffer($tpl->compile('profile_message'));
	}
	else if(isset($_GET['message_send']) && !$profile_init->profile_self)
	{
		secure::secure();
		
		if($profile_data['p_status']['message'] == 0 && $profile_init->alias_init->userlevel < alias::user(alias::USR_MODERRATOR))
		{
			trigger_error($profile_init->alias_init2->alias.' does not allow messages.');
		}
		
		if(!form::submitted())
		{
			$tpl->buffer($tpl->compile('profile_message_send'));
		}
		else if(form::submitted())
		{
			//void val::post() (profile::message() calls it)
			$message = $_POST['message'];
			
			if(trim($message) == null)
			{
				trigger_error('You cannot post an empty message.');
			}
			form::unique_check();
			
			$options = parse::parse_options_compile();
			
			profile::message($profile_init->alias_init2->alias, $profile_init->alias_init->alias, null, $message, $options, 'message');
			
			header::location($profile_init->uri.'&profile=message#Message:'.$db->insert_id());
		}
	}
	else if(isset($_GET['message_delete']))
	{
		secure::secure();
		
		$id = (int) val::post($_GET['message_delete']);
		
		$sql = $db->query("SELECT * FROM `{$db->tb->Message}` WHERE `ID`='$id' AND `Type`='message';");
		$fetch = $db->fetch_array($sql);
		
		$alias = $fetch['Alias'];
		$author = $fetch['Author'];
		
		//message existence
		message_existence('message');
		//restrictions
		if(($profile_init->alias_init->alias != $alias && $profile_init->profile_self) || ($profile_init->alias_init->alias != $author && !$profile_init->profile_self))
		{
			secure::restrict(alias::user(alias::USR_MODERRATOR));
		}
		
		if(!form::submitted())
		{
			$tpl->buffer($tpl->compile('profile_message_delete'));
		}
		else if(form::submitted())
		{
			$db->query("DELETE FROM `{$db->tb->Message}` WHERE `ID`='$id' AND `Type`='message';");
			
			header::location($profile_init->uri.'&profile=message');
		}
	}
	else if(isset($_GET['message_edit']))
	{
		secure::secure();
		
		$id = (int) val::post($_GET['message_edit']);
		
		$sql = $db->query("SELECT * FROM `{$db->tb->Message}` WHERE `ID`='$id' AND `Type`='message';");
		$fetch = $db->fetch_array($sql);
		
		$alias = $fetch['Alias'];
		$message = val::encode($fetch['Message']);
		$author = $fetch['Author'];
		$options = $fetch['Options'];
		
		//message existence
		message_existence('message');
		//restrictions
		if(($profile_init->alias_init->alias != $alias && $profile_init->profile_self) || ($profile_init->alias_init->alias != $author && !$profile_init->profile_self))
		{
			secure::restrict(alias::user(alias::USR_MODERRATOR));
		}
		
		if(!form::submitted())
		{
			$tpl->assign_vars(array(
			'message'       => $message,
			'parse_options' => $options
			));
			$tpl->buffer($tpl->compile('profile_message_edit'));
		}
		else if(form::submitted())
		{
			//AJAX
			val::AJAX_decode();
			
			$message = val::post($_POST['message']);
			//options
			$options = parse::parse_options_compile();
			
			if(trim($message) == null)
			{
				trigger_error('You cannot post an empty edit.');
			}
			form::unique_check();
			
			$db->query("UPDATE `{$db->tb->Message}` SET `Message`='$message', `Edit`=CONCAT_WS(';', CONCAT_WS(':', UNIX_TIMESTAMP(), SUBSTRING_INDEX(`Edit`, ':', -1)+1), '".val::post($profile_init->alias_init->alias)."'), `Options`='$options' WHERE `ID`='$id';");
			
			//AJAX: return to correct page
			$page = (isset($_GET['ajax'])) ? '&amp;page='.page::get_http_referer('page') : null;
			
			header::location($profile_init->uri.'&profile=message&alias='.$profile_init->alias_init2->alias.$page.'#Message:'.$id);
		}
	}
}
else if($profile == 'email')
{
	if($profile_init->profile_self)
	{
		if(!preg_match('/(email\=|email\_.*)/i', $_SERVER['QUERY_STRING']))
		{
			$page = new page;
			$page->display = 'numeric';
			
			//*box handler
			$box = array(
			'type'   => (isset($_GET['box']) && in_array($_GET['box'], array('email', 'email2'))) ? $_GET['box'] : 'email',
			'status' => (isset($_GET['box_status']) && is_numeric($_GET['box_status']) && in_array((int) $_GET['box_status'], array(0, 1, 2), true)) ? "AND `Status`='".$_GET['box_status']."'" : null
			);
			
			$search = (isset($_GET['search'])) ? forum::search(array("`Author`", "`Subject`", "`Message`"), val::post($_GET['search'])) : null;
			
			$sql = $page->page("SELECT * FROM `{$db->tb->Message}` WHERE `Alias`='".val::post($profile_init->alias_init2->alias)."' AND `Type`='{$box['type']}' $search {$box['status']} ORDER BY `ID` DESC;", 25, &$pagination);
			
			$emails = array();
			
			while($fetch = $db->fetch_array($sql))
			{
				$author = $fetch['Author'];
				$unix = reset(explode(' ', val::unix($fetch['Unix'], 'lite')));
				$subject = val::encode($fetch['Subject']);
				$id = $fetch['ID'];
				$status = $fetch['Status'];
				
				//status class
				switch($status)
				{
					case 0:
						$status = 'unread';
					break;
					case 1:
						$status = 'read';
					break;
					case 2:
						$status = 'replied';
					break;
				}
				
				$emails[] = array(
				'id'      => $id,
				'author'  => template::profile_link(val::encode($author), 'style="color: '.$alias_init->alias_mod($author, 'lite:Mod').';"'),
				'subject' => val::str_trim($subject, 60),
				'date'    => $unix,
				'status'  => $status
				);
			}
			
			$tpl->assign_vars(array(
			'emails'     => $emails,
			'pagination' => $pagination,
			'box'        => $box
			));
			$tpl->buffer($tpl->compile('profile_email'));
		}
		else if(isset($_GET['email']))
		{
			$id = (int) val::post($_GET['email']);
			
			$sql = $db->query("SELECT * FROM `{$db->tb->Message}` WHERE `ID`='$id' AND `Type` IN('email', 'email2');");
			$fetch = $db->fetch_array($sql);
			
			$alias = $fetch['Alias'];
			$author = $fetch['Author'];
			$unix = val::unix($fetch['Unix']);
			$options = $fetch['Options'];
			$subject = val::encode($fetch['Subject']);
			$email = parse::parse($fetch['Message'], $options);
			$type = $fetch['Type'];
			$status = $fetch['Status'];
			
			//email existence
			message_existence('email');
			//restrictions
			if($profile_init->alias_init->alias != $alias)
			{
				secure::restrict(alias::user(alias::USR_MODERRATOR));
			}
			
			//set status as read
			if($status == 0)
			{
				$db->query("UPDATE `{$db->tb->Message}` SET `Status`='1' WHERE `ID`='$id' AND `Type` IN('email', 'email2') AND `Status`='0';");
			}
			
			//previous/next links
			$sql = $db->query("(SELECT MAX(`ID`) AS `ID` FROM `{$db->tb->Message}` WHERE `ID`<'$id' AND `Type`='$type' AND `Alias`='".val::post($profile_init->alias_init->alias)."' LIMIT 1)
			UNION
			(SELECT MIN(`ID`) AS `ID` FROM `{$db->tb->Message}` WHERE `ID`>'$id' AND `Type`='$type' AND `Alias`='".val::post($profile_init->alias_init->alias)."' LIMIT 1);");
			
			$email_links = array();
			
			for($i = 1; $fetch = $db->fetch_array($sql), $i <= 2; $i++)
			{
				$email_links[($i & 1) ? 'previous' : 'next'] = $fetch['ID'];
			}
			
			$tpl->assign_vars(array(
			'id'          => $id,
			'author'      => array('link' => template::profile_link(val::encode($author), 'style="color: '.$alias_init->alias_mod($author, 'lite:Mod').';"'), 'author' => val::encode($author)),
			'alias'       => template::profile_link(val::encode($alias), 'style="color: '.$alias_init->alias_mod($alias, 'lite:Mod').';"'),
			'subject'     => $subject,
			'email'       => $email,
			'date'        => $unix,
			'email_links' => $email_links
			));
			$tpl->buffer($tpl->compile('profile_email_email'));
		}
		else if(isset($_GET['email_delete']))
		{
			if($_GET['email_delete'] == 'array')
			{
				email_array("DELETE FROM `{$db->tb->Message}` WHERE `Type` IN('email', 'email2') %s;");
			}
			else if(is_numeric($_GET['email_delete']))
			{
				$id = (int) val::post($_GET['email_delete']);
				
				$sql = $db->query("SELECT `Alias` FROM `{$db->tb->Message}` WHERE `ID`='$id' AND `Type` IN('email', 'email2');");
				$fetch = $db->fetch_array($sql);
				
				//email existence
				message_existence('email');
				//restrictions
				if($profile_init->alias_init->alias != $fetch['Alias'])
				{
					secure::restrict(alias::user(alias::USR_MODERRATOR));
				}
				
				if(!form::submitted())
				{
					$tpl->buffer($tpl->compile('profile_email_delete'));
				}
				else if(form::submitted())
				{
					$db->query("DELETE FROM `{$db->tb->Message}` WHERE `ID`='$id' AND `Type` IN('email', 'email2');");
					
					header::location($profile_init->uri.'&profile=email');
				}
			}
		}
		else if(isset($_GET['email_read']))
		{
			if($_GET['email_read'] == 'array')
			{
				email_array("UPDATE `{$db->tb->Message}` SET `Status`='1' WHERE `Type` IN('email', 'email2') %s;");
			}
		}
		else if(isset($_GET['email_unread']))
		{
			if($_GET['email_unread'] == 'array')
			{
				email_array("UPDATE `{$db->tb->Message}` SET `Status`='0' WHERE `Type` IN('email', 'email2') %s;");
			}
			else if(is_numeric($_GET['email_unread']))
			{
				$id = (int) val::post($_GET['email_unread']);
				
				$sql = $db->query("SELECT `Alias` FROM `{$db->tb->Message}` WHERE `ID`='$id' AND `Type` IN('email', 'email2');");
				$fetch = $db->fetch_array($sql);
				
				//email existence
				message_existence('email');
				//restrictions
				if($profile_init->alias_init->alias != $fetch['Alias'])
				{
					secure::restrict(alias::user(alias::USR_MODERRATOR));
				}
				
				//don't bother prompting...
				
				$db->query("UPDATE `{$db->tb->Message}` SET `Status`='0' WHERE `ID`='$id' AND `Type` IN('email', 'email2');");
				
				header::location($profile_init->uri.'&profile=email#Email:'.$id);
			}
		}
		else if(isset($_GET['email_edit']))
		{
			//restrictions
			secure::restrict(alias::user(alias::USR_MODERRATOR));
			
			$id = (int) val::post($_GET['email_edit']);
			
			$sql = $db->query("SELECT * FROM `{$db->tb->Message}` WHERE `ID`='$id' AND `Type` IN('email', 'email2');");
			$fetch = $db->fetch_array($sql);
			
			$options = $fetch['Options'];
			$subject = val::encode($fetch['Subject']);
			$email = val::encode($fetch['Message']);
			
			//email existence
			message_existence('email');
			
			if(!form::submitted())
			{
				$tpl->assign_vars(array(
				'subject'       => $subject,
				'email'         => $email,
				'parse_options' => $options
				));
				$tpl->buffer($tpl->compile('profile_email_edit'));
			}
			else if(form::submitted())
			{
				$subject = val::post($_POST['subject']);
				$email = val::post($_POST['email']);
				//options
				$options = parse::parse_options_compile();
				
				$db->query("UPDATE `{$db->tb->Message}` SET `Subject`='$subject', `Message`='$email', `Edit`=CONCAT_WS(';', CONCAT_WS(':', UNIX_TIMESTAMP(), SUBSTRING_INDEX(`Edit`, ':', -1)+1), '".val::post($profile_init->alias_init->alias)."'), `Options`='$options' WHERE `ID`='$id';");
				
				header::location($profile_init->uri.'&profile=email&email='.$id);
			}
		}
	}
	else if(!$profile_init->profile_self)
	{
		secure::secure();
		
		if($profile_data['p_status']['email'] == 0 && $profile_init->alias_init->userlevel < alias::user(alias::USR_MODERRATOR))
		{
			trigger_error($profile_init->alias_init2->alias.' does not allow emails.');
		}
		
		//reply
		if(isset($_GET['reply']))
		{
			$reply_id = (int) val::post($_GET['reply']);
			
			$sql = $db->query("SELECT `Subject` FROM `{$db->tb->Message}` WHERE `ID`='$reply_id' AND `Alias`='".val::post($profile_init->alias_init->alias)."' AND `Type`='email';");
			$fetch = $db->fetch_array($sql);
			
			$subject = val::encode($fetch['Subject']);
		}
		//subject
		else
		{
			$subject = (isset($_GET['subject'])) ? val::encode(urldecode($_GET['subject'])) : null;
		}
		
		if(!form::submitted())
		{
			$tpl->assign_vars(array(
			'subject' => $subject
			));
			$tpl->buffer($tpl->compile('profile_email_send'));
		}
		else if(form::submitted())
		{
			//void val::post() (profile::message() runs it)
			$subject = $_POST['subject'];
			$email = $_POST['email'];
			//box
			$box = $_POST['box'];
			//options
			$options = parse::parse_options_compile();
			
			//recipients
			$recipients = array_filter(explode(',', preg_replace('/\s/', null, val::post($_POST['recipient']))), create_function('$str', 'return !empty($str);')); #function($str){return !empty($str);}
			$recipient = array($profile_init->alias_init2->alias);
			
			foreach($recipients as $value)
			{
				$sql = $db->query("SELECT null FROM `{$db->tb->Alias}` WHERE `Alias`='$value';");
				
				//check for duplicates & alias existence
				if(!in_array($value, $recipient) && trim($value) != null && $db->count_rows($sql) == 1)
				{
					$recipient[] = $value;
				}
			}
			
			$subject = ($subject == null) ? 'No Subject' : $subject;
			if(trim($email) == null)
			{
				trigger_error('You cannot post an empty email.');
			}
			form::unique_check();
			
			foreach($recipient as $value)
			{
				profile::message($value, $profile_init->alias_init->alias, $subject, $email, $options, 'email');
				
				//box
				if($box == 1)
				{
					profile::message($profile_init->alias_init->alias, $value, $subject, $email, $options, 'email2');
				}
			}
			
			if(isset($_GET['reply']))
			{
				//update status to replied
				$reply_id = (int) val::post($_GET['reply']);
			
				$db->query("UPDATE `{$db->tb->Message}` SET `Status`='2' WHERE `ID`='$reply_id' AND `Alias`='".val::post($profile_init->alias_init->alias)."' AND `Type`='email' AND `Status`='1';");
			}
			
			header::location($profile_init->uri.'&profile=index&alias='.$profile_init->alias_init2->alias);
		}
	}
}

$tpl->assign_vars(array(
'profile_main' => $tpl->buffer()
));
return $tpl->compile('profile_main');
?>