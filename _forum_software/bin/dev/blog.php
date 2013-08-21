<?php
//dev
include 'devlib.php';
dev_init();

//@blog config
class blog
{
	public function url_rewrite()
	{
		//sort blog's rewrites
		foreach($_GET as &$value)
		{
			if(strstr($value, '/'))
			{
				$__GET = explode('/', $value);
				$value = reset($__GET);
				
				$__GET = explode('=', end($__GET));
				$_GET[reset($__GET)] = end($__GET);
			}
		}
		
		unset($__GET);
	}
	public function buffer_output($output)
	{
		$buffer = array(
		'/\&quot\;(.*?)\&quot\;/m'   => '&ldquo;$1&rdquo;',
		'/\&\#039\;(.*?)\&\#039\;/m' => '&lsquo;$1&rsquo;',
		'/\&quot\;/'                 => '&ldquo;',
		'/\&\#039\;/'                => '&rsquo;'
		);
		
		return preg_replace(array_keys($buffer), $buffer, $output);
	}
	public function entry_existence($type)
	{
		//global sql used
		global $db, $sql, $tpl;
		
		if($db->count_rows($sql) == 0)
		{
			return trigger_error(sprintf($tpl->error('BLOG_NOT_EXIST'), $type));
		}
		//check for mutiple entry existence, in case of reference by subject
		else if($db->count_rows($sql) > 1)
		{
			#return trigger_error($tpl->error('BLOG_MULTIPLE_EXISTENCE');
		}
	}
	public function locked($status)
	{
		return (in_array($status, array('hidden'))) ? true : false;
	}
	public function subscribe($entry = 'blog')
	{
		global $db;
		
		$sql = $db->query("SELECT `Alias` FROM `{$db->tb->Forum_Subscription}` WHERE `Thread`='blog:$entry';");
		
		$subscriptions = array();
		while($fetch = $db->fetch_array($sql))
		{
			$subscriptions[] = $fetch['Alias'];
		}
		
		return $subscriptions;
	}
	public function extract_url_subject($subject)
	{
		return str_replace(array('_', '-'), ' ', urldecode($subject));
	}
}

//@blog main
$blog = new blog;
$blog->url_rewrite();
$alias_init = new alias;
$db = new connection;
//blog uri
define('blog_uri', '/blog'); #const blog_uri = '/blog';

$tpl = new template('blog');

$status  = (isset($_GET['status'])) ? $_GET['status'] : 'index';
$entryid = (int) (isset($_GET['entry'])) ? val::post($_GET['entry']) : null;

//fetch five most recent entries
$sql = $db->query("SELECT * FROM `{$db->tb->Blog}` WHERE `Status`='visible' AND `Type`='entry' ORDER BY `ID` DESC LIMIT 5;");

$recent_entries = array();

while($fetch = $db->fetch_array($sql))
{
	$id = $fetch['ID'];
	$subject = val::encode($fetch['Subject']);
	$unix = $fetch['Unix'];
	
	$recent_entries[] = array(
	'id'      => $fetch['ID'],
	'Subject' => val::encode(val::str_trim($fetch['Subject'], 15))
	);
}

//fetch blog stats
$sql = $db->query("SELECT `Status`, COUNT(*) AS `Count` FROM `{$db->tb->Blog}` WHERE `Type`='entry' GROUP BY `Status` WITH ROLLUP;");

$entry_count = array(
'total'   => 0,
'visible' => 0,
'hidden'  => 0,
'private' => 0,
'archive' => 0
);

while($entry_fetch = $db->fetch_array($sql))
{
	$entry_fetch['Status'] = ($entry_fetch['Status'] == null) ? 'total' : $entry_fetch['Status'];
	$entry_count[$entry_fetch['Status']] += $entry_fetch['Count'];
}


//init categories
$categories = array('All');

//fetch categories
$sql = $db->query("SELECT DISTINCT BINARY `B`.`Category` AS `Category`, COUNT(`E`.`ID`) AS `EntryCount`
FROM `{$db->tb->Blog}` `B`
LEFT OUTER JOIN `{$db->tb->Blog}` `E` ON `B`.`Category`=`E`.`Category` AND `E`.`Type`='entry'
WHERE `B`.`Type`='entry' AND `B`.`Status`!='archive'
GROUP BY `B`.`ID`
ORDER BY `B`.`Category`;");

while($fetch = $db->fetch_array($sql))
{
	$categories[] = $fetch['Category'];
}

//fetch archive
if($entry_count['archive'] > 0)
{
	//fetch archived entries
	$sql = $db->query("SELECT `ID`, `Subject` FROM `{$db->tb->Blog}` WHERE `Type`='entry' AND `Status`='archive' ORDER BY `ID` DESC;");
	
	$archive = array();
	
	while($fetch = $db->fetch_array($sql))
	{
		$archive[] = array(
		'id'      => $fetch['ID'],
		'subject' => val::encode(val::str_trim($fetch['Subject'], 15))
		);
	}
}

$tpl->assign_vars(array(
'entry_count_total'   => $entry_count['total'],
'entry_count_visible' => $entry_count['visible'],
'entry_count_private' => $entry_count['private'],
'entry_count_hidden'  => $entry_count['hidden'],
'entry_count_archive' => $entry_count['archive'],
'recent_entries'      => $recent_entries,
'entry_categories'    => $categories,
'entry_archive'       => $archive
));

if($status == 'index')
{
	//search/category params
	$search = ((isset($_GET['search'])) ? forum::search(array("`B`.`Entry`", "`B`.`Subject`", "`B`.`Category`"), val::post($_GET['search'])) : null)
	.((isset($_GET['category']) && $_GET['category'] != 'All') ? (($_GET['category'] != 'archive') ? "AND BINARY `B`.`Category`='".val::post(urldecode($_GET['category']))."' AND `B`.`Status`!='archive'" : "AND `B`.`Status`='archive'") : "AND `B`.`Status`!='archive'");
	//generate sql depending on userlevel
	$query = (isset($alias_init->alias) && $alias_init->userlevel >= alias::user(alias::USR_ADMINISTRATOR)) ? null : ((isset($alias_init->alias)) ? "AND `B`.`Status`!='hidden'" : "AND `B`.`Status`!='hidden' AND `B`.`Status`!='private'");
	
	$page = new page;
	//.htaccess mod_rewrite delimiter
	$page->delimiter = '/page=';
	$page->default = 'last';
	$sql = $page->page("SELECT `B`.*, COUNT(`C`.`ID`) AS `CommentCount`, `A`.`Alias` AS `Author`
	FROM `{$db->tb->Blog}` `B`
	LEFT OUTER JOIN `{$db->tb->Blog}` `C` ON `B`.`ID`=`C`.`ID2` AND `C`.`Type`='comment'
	INNER JOIN `{$db->tb->Alias}` `A` ON `B`.`Author`=`A`.`ID`
	WHERE `B`.`Type`='entry' $query $search
	GROUP BY `B`.`ID`
	ORDER BY `B`.`ID` ASC;", 5, &$pagination);
	
	$entries = array();
	
	//for ws parsing
	$code = new code;
	
	while($fetch = $db->fetch_array($sql))
	{
		$id       = $fetch['ID'];
		$author   = $fetch['Author'];
		#$options = $fetch['Options'];
		$subject  = $blog->buffer_output(val::encode($fetch['Subject']));
		#$entry   = parse::parse($fetch['Entry'], $options, array('signature' => false));
		$entry    = val::str_trim($blog->buffer_output($code->parse_code_ws(val::encode(code::strip_code_tags($fetch['Entry'])))), 250);
		$unix     = val::unix($fetch['Unix']);
		$edit     = (!empty($fetch['Edit'])) ? ', last edited on '.val::unix($fetch['Edit']) : null;
		$category = val::encode($fetch['Category']);
		$comments = $fetch['CommentCount'];
		
		$entries[] = array(
		'id'        => $id,
		'author'    => $tpl->profile_link(val::encode($author), 'class="entry_info" style="color: '.$alias_init->alias_mod($author, 'lite:Mod').'"'),
		'subject'   => $subject,
		'entry'     => $entry,
		'date'      => $unix,
		'edit'      => $edit,
		'category'  => $category,
		'comments'  => $comments
		);
	}
	
	$tpl->assign_vars(array(
	'entries'          => $entries,
	'entry_pagination' => $pagination,
	'new_entry_link'   => ($alias_init->userlevel >= alias::user(alias::USR_ADMINISTRATOR)) ? true : false,
	'edit_link'        => ($alias_init->userlevel >= alias::user(alias::USR_ADMINISTRATOR)) ? true : false
	));
	$tpl->buffer($tpl->compile('blog_index'));
}
else if($status == 'view_entry')
{
	$sql = $db->query("SELECT `B`.*, `A`.`Alias` AS `Author`
	FROM `{$db->tb->Blog}` `B`
	INNER JOIN `{$db->tb->Alias}` `A` ON `B`.`Author`=`A`.`ID`
	WHERE ".((is_numeric($entryid)) ? "`B`.`ID`='$entryid'" : "BINARY `B`.`Subject`='".$blog->extract_url_subject($entryid)."'")." AND `B`.`Type`='entry';");
	
	$fetch = $db->fetch_array($sql);
	
	$id       = $fetch['ID'];
	$author   = $fetch['Author'];
	$options  = $fetch['Options'];
	$subject  = $blog->buffer_output(val::encode($fetch['Subject']));
	$entry    = $blog->buffer_output(parse::parse($fetch['Entry'], $options & ~parse::options_attach_signature));
	$unix     = val::unix($fetch['Unix']);
	$edit     = (!empty($fetch['Edit'])) ? ', last edited on '.val::unix($fetch['Edit']) : null;
	$category = val::encode($fetch['Category']);
	$status   = $fetch['Status'];
	
	//entry existence
	$blog->entry_existence('entry');
	//restrictions
	if($blog->locked($status))
	{
		secure::restrict(alias::user(alias::USR_ADMINISTRATOR));
	}
	if(!isset($alias_init->alias) && $status == 'private')
	{
		trigger_error(sprintf($tpl->error('BLOG_NO_PERMISSION'), 'entry'));
	}
	
	$entry = array(
	'id'        => $id,
	'author'    => $tpl->profile_link(val::encode($author), 'style="color: '.$alias_init->alias_mod($author, 'lite:Mod').'"'),
	'subject'   => $subject,
	'entry'     => $entry,
	'date'      => $unix,
	'edit'      => $edit,
	'category'  => $category,
	'edit_link' => ($alias_init->userlevel == alias::user(alias::USR_ADMINISTRATOR)) ? true : false
	);
	
	//fetch comments
	$page = new page;
	//.htaccess mod_rewrite delimiter
	$page->delimiter = '/page=';
	$page->display = 'numeric';
	$page->default = 'last';
	$sql = $page->page("SELECT `B`.*, `A`.`Alias` AS `Author`
	FROM `{$db->tb->Blog}` `B`
	INNER JOIN `{$db->tb->Alias}` `A` ON `B`.`Author`=`A`.`ID`
	WHERE `B`.`ID2`='$entryid' AND `B`.`Type`='comment' ORDER BY `B`.`ID` ASC;", 5, &$pagination);
	
	$comments = array();
	
	for($i = 0; $fetch = $db->fetch_array($sql); $i++)
	{
		$id      = $fetch['ID'];
		$author  = $fetch['Author'];
		$unix    = 'Posted on '.val::unix($fetch['Unix']);
		$edit    = (!empty($fetch['Edit'])) ? ', last edited on '.val::unix($fetch['Edit']) : null;
		$options = $fetch['Options'];
		$subject = val::encode($fetch['Subject']);
		$comment = $blog->buffer_output(parse::parse($fetch['Entry'], $options & ~parse::options_attach_signature));
		
		$comments[] = array(
		'id'        => $id,
		'author'    => $tpl->profile_link(val::encode($author), 'style="color: '.$alias_init->alias_mod($author, 'lite:Mod').'"'),
		'date'      => $unix,
		'edit'      => $edit,
		'subject'   => $subject,
		'comment'   => $comment,
		'class'     => ($i % 2 == 1) ? 'modulo' : null,
		'edit_link' => ($alias_init->alias == $author || $alias_init->userlevel == alias::user(alias::USR_ADMINISTRATOR)) ? true : false
		);
	}
	
	$tpl->assign_vars(array(
	'entry'              => $entry,
	'comments'           => $comments,
	'comment_pagination' => $pagination,
	'comment_link'       => (isset($alias_init->alias)) ? true : false
	));
	$tpl->buffer($tpl->compile('blog_view_entry'));
}
else if($status == 'comment')
{
	secure::secure();
	
	$sql = $db->query("SELECT * FROM `{$db->tb->Blog}` WHERE `ID`='$entryid' AND `Type`='entry';");
	$fetch = $db->fetch_array($sql);
	
	$id = $fetch['ID'];
	$status = val::encode($fetch['Status']);
	$subject = $fetch['Subject'];
	$category = $fetch['Category'];
	
	//entry existence
	$blog->entry_existence('entry');
	//restrictions
	if($blog->locked($status))
	{
		secure::restrict(alias::user(alias::USR_ADMINISTRATOR));
	}
	
	if(!form::submitted())
	{
		$tpl->assign_vars(array(
		'entry_subject' => $subject
		));
		$tpl->buffer($tpl->compile('blog_comment'));
	}
	else if(form::submitted())
	{
		//AJAX
		val::AJAX_decode();
		
		$subject = val::post($_POST['subject']);
		$comment = val::post($_POST['comment']);
		//options
		$options = parse::parse_options_compile();
		
		if(trim($subject) == null || trim($comment) == null)
		{
			trigger_error(sprintf($tpl->error('BLOG_NO_EMPTY'), 'comment'));
		}
		form::unique_check();
		
		$db->query("INSERT INTO `{$db->tb->Blog}` (`ID`, `ID2`, `Type`, `Category`, `Subject`, `Entry`, `Author`, `Unix`, `Edit`, `Status`, `Options`) VALUES (null, '$entryid', 'comment', '$category', '$subject', '$comment', '".val::post($alias_init->aliasid)."', UNIX_TIMESTAMP(), '0', 'visible', '$options');");
		
		foreach($blog->subscribe($id) as $value)
		{
			if($value != $alias_init->alias)
			{
				profile::message($value, $config_init->get_config('site_owner'), $config_init->get_config('email_prefix').'Blog Update', "$value,\n'.$alias_init->alias has posted comment $subject.\nCheck it out [url=".blog_uri."/$id#comments]here[/url].", parse::options_parse_code | parse::options_parse_code_ws, 'email');
			}
		}
		
		header::location(blog_uri.'/'.$entryid.'#comments');
	}
}
else if($status == 'edit_comment')
{
	$sql = $db->query("SELECT `B`.*, `A`.`Alias` AS `Author`
	FROM `{$db->tb->Blog}` `B`
	INNER JOIN `{$db->tb->Alias}` `A` ON `B`.`Author`=`A`.`ID`
	WHERE `B`.`ID`='$entryid' AND `B`.`Type`='comment';");
	
	$fetch = $db->fetch_array($sql);
	
	$id = $fetch['ID2'];
	$author = $fetch['Author'];
	$subject = val::encode($fetch['Subject']);
	$comment = val::encode($fetch['Entry']);
	$options = $fetch['Options'];
	
	//comment existence
	$blog->entry_existence('comment');
	//restrictions
	if($author != $alias_init->alias)
	{
		secure::secure(alias::user(alias::USR_ADMINISTRATOR));
	}
	
	if(!form::submitted())
	{
		$tpl->assign_vars(array(
		'comment_subject' => $subject,
		'comment_comment' => $comment,
		'parse_options'   => $options
		));
		$tpl->buffer($tpl->compile('blog_edit_comment'));
	}
	else if(form::submitted())
	{
		$edit = val::post($_POST['edit']);
		//options
		$options = parse::parse_options_compile();
		
		if(trim($edit) == null)
		{
			trigger_error(sprintf($tpl->error('BLOG_NO_EMPTY'), 'edit'));
		}
		form::unique_check();
		
		$db->query("UPDATE `{$db->tb->Blog}` SET `Entry`='$edit', `Edit`=UNIX_TIMESTAMP(), `Options`='$options' WHERE `ID`='$entryid' AND `Type`='comment';");
		
		header::location(blog_uri.'/'.$id.'#comments');
	}
}
else if($status == 'delete_comment')
{
	$sql = $db->query("SELECT `B`.*, `A`.`Alias` AS `Author`
	FROM `{$db->tb->Blog}` `B`
	INNER JOIN `{$db->tb->Alias}` `A` ON `B`.`Author`=`A`.`ID`
	WHERE `B`.`ID`='$entryid' AND `B`.`Type`='comment';");
	
	$fetch = $db->fetch_array($sql);
	
	$author = $fetch['Author'];
	$id2 = $fetch['ID2'];
	
	//comment existence
	$blog->entry_existence('comment');
	//restrictions
	if($author != $alias_init->alias)
	{
		secure::restrict(alias::user(alias::USR_ADMINISTRATOR));
	}
	
	if(!form::submitted())
	{
		$tpl->buffer($tpl->compile('blog_delete_comment'));
	}
	else if(form::submitted())
	{
		$db->query("DELETE FROM `{$db->tb->Blog}` WHERE `ID`='$entryid';");
		
		header::location(blog_uri.'/'.$id2.'#comments');
	}
}
else if($status == 'subscribe')
{
	secure::secure();
	
	//check for subscription to entire blog or a defined entry
	if(isset($entryid))
	{
		$sql = $db->query("SELECT * FROM `{$db->tb->Blog}` WHERE `ID`='$entryid' AND `Type` IN('entry', 'comment');");
		$fetch = $db->fetch_array($sql);
		
		$status = $fetch['Status'];
		
		//entry existence
		$blog->entry_existence('entry');
		//restrictions
		if($blog->locked($status))
		{
			secure::restrict(alias::user(alias::USR_MODERRATOR));
		}
		
		$subscription_type = 'entry';
		$subscription = 'blog:'.$entryid;
	}
	else
	{
		$subscription_type = 'blog';
		$subscription = 'blog:blog';
	}
	//check if user is subscibing or unsubscribing
	$sql = $db->query("SELECT null FROM `{$db->tb->Forum_Subscription}` WHERE `Alias`='".val::post($alias_init->alias)."' AND `Thread`='$subscription';");
	
	$subscription_type .= ($db->count_rows($sql) == 0) ? '_subscribe' : '_unsubscribe';
	
	if(!form::submitted())
	{
		$tpl->assign_vars(array(
		'subscription_type' => $subscription_type
		));
		$tpl->buffer($tpl->compile('blog_subscribe'));
	}
	else if(form::submitted())
	{
		//check if user is subscribing/unsubscribing
		if($db->count_rows($sql) == 0)
		{
			$db->query("INSERT INTO `{$db->tb->Forum_Subscription}` (`ID`, `Thread`, `Alias`) VALUES (null, '$subscription', '".val::post($alias_init->alias)."');");
		}
		else
		{
			$db->query("DELETE FROM `{$db->tb->Forum_Subscription}` WHERE `Alias`='".val::post($alias_init->alias)."' AND `Thread`='$subscription';");
		}
		
		header::location(blog_uri.'/'.$entryid);
	}
}
else if($status == 'entry')
{
	secure::secure();
	//restrictions
	secure::restrict(alias::user(alias::USR_ADMINISTRATOR));
	
	if(!form::submitted())
	{
		//fetch categories for parse::parse_options() extension
		$sql = $db->query("SELECT DISTINCT `Category` FROM `{$db->tb->Blog}` WHERE `Type`='entry' AND `Status`!='archive' ORDER BY `Category`;");
		
		$categories = array();
		
		while($fetch = $db->fetch_array($sql))
		{
			$categories[] = $fetch['Category'];
		}
		
		$tpl->assign_vars(array(
		'parse_options_categories' => $categories
		));
		$tpl->buffer($tpl->compile('blog_entry'));
	}
	else if(form::submitted())
	{
		$subject = val::post($_POST['subject']);
		$entry = val::post($_POST['entry']);
		$category = ($_POST['category'] == null) ? 'Uncategorised' : val::post($_POST['category']);
		$visibility = val::post($_POST['visibility']);
		//options
		$options = parse::parse_options_compile();
		
		if(trim($subject) == null || trim($entry) == null)
		{
			trigger_error(sprintf($tpl->error('BLOG_NO_EMPTY'), 'entry'));
		}
		form::unique_check();
		
		$db->query("INSERT INTO `{$db->tb->Blog}` (`ID`, `ID2`, `Type`, `Category`, `Subject`, `Entry`, `Author`, `Unix`, `Edit`, `Status`, `Options`) VALUES (null, LAST_INSERT_ID(), 'entry', '$category', '$subject', '$entry', '".val::post($alias_init->aliasid)."', UNIX_TIMESTAMP(), '0', '$visibility', '$options');");
		//define insert_id here
		$insert_id = $db->insert_id();
		//entry pointer hack
		$db->query("UPDATE `{$db->tb->Blog}` SET `ID2`='$insert_id' WHERE `ID`='$insert_id' AND `Type`='entry';");
		
		foreach($blog->subscribe() as $value)
		{
			if($value != $alias_init->alias)
			{
				profile::message($value, $config_init->get_config('site_owner'), $config_init->get_config('email_prefix').'Blog Update', "$value,\n$alias_init->alias has posted a new entry: ".stripslashes($subject).".\nCheck it out [url=".blog_uri."/$last_insert]here[/url].", parse::options_parse_code | parse::options_parse_code_ws, 'email');
			}
		}
		
		header::location(blog_uri.'/'.$insert_id);
	}
}
else if($status == 'edit')
{
	secure::secure();
	//restrictions
	secure::restrict(alias::user(alias::USR_ADMINISTRATOR));
	
	$sql = $db->query("SELECT * FROM `{$db->tb->Blog}` WHERE `ID`='$entryid' AND `Type`='entry';");
	$fetch = $db->fetch_array($sql);
	
	//entry existence
	$blog->entry_existence('entry');
	
	$subject = val::encode($fetch['Subject']);
	$entry = val::encode($fetch['Entry']);
	$options = $fetch['Options'];
	
	if(!form::submitted())
	{
		$tpl->assign_vars(array(
		'subject'                 => $subject,
		'entry'                   => $entry,
		'parse_options'           => $options
		));
		$tpl->buffer($tpl->compile('blog_edit'));
	}
	else if(form::submitted())
	{
		$subject = val::post($_POST['subject']);
		$edit = val::post($_POST['edit']);
		//options
		$options = parse::parse_options_compile();
		
		if(trim($subject) == null || trim($edit) == null)
		{
			trigger_error(sprintf($tpl->error('BLOG_NO_EMPTY'), 'edit'));
		}
		form::unique_check();
		
		$db->query("UPDATE `{$db->tb->Blog}` SET `Subject`='$subject', `Entry`='$edit', `Edit`=UNIX_TIMESTAMP(), `Options`='$options' WHERE `ID`='$entryid';");
		
		header::location(blog_uri.'/'.$entryid);
	}
}
else if($status == 'permission')
{
	secure::secure();
	//restrictions
	secure::restrict(alias::user(alias::USR_ADMINISTRATOR));
	
	$sql = $db->query("SELECT * FROM `{$db->tb->Blog}` WHERE `ID`='$entryid' AND `Type`='entry';");
	$fetch = $db->fetch_array($sql);
	
	$status = $fetch['Status'];
	$category = $fetch['Category'];
	
	//entry existence
	$blog->entry_existence('entry');
	
	if(!form::submitted())
	{
		$tpl->assign_vars(array(
		'permission' => $status,
		'category'   => $category
		));
		$tpl->buffer($tpl->compile('blog_permission'));
	}
	else if(form::submitted())
	{
		$permission = val::post($_POST['permission']);
		$category = val::post($_POST['category']);
		
		switch($permission)
		{
			default:
				$db->query("UPDATE `{$db->tb->Blog}` SET `Status`='$permission', `Category`='$category' WHERE `ID`='$entryid' AND `Type`='entry';");
			break;
			case 'delete':
				//delete entry & comments
				$db->query("DELETE FROM `{$db->tb->Blog}` WHERE (`ID`='$entryid' AND `Type`='entry') OR (`ID2`='$entryid' AND `Type`='comment');");
				//delete subscriptions
				$db->query("DELETE FROM `{$db->tb->Forum_Subscription}` WHERE `Thread`='blog:$entryid';");
			break;
		}
		
		header::location(blog_uri.(($permission != 'delete') ? '/'.$entryid : null));
	}
}
else
{
	echo '<div class="center">This page does not exist</div>';
}

$tpl->assign_vars(array(
'blog_main' => $tpl->buffer(),
));
return $tpl->compile('blog_main');
?>