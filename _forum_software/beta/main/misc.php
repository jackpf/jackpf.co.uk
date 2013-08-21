<?php
//supports lite
$lite = (basename($_SERVER['PHP_SELF']) == basename(__FILE__)) ? true : false;
if($lite)
	@include_once '../config/lib.php';
	
$db = new connection;
$alias_init = new alias;
$tpl = new template('misc');

$status = (isset($_GET['status'])) ? $_GET['status'] : null;

$tpl->assign_vars(array(
'lite' => $lite
));

//browse profiles
if($status == 'search_users')
{
	$stat = new stat;
	$search = (isset($_GET['search'])) ? val::post($_GET['search']) : null;
	
	if(!isset($search))
	{
		//compile permissions
		$permission = array();
		foreach($alias_init->user as $value)
		{
			$permission[] = array(
			'name'  => val::encode($value['Name']),
			'color' => val::encode($value['Mod'])
			);
		}
		
		//fetch stats for online display
		if(!$lite)
		{
			$stats = $stat->fetch_stats();
			
			foreach($stats['aliases'] as $key => $value/*&$value*/)
			{
				$stats['aliases'][$key] = template::profile_link(val::encode($value), 'style="color: '.$alias_init->alias_mod($value, 'lite:Mod').';"');
			}
		}
		
		$tpl->assign_vars(array(
		'search'      => null,
		'permissions' => $permission,
		'online'      => $stats['aliases']
		));
	}
	else if(isset($search))
	{
		$page = new page;
		
		//search params for default mode
		if(!$lite)
		{
			$user = forum::search2("`User`", val::post($_GET['user']));
			$posts = forum::search3("`PostCount`", val::post($_GET['posts']));
			//convert date to unix for forum::search3
			$_GET['register'][1] = strtotime($_GET['register'][1]);
			$unix = forum::search3("`A`.`Unix`", val::post($_GET['register']), ($posts == null) ? "HAVING" : "AND");
		}
		
		$sql = $page->page("SELECT `A`.*, COUNT(`F`.`ID`) AS `PostCount`
		FROM `{$db->tb->Alias}` `A`
		LEFT OUTER JOIN `{$db->tb->Forum}` `F` ON `A`.`Alias`=`F`.`Author`
		WHERE (`A`.`Alias` LIKE '%$search%' OR `A`.`Email` LIKE '%$search%' OR `A`.`Name` LIKE '%$search%')
		$user
		GROUP BY `A`.`Alias`
		$posts $unix
		ORDER BY `A`.`ID` ASC;", 25, &$pagination);
		
		$search = array();
		
		if($db->count_rows($sql) > 0)
		{
			while($fetch = $db->fetch_array($sql))
			{
				$search[] = array('link' => template::profile_link(val::encode($fetch['Alias']), 'style="color: '.$alias_init->alias_mod($fetch['Alias'], 'lite:Mod').';"'), 'alias' => val::encode($fetch['Alias']));
			}
		}
		
		$tpl->assign_vars(array(
		'search'     => $search,
		'pagination' => $pagination
		));
	}
	
	$tpl->buffer($tpl->compile('misc_search_users'));
	
	if($lite)
	{
		//echo the buffer rather than return...
		echo $tpl->compile('misc_search_users');
	}
}
//cpanel
else if($status == 'cpanel')
{
	secure::secure();
	secure::restrict(alias::user(alias::USR_MODERATOR));
	
	$cmd = (isset($_GET['cmd'])) ? $_GET['cmd'] : null;
	
	$tpl->assign_vars(array(
	'cmd' => $cmd
	));
	
	function alias_existence()
	{
		global $db, $sql;
		
		if($db->count_rows($sql) == 0)
		{
			trigger_error('This user does not exist.');
		}
	}
	
	//file editing (config)
	if($cmd == 'config')
	{
		secure::restrict(alias::user(alias::USR_ADMINISTRATOR));
		
		if(!form::submitted())
		{
			$tpl->assign_vars(array(
			'config' => val::encode($config_init->get_config(null, true))
			));
		}
		else if(form::submitted())
		{
			//write new config
			$config_init->write($_POST['config'], true);
			
			header::location($_SERVER['HTTP_REFERER']);
		}
	}
	else if($cmd == 'forum_config')
	{
		if(!form::submitted())
		{
			$sql = $db->query("SELECT `ID`, `Forum`, `Subject`, SUBSTRING_INDEX(`Status`, ':', -1) AS `Order`, IF(`ID`=`Forum`, 1, 0) AS `Child` FROM `{$db->tb->Forum}` WHERE `Type`='forum' ORDER BY `Child`, `Order`, `Forum`;");
			
			$forums = array();
			
			while($fetch = $db->fetch_array($sql))
			{
				$forums[] = array(
				'id'      => $fetch['ID'],
				'subject' => val::encode($fetch['Subject']),
				'child'   => ($fetch['ID'] != $fetch['Forum']),
				'order'   => (int) val::encode($fetch['Order'])
				);
			}
			
			$tpl->assign_vars(array(
			'forums' => $forums
			));
		}
		else if(form::submitted())
		{
			foreach($_POST['forum_order_id'] as $key => $value)
			{
				$id = (int) val::post($value);
				$order = (int) val::post($_POST['forum_order'][$key]);
				
				$db->query("UPDATE `{$db->tb->Forum}` SET `Status`=CONCAT_WS(':', SUBSTRING_INDEX(`Status`, ':', 1), '{$order}') WHERE `ID`='$id';");
			}
			
			header::location($_SERVER['HTTP_REFERER']);
		}
		
		if($lite)
		{
			//echo the buffer rather than return...
			echo $tpl->compile('misc_cpanel');
		}
	}
	else if($cmd == 'mod_list')
	{
		$page = new page;
		$mod = (isset($_GET['mod'])) ? val::post($_GET['mod']) : 'post';
		
		//define author
		$query = (isset($_GET['alias'])) ? "AND `Author`='".val::post($_GET['alias'])."'" : null;
		
		switch($mod)
		{
			//reports hack
			case 'report':
				$mod = 'email';
				$query = "AND `Subject`='".val::post($config_init->get_config('email_prefix')."Post Reported")."'";
			break;
			//requests hack
			case 'request':
				$mod = 'email';
				$query  = "AND `Subject`='".val::post($config_init->get_config('email_prefix')."Group Join Request")."'";
			break;
			//post_mod hack
			case 'post_mod':
				$mod = 'email';
				$query  = "AND `Subject`='".val::post($config_init->get_config('email_prefix')."Moderation Post")."'";
			break;
		}
		
		switch($mod)
		{
			case 'email': case 'message':
				$sql = $page->page("SELECT * FROM `{$db->tb->Message}` WHERE `Type`='$mod' $query ORDER BY `ID` DESC;", 10, &$pagination);
			break;
			default:
				//default to post
				$sql = $page->page("SELECT * FROM `{$db->tb->Forum}` WHERE `Type` IN('forum', 'thread', 'post') $query ORDER BY `ID` DESC;", 10, &$pagination);
			break;
		}
		
		$mod_list = array();
		
		while($fetch = $db->fetch_array($sql))
		{
			switch($mod)
			{
				case 'message': case 'email':
					$id      = $fetch['ID'];
					$alias  = $fetch['Alias'];
					$author  = $fetch['Author'];
					$unix    = val::unix($fetch['Unix']);
					$options = $fetch['Options'];
					$message = parse::parse($fetch['Message'], $options, $options & ~parse::options_attach_signature);
					
					$mod_list[] = array(
					'author'    => array('alias' => val::encode($author),
										 'link'  => template::profile_link(val::encode($author), 'style="color: '.$alias_init->alias_mod($author, 'lite:Mod').';"')),
					'recipient' => array('alias' => val::encode($alias),
										 'link'  => template::profile_link(val::encode($alias), 'style="color: '.$alias_init->alias_mod($alias, 'lite:Mod').';"')),
					'id'        => $id,
					'date'      => $unix,
					'link'      => val::encode($_SERVER['PHP_SELF'].'?action=profile&status=profile&profile=message&alias='.$alias.'#Message:'.$id),
					'message'   => $message
					);
				break;
				default:
					$id      = $fetch['ID'];
					$forum   = $fetch['Forum'];
					$thread  = $fetch['Thread'];
					$author  = $fetch['Author'];
					$unix    = val::unix($fetch['Unix']);
					$options = $fetch['Options'];
					$subject = val::encode($fetch['Subject']);
					$post    = parse::parse($fetch['Post'], $options & ~parse::options_attach_signature);
					
					$mod_list[] = array(
					'author'  => array('alias' => val::encode($author),
									  'link'   => template::profile_link(val::encode($author), 'style="color: '.$alias_init->alias_mod($author, 'lite:Mod').';"')),
					'id'      => $id,
					'date'    => $unix,
					'link'    => val::encode($_SERVER['PHP_SELF'].'?action=forum&forum='.$forum.'&status=thread&thread='.$thread.'#Post:'.$id),
					'subject' => $subject,
					'message' => $post
					);
				break;
			}
		}
		
		$tpl->assign_vars(array(
		'mod_list'   => $mod_list,
		'pagination' => $pagination
		));
	}
	else if($cmd == 'users')
	{
		$usergroups = array();
		
		foreach($alias_init->user as $value)
		{
			//define query for use in query & pagination
			$query = "SELECT `Alias` FROM `{$db->tb->Alias}` WHERE `User`='".val::post($value['Name'])."' ORDER BY `ID`;";
			
			if($value['Name'] == 'User')
			{
				//paginate Users
				$page = new page;
				$page->display = 'numeric';
				$sql = $page->page($query, 25, &$pagination);
			}
			else
			{
				$pagination = null;
				$sql = $db->query($query);
			}
			
			$members = array();
			
			while($fetch = mysql_fetch_array($sql))
			{
				$alias = val::encode($fetch['Alias']);
				
				$members[] = array(
				'link'  => template::profile_link($alias, 'style="color: '.$value['Mod'].';"'),
				'alias' => $alias
				);
			}
			
			$usergroups[] = array(
			'name'       => $value['Name'],
			'color'      => $value['Mod'],
			'members'    => $members,
			'pagination' => (isset($pagination)) ? $pagination : null
			);
		}
		
		$tpl->assign_vars(array(
		'usergroups' => $usergroups
		));
	}
	if($cmd == 'edit_user')
	{
		if(!isset($_REQUEST['alias']))
		{
			$tpl->assign_vars(array(
			'login' => false
			));
		}
		else if(isset($_REQUEST['alias']))
		{
			$alias = val::post($_REQUEST['alias']);
			
			$sql = $db->query("SELECT * FROM `{$db->tb->Alias}` WHERE `Alias`='$alias';");
			$fetch = $db->fetch_array($sql);
			
			$usergroup = $fetch['User'];
			$alias = val::encode($fetch['Alias']);
			$password = val::encode($fetch['Password']);
			
			//alias existence
			alias_existence();
			//check login is allowed
			if($alias_init->user[$usergroup]['Userlevel'] > $alias_init->userlevel)
			{
				trigger_error('You do not possess a sufficient userlevel to login as this user.');
			}
			
			$tpl->assign_vars(array(
			'login'    => true,
			'alias'    => $alias,
			'password' => $password
			));
		}
	}
	else if($cmd == 'edit_usergroup')
	{
		//sql for pre-mod & mod
		if(isset($_REQUEST['alias']))
		{
			$alias = val::post($_REQUEST['alias']);
			
			$sql = $db->query("SELECT * FROM `{$db->tb->Alias}` WHERE `Alias`='$alias';");
			$fetch = $db->fetch_array($sql);
			
			$usergroup = val::encode($fetch['User']);
			$alias = val::encode($fetch['Alias']);
			$p_status = profile::profile_status($fetch['Status']);
			
			//alias existence
			alias_existence();
		}
		
		if(!form::submitted() && !isset($alias))
		{
			$tpl->assign_vars(array(
			'edit_usergroup' => false
			));
		}
		else if(!isset($_POST['usergroup']))
		{
			$permissions = array();
			
			foreach($alias_init->user as $value)
			{
				$permissions[] = array(
				'name'  => val::encode($value['Name']),
				'color' => val::encode($value['Mod']),
				);
			}
			
			$tpl->assign_vars(array(
			'edit_usergroup' => true,
			'alias'          => $alias,
			'usergroup'      => array('usergroup' => $usergroup, 'color' => $alias_init->user[$usergroup]['Mod']),
			'permissions'    => $permissions,
			'alias_mod'      => $p_status['alias_mod']
			));
		}
		else if(form::submitted() && isset($_POST['usergroup']))
		{
			$new_usergroup = val::post($_POST['usergroup']);
			$p_status['alias_mod'] = (int) val::post($_POST['alias_mod']);
			$p_status = profile::compile_profile_status($p_status);
			
			//check mod is allowed
			if($alias_init->user[$new_usergroup]['Userlevel'] > $alias_init->userlevel || $alias_init->user[$usergroup]['Userlevel'] > $alias_init->userlevel)
			{
				trigger_error('You do not possess a sufficient userlevel to modify this user\'s usergroup, or modify to this particular usergroup.');
			}
			
			$db->query("UPDATE `{$db->tb->Alias}` SET `User`='$new_usergroup', `Status`='$p_status' WHERE `Alias`='$alias';");
			
			header::location($_SERVER['PHP_SELF'].'?action=profile&status=profile_self&profile=profile&account=cpanel');
		}
	}
	else if($cmd == 'ban_list')
	{
		$sql = $db->query("SELECT `Alias` FROM `{$db->tb->Alias}` WHERE SUBSTRING_INDEX(`User`, ':', 1)='Banned';");
		
		$banned = array();
		
		while($fetch = $db->fetch_array($sql))
		{
			$banned[] = template::profile_link(val::encode($fetch['Alias']), 'style="color: '.$alias_init->alias_mod($fetch['Alias'], 'lite:Mod').';"');
		}
		
		$tpl->assign_vars(array(
		'banned' => $banned
		));
	}
	else if($cmd == 'logs')
	{
		if(!form::submitted())
		{
			$page = new page;
			$page->display = 'numeric';
			
			$order = (isset($_GET['order'])) ? val::post(urldecode($_GET['order'])) : '`Unix` DESC';
			
			$sql = $page->page("SELECT * FROM `{$db->tb->Alias_Stats}` WHERE `Type` IN('alias', 'hidden', 'stranger') ORDER BY $order;", 25, &$pagination);
			
			$logs = array();
			
			while($fetch = $db->fetch_array($sql))
			{
				$logs[] = array(
				'alias'         => val::encode($fetch['Alias']),
				'type'          => val::encode($fetch['Type']),
				'online_status' => $fetch['Online'],
				'unix_stamp'    => val::unix($fetch['Unix']),
				'uri'           => val::encode($fetch['URI'])
				);
			}
			
			$tpl->assign_vars(array(
			'alias'      => false,
			'logs'       => $logs,
			'pagination' => $pagination
			));
		}
		else if(form::submitted())
		{
			$alias = val::post($_POST['alias']);
			
			$sql = $db->query("SELECT * FROM `{$db->tb->Alias_Stats}` WHERE `Alias`='$alias';");
			$fetch = $db->fetch_array($sql);
			
			$unix = time() - $fetch['Unix'];
			$uri = val::encode($fetch['URI']);
			//fetch alias to avoid val::post()'s escaping
			$alias = $fetch['Alias'];
			
			echo '';
			
			$tpl->assign_vars(array(
			'alias'          => template::profile_link(val::encode($alias), 'style="color: '.$alias_init->alias_mod($alias, 'lite:Mod').';"'),
			'unix_timestamp' => $unix,
			'uri'            => $uri,
			'online'         => (stat::online($alias)) ? true : false
			));
		}
	}
	else if($cmd == 'user')
	{
		$group = (isset($_GET['group'])) ? $_GET['group'] : 'index';
		$user  = (isset($_GET['user'])) ? $_GET['user'] : null;
		
		$tpl->assign_vars(array(
		'moderator'  => ($alias_init->userlevel >= alias::user(alias::USR_MODERATOR)) ? true : false,
		'users_type' => $group
		));
		
		if($group == 'index')
		{
			//iterate through each usergroup
			$usergroups = array();
			
			foreach($alias_init->user as $value)
			{
				//fetch a few members...
				$sql = $db->query("SELECT `Alias` FROM `{$db->tb->Alias}` WHERE `User`='".val::post($value['Name'])."' ORDER BY `ID` ASC LIMIT 5;");
				
				$members = array();
				
				while($fetch = $db->fetch_array($sql))
				{
					$members[] = template::profile_link(val::encode($fetch['Alias']), 'style="color: '.val::encode($value['Mod']).';"');
				}
				
				//check alias' join status
				$join_status = ($alias_init->usergroup == $value['Name']) ? 'joined' : (($value['Owner'] == null || $value['Userlevel'] <= $alias_init->userlevel) ? 'join' : 'request');
				
				$usergroups[] = array(
				'name'        => val::encode($value['Name']),
				'color'       => val::encode($value['Mod']),
				'join_status' => $join_status,
				'members'     => $members
				);
			}
			
			$tpl->assign_vars(array(
			'usergroups' => $usergroups
			));
		}
		else if($group == 'join')
		{
			secure::secure();
			
			$group = $alias_init->user[$user];
			
			//check group existence
			if($group == null)
			{
				trigger_error('This group does not exist.');
			}
			
			//check user is not already a member of the group
			if($alias_init->usergroup == $group['Name'])
			{
				trigger_error('You are already in this group.');
			}
			
			if(!form::submitted())
			{
				$tpl->assign_vars(array(
				'user'  => array('name' => $group['Name'], 'color' => $group['Mod'])
				));
			}
			else if(form::submitted())
			{
				if($group['Owner'] != null && $alias_init->userlevel < $group['Userlevel'])
				{
					//email owners the request
					foreach(val::array_explode(',', $group['Owner']) as $value)
					{
						profile::message($group['Owner'], $config_init->get_config('site_owner'), $config_init->get_config('email_prefix').'Group Join Request', "$value,\n$alias_init->alias has requested to join the group [color={$group['Mod']}]{$group['Name']}[/color].\nModerate this request [url={$_SERVER['PHP_SELF']}?action=misc&status=cpanel&cmd=user&group=authenticate&user={$group['Name']}&alias=$alias_init->alias&key=".sha1($alias_init->alias)."]here[/url].", parse::options_parse_code | parse::options_parse_code_ws, 'email');
					}
				}
				else
				{
					//automatic authentication
					$db->query("UPDATE `{$db->tb->Alias}` SET `User`='".val::post($group['Name'])."' WHERE `Alias`='".val::post($alias_init->alias)."';");
				}
				
				header::location($_SERVER['PHP_SELF'].'?action=misc&status=cpanel&cmd=user');
			}
		}
		else if($group == 'authenticate')
		{
			secure::secure();
			
			$alias = (isset($_GET['alias'])) ? $_GET['alias'] : null;
			$group = $alias_init->user[$user];
			
			//check group existence
			if($group == null)
			{
				trigger_error('This group does not exist.');
			}
			//check if user is an owner
			if(!in_array($alias_init->alias, val::array_explode(',', $group['Owner'])))
			{
				trigger_error('You are not an owner of this group.');
			}
			//check if the link has been followed from the email
			if(sha1($alias) != $_GET['key'])
			{
				trigger_error('This user has not requested to join this group.');
			}
			
			if(!form::submitted())
			{
				$tpl->assign_vars(array(
				'alias_request' => val::encode($alias2),
				'user'          => array('name' => val::encode($group['Name']), 'color' => val::encode($group['Mod']))
				));
			}
			else if(form::submitted())
			{
				$db->query("UPDATE `{$db->tb->Alias}` SET `User`='".val::post($group['Name'])."' WHERE `Alias`='".val::post($alias)."';");
				
				header::location($_SERVER['PHP_SELF'].'?action=profile&status=profile_self&profile=email');
			}
		}
		else if($group == 'user')
		{
			$group = $alias_init->user[$user];
			$owners = val::array_explode(',', $group['Owner']);
			
			$page = new page;
			$page->display = 'numeric';
			
			//check group existence
			if($group == null)
			{
				trigger_error('This group does not exist.');
			}
			
			$sql = $page->page("SELECT `Alias` FROM `{$db->tb->Alias}` WHERE `User`='".val::post($user)."' ORDER BY `ID` ASC;", 25, &$pagination);
			
			$group_owners = $group_members = array();
			
			while($fetch = $db->fetch_array($sql))
			{
				$alias = $fetch['Alias'];
				
				//check if user is an owner | member
				$member_type = (in_array($alias, $owners)) ? 'group_owners' : 'group_members';
				
				//compile list
				array_push($$member_type, template::profile_link(val::encode($alias), 'style="color: '.val::encode($group['Mod']).';"'));
			}
			
			$tpl->assign_vars(array(
			'user'          => array('name' => $group['Name'], 'color' => $group['Mod']),
			'group_owners'  => $group_owners,
			'group_members' => $group_members
			));
		}
		else if($group == 'users')
		{
			secure::secure();
			secure::restrict(alias::user(alias::USR_ADMINISTRATOR));
			
			$users = (isset($_GET['users'])) ? $_GET['users'] : 'index';
			
			$tpl->assign_vars(array(
			'users' => $users
			));
			
			switch($users)
			{
				case 'index':
					$tpl->assign_vars(array(
						'usergroups' => $alias_init->user
					));
				break;
				case 'new':
					if(!form::submitted())
					{
						if(isset($_GET['usergroup']) && array_key_exists($_GET['usergroup'], $alias_init->user))
						{
							$tpl->assign_vars(array(
								'usergroup' => val::encode($alias_init->user[$_GET['usergroup']])
							));
						}
					}
					else if(form::submitted())
					{
						$name      = val::post($_POST['name']);
						$userlevel = (float) val::post($_POST['userlevel']);
						$mod       = val::post($_POST['mod']);
						$owner     = val::post($_POST['owner']);
						
						$db->query("INSERT INTO `{$db->tb->Users}`
						(`ID`, `Name`, `Userlevel`, `Mod`, `Owner`)
						VALUES
						(null, '$name', '$userlevel', '$mod', '$owner')
						ON DUPLICATE KEY
						UPDATE `Userlevel`=VALUES(`Userlevel`), `Mod`=VALUES(`Mod`), `Owner`=VALUES(`Owner`);");
						
						header::location($_SERVER['PHP_SELF'].'?action=misc&status=cpanel&cmd=user&group=users');
					}
				break;
				case 'delete':
					$usergroup = $_GET['usergroup'];
					
					if(!array_key_exists($_GET['usergroup'], $alias_init->user))
					{
						trigger_error('This usergroup does not exist.');
					}
					
					//check if any users are in this usergroup...
					$affected_users = $db->count_rows($db->query("SELECT null FROM `{$db->tb->Alias}` WHERE `User`='$usergroup';"));
					
					if(!form::submitted())
					{
						$tpl->assign_vars(array(
						'affected_users' => $affected_users,
						'usergroup'      => $alias_init->user[$usergroup]
						));
					}
					else if(form::submitted())
					{
						$db->query("DELETE FROM `{$db->tb->Users}` WHERE `Name`='$usergroup';");
						
						//revert users of this usergroup to "users"
						if($affected_users)
						{
							$db->query("UPDATE `{$db->tb->Alias}` SET `User`='".val::post($alias_init->user['User']['Name'])."' WHERE `User`='$usergroup';");
						}
						
						header::location($_SERVER['PHP_SELF'].'?action=misc&status=cpanel&cmd=user&group=users');
					}
				break;
			}
		}
	}
	
	$tpl->buffer($tpl->compile('misc_cpanel'));
}
/*//forum: read forum
else if($status == 'forum_read')
{
	secure::secure();
	
	if(isset($_GET['forum']))
	{
		$forumid = (int) val::post($_GET['forum']);
		
		$sql = $db->query("SELECT `F`.`ID`, COUNT(`P`.`ID`) AS `PostCount`
		FROM `{$db->tb->Forum}` `F`
		LEFT OUTER JOIN `{$db->tb->Forum}` `P` ON `F`.`ID`=`P`.`Thread` AND `P`.`Type` IN('thread', 'post')
		WHERE `F`.`Forum`='$forumid' AND `F`.`Type`='thread'
		GROUP BY `F`.`ID`;");
		
		$query = array();
		
		while($fetch = $db->fetch_array($sql))
		{
			$query[] = "(null, '".val::post($alias_init->alias)."', '$forumid', '{$fetch['ID']}', '{$fetch['PostCount']}')";
		}
		
		$db->query("INSERT INTO `{$db->tb->Forum_Data}`
		(`ID`, `Alias`, `Forum`, `Thread`, `PostCount`)
		VALUES"
		.implode(", ", $query).
		"ON DUPLICATE KEY
		UPDATE `PostCount`=VALUES(`PostCount`);");
	}
}*/
//AJAX
else if($status == 'ajax')
{
	//validation
	if(isset($_POST['alias']) && isset($_POST['email']) && isset($_GET['validate']))
	{
		if($_GET['validate'] == 'alias')
		{
			$post = val::post(urldecode(val::alias_val(trim($_POST['alias']))));
			$type = 'Alias';
		}
		else if($_GET['validate'] == 'email')
		{
			$post = val::post(urldecode(val::email_val(trim($_POST['email']))));
			$type = 'Email';
		}
		
		if($post == null)
		{
			$validation = 'invalid';
		}
		else
		{
			$sql = $db->query("SELECT null FROM `{$db->tb->Alias}` WHERE `$type`='$post';");
			$validation = ($db->count_rows($sql) == 0) ? 'available' : 'unavailable';
		}
		
		$tpl->assign_vars(array(
		'ajax_type'  => 'register',
		'validation' => $validation
		));
	}
	//preview
	else
	{
		if(form::submitted())
		{
			//compile $_POST array
			val::AJAX_decode(false);
			$i = 0;
			foreach($_POST as $value)
			{
				$_POST[$i] = trim($value);
				$i++;
			}
			
			//multiple preview
			if(isset($_POST['subject']) || isset($_POST['post_explode']) || isset($_POST['message_explode']))
			{
				$preview = array($_POST[0], parse::parse(urldecode($_POST[1]), parse::parse_options_compile()));
				$preview = '<span style="text-decoration: underline;">'.$_POST[0].'</span><br />'.parse::parse(urldecode($_POST[1]), parse::parse_options_compile(), array('signature' => false));
			}
			//standard
			else
			{
				$preview = parse::parse($_POST[0], parse::parse_options_compile());
			}
			
			$tpl->assign_vars(array(
			'ajax_type' => 'preview',
			'preview'   => $preview
			));
		}
	}
	
	//echo the buffer rather than return...
	echo $tpl->compile('misc_ajax');
}
else if($status == 'forum_reputation')
{
	secure::secure();
	
	$postid = val::post($_GET['post']);
	
	$sql = $db->query("SELECT `Author` FROM `{$db->tb->Forum}` WHERE `ID`='$postid' AND TYPE IN('thread', 'post');");
	$fetch = $db->fetch_array($sql);
	$author = $fetch['Author'];
	$post_exists = $db->count_rows($sql);
	
	$sql = $db->query("SELECT null FROM `{$db->tb->Forum_Reputation}` WHERE `Author`='".val::post($alias_init->alias)."' /*`Alias`*/ AND `Post`='$postid';");
	$rep = $db->count_rows($sql);
	
	if($postid != null && $author != $alias_init->alias && $post_exists > 0 && $rep == 0)
	{
		$posts = forum::posts($alias_init->alias, false);
		
		$rep = 1 + round(($posts['post_count'] + $posts['rep']) / 25);
		
		$db->query("INSERT INTO `{$db->tb->Forum_Reputation}` (`ID`, `Author`, `Alias`, `Post`, `Reputation`) VALUES (null, '".val::post($alias_init->alias)."', '".val::post($author)."', '$postid', '$rep');");
	}
}
//IM
else if($status == 'im')
{
	//init session handle
	session_name('jackpf_im');
	session_start();
	//init vars
	$_SERVER['PHP_SELF_IM'] = '/main/misc.php?status=im';
	$id                     = (int) (isset($_GET['im'])) ? val::post($_GET['im']) : null;
	$im_action              = (isset($_GET['im_action'])) ? $_GET['im_action'] : null;
	
	function IM_existence()
	{
		global $db, $id;
		
		$sql = $db->query("SELECT null FROM `{$db->tb->IM}` WHERE `ID`='$id';");
		
		if($db->count_rows($sql) == 0)
		{
			trigger_error('This IM does not exist.');
		}
	}
	
	secure::secure();
	
	switch($im_action)
	{
		case 'new':
			//new IM
			$db->query("INSERT INTO `{$db->tb->IM}` (`ID`, `ID2`, `Author`, `Post`) VALUES (null, LAST_INSERT_ID(), '".val::post($alias_init->alias)."', 'IM started.');");
			
			header::location(reset(explode('&im=', $_SERVER['HTTP_REFERER'])).'&im='.$db->insert_id());
		break;
		case 'post':
			//new message
			val::AJAX_decode(false);
			
			$id = (int) val::post((!isset($_GET['id'])) ? $_POST['id'] : $_GET['id']);
			
			$message = (!isset($_GET['message'])) ? $_POST['message'] : $_GET['message'];
			//alias of val::AJAX_decode
			$message = val::post(urldecode($message));
			
			$db->query("INSERT INTO `{$db->tb->IM}` (`ID`, `ID2`, `Author`, `Post`) VALUES (null, '$id', '".val::post($alias_init->alias)."', '$message');");
		break;
		case 'init':
			//fetch IM
			$id = (int) val::post($_GET['id']);
			$c_id = (isset($_SESSION[$config_init->get_config('cookie_prefix').'im'])) ? val::post($_SESSION[$config_init->get_config('cookie_prefix').'im']) : 0;
			
			//check session relates to this IM
			if($_SESSION[$config_init->get_config('cookie_prefix').'imid'] != $id)
			{
				$_SESSION[$config_init->get_config('cookie_prefix').'imid'] = $id;
				$_SESSION[$config_init->get_config('cookie_prefix').'im'] = 0;
			}
			
			//select all messages after currently loaded
			$sql = $db->query("SELECT * FROM `{$db->tb->IM}` WHERE (`ID`='$id' OR `ID2`='$id') AND `ID`>'$c_id' ORDER BY `ID`;");
			
			$messages = array();
			
			while($fetch = $db->fetch_array($sql))
			{
				$id = $fetch['ID'];
				$author = val::encode($fetch['Author']);
				$message = parse::parse($fetch['Post'], parse::options_parse_code | parse::options_parse_smiley);
				
				$messages[] = array(
				'author'  => $author,
				'message' => $message
				);
			}
			
			//update currently loaded messages
			if($id > $c_id)
			{
				$_SESSION[$config_init->get_config('cookie_prefix').'im'] = $id;
			}
			
			$tpl->assign_vars(array(
			'im'       => 'messages',
			'messages' => $messages
			));
			
			//echo the buffer rather than return...
			echo $tpl->compile('misc_im');
		break;
		default:
			//defined IM
			if(isset($id))
			{
				//im existence
				IM_existence();
				
				$tpl->assign_vars(array(
				'im'     => 'im',
				'imid'   => $id,
				'online' => (stat::online($alias_init->alias)) ? true : false
				));
			}
			//no IM
			else
			{
				$tpl->assign_vars(array(
				'im' => false
				));
			}
		break;
	}
	
	$tpl->buffer($tpl->compile('misc_im'));
}
//end (for redirect after edit_user login)
else
{
	header::location(val::encode($_SERVER['PHP_SELF']));
}

return $tpl->buffer();
?>