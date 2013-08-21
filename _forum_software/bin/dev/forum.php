<?php
//dev
include 'devlib.php';
dev_init();

$db = new connection;
$alias_init = new alias;
$tpl = new template('forum');

$status   = (isset($_GET['status'])) ? $_GET['status'] : 'forum';
$forumid  = (int) (isset($_GET['forum'])) ? val::post($_GET['forum']) : null;
$threadid = (int) (isset($_GET['thread'])) ? val::post($_GET['thread']) : null;
$postid   = (int) (isset($_GET['post'])) ? val::post($_GET['post']) : null;
$_id      = (isset($postid)) ? $postid : ((isset($threadid)) ? $threadid : $forumid);

function restricted($status, $type = array('restricted', 'hidden'), $die = true)
{
	global $alias_init;
	
	list($status, $type) = array((array) $status, (array) $type);
	
	//var return used for multiple statuses so restriction is allowed to accumulate
	$return = false;
	
	//iterate each status (if array given)
	foreach($status as $value)
	{
		//iterate each restriction
		foreach(preg_split('/(\)|\;|\)\;)/', preg_replace('/\s/', null, $value)) as $value2)
		{
			//get restriction level/permissions
			list($restriction_type, $restriction) = explode('(', ($value2));
			
			//case for a check to see if the thread is closed/locked; void restriction level
			if(($type == array('closed', 'locked') && $die === false) && in_array($restriction_type, $type))
			{
				return true;
			}
			
			//iterate each type
			foreach($type as $value3)
			{
				//check if private restriction applies (posting), restricted or hidden
				if(in_array($value3, array('restricted', 'hidden', 'private', 'closed', 'locked', 'moderated')) && $restriction_type == $value3)
				{
					//default restriction
					$restriction = ($restriction == null) ? alias::user(alias::USR_MODERRATOR) : $restriction;
					
					//userlevel-based restriction
					if(is_numeric($restriction))
					{
						$return = ($die) ? secure::restrict($restriction) : (($alias_init->userlevel < $restriction) ? true : $return);
					}
					//alias/user based restriction
					else
					{
						//exctract allowed users
						$restriction = array_filter(explode(',', preg_replace('/\s/', null, $restriction)), create_function('$str', 'return !empty($str);')); #function($str){return !empty($str);}
						
						//alias/usergroup matches or wildcard
						$accept = (in_array($alias_init->alias, $restriction) || in_array('*', $restriction) || in_array('user:'.$alias_init->alias_mod($alias_init->alias, 'lite:Name'), $restriction)) ? true : false;
						//negative match
						$accept = (in_array('-'.$alias_init->alias, $restriction) || in_array('-user:'.$alias_init->alias_mod($alias_init->alias, 'lite:Name'), $restriction)) ? false : $accept;
						
						if(!$accept)
						{
							$return = ($die === true) ? secure::restrict(alias::user(alias::USR_ADMINISTRATOR)) : true;
						}
					}
				}
			}
		}
	}
	
	return $return;
}
function existence($sql, $type)
{
	global $db, $status;
	
	$message = sprintf('This %s does not exist.', $type);
	
	//void forum check on wildcard or thread existence check on forums
	if(($status == 'forum' && $GLOBALS['forumid'] == 0) || ($type == 'thread' && !isset($GLOBALS['threadid'])))
	{
		return;
	}
	
	//sql provided
	if(is_resource($sql) || $sql === false)
	{
		if($db->count_rows($sql) < 1)
		{
			trigger_error($message);
		}
	}
	//id provided
	else
	{
		global $forumid, $threadid, $postid;
		
		$id = $sql;
		
		switch($type)
		{
			case 'forum':
				$query = "`ID`='$id' AND `Type`='forum'";
			break;
			case 'thread':
				$query = "`Forum`='$forumid' AND `Thread`='$id' AND `Type`='thread'";
			break;
			case 'post':
				$query = "`Forum`='$forumid' AND `Thread`='$threadid' AND `ID`='$id' AND `Type`='post'";
			break;
		}
		
		$sql = $db->query("SELECT null FROM `{$db->tb->Forum}` WHERE $query;");
		
		if($db->count_rows($sql) < 1)
		{
			trigger_error($message);
		}
	}
}
function forum_info()
{
	//forum info
	global $db, $forumid;
	
	$sql = $db->query("SELECT * FROM `{$db->tb->Forum}` WHERE `ID`='$forumid' AND `Type`='forum';");
	
	//forum existence
	existence($sql, 'forum');
	
	return $db->fetch_array($sql);
}
function thread_info()
{
	//thread info
	global $db, $forumid, $threadid, $postid;
	
	$sql = $db->query("SELECT * FROM `{$db->tb->Forum}` WHERE `Forum`='$forumid' AND `ID`='$threadid' AND `Type`='thread';");
	
	//thread existence
	existence($sql, 'thread');
	
	return $db->fetch_array($sql);
}
function sticky($status)
{
	//extract status
	if(!strstr($status, ':'))
	{
		return array($status);
	}
	
	$origin_status = explode(':', $status);
	$sticky = array_pop($origin_status);
	$status = implode(':', $origin_status);
	
	return array($status, $sticky);
}
function check_post()
{
	global $forumid, $threadid, $db;
	
	//check for new posts
	$post_count = read_forum_data();
	
	$sql = $db->query("SELECT null FROM `{$db->tb->Forum}` WHERE `Forum`='$forumid' AND `Thread`='$threadid' AND `Type` IN('thread', 'post');");
	$post_count_new = $db->count_rows($sql);
	
	if($post_count_new > $post_count)
	{
		write_forum_data($post_count_new);
		
		trigger_error('While you were '.((!form::submitted()) ? 'reading' : 'typing').' someone else has posted...');
	}
}
class mod
{
	private
		$id,
		$userlevel_origin,
		$fetch,
		$t_mod = array(),
		$f_mod = array();
	
	public function __construct()
	{
		global $_id, $fetch, $alias_init;
		
		$this->id = $_id;
		$this->fetch = $fetch;
		$this->userlevel_origin = $alias_init->userlevel;
	}
	public function mod($id = 'MOD')
	{
		global $alias_init;
		$this->id = ($id == 'MOD') ? $this->id : $id;
		
		if(isset($this->id))
		{
			//check if user is a mod of thread/post
			$fetch = $this->get_mod();
			
			//check mod
			$mod = array_filter(explode(',', preg_replace('/\s/', null, $fetch['Mod'])), create_function('$str', 'return !empty($str);')); #function($str){return !empty($str);}
			
			//check if user is a mod of post's parent thread
			if($fetch['Type'] == 'post' && $id == 'MOD')
			{
				$this->id = $fetch['Thread'];
				
				if(!array_key_exists($this->id, $this->t_mod))
				{
					//force new query
					$this->fetch = null;
					$fetch = $this->get_mod();
					
					//cache
					$this->t_mod[$this->id] = array_filter(explode(',', preg_replace('/\s/', null, $fetch['Mod'])), create_function('$str', 'return !empty($str);')); #function($str){return !empty($str);}
				}
				
				$mod = array_merge($mod, $this->t_mod[$this->id]);
			}
			
			//check if user is a mod of post's parent forum
			if(in_array($fetch['Type'], array('thread', 'post')) && $id == 'MOD')
			{
				$this->id = $fetch['Forum'];
				
				if(!array_key_exists($this->id, $this->f_mod))
				{
					//force new query
					$this->fetch = null;
					$fetch = $this->get_mod();
					
					//cache
					$this->f_mod[$this->id] = array_filter(explode(',', preg_replace('/\s/', null, $fetch['Mod'])), create_function('$str', 'return !empty($str);')); #function($str){return !empty($str);}
				}
				
				$mod = array_merge($mod, $this->f_mod[$this->id]);
			}
			
			if(in_array($alias_init->alias, $mod) || in_array('user:'.$alias_init->alias_mod($alias_init->alias, 'lite:Name'), $mod))
			{
				//update userlevel origin
				$this->userlevel_origin = alias::user(alias::USR_MODERRATOR);
				//return mod userlevel
				$alias_init->userlevel = $this->userlevel_origin;
			}
			else
			{
				//return userlevel origin
				$alias_init->userlevel = $this->userlevel_origin;
			}
		}
		else
		{
			$alias_init->userlevel = $this->userlevel_origin;
		}
	}
	private function get_mod()
	{
		global $db;
		
		if($this->fetch == null)
		{
			$sql = $db->query("SELECT `Forum`, `Thread`, `Type`, `Mod` FROM `{$db->tb->Forum}` WHERE `ID`='$this->id' AND `Type` IN('forum', 'thread', 'post');");
			
			return $db->fetch_array($sql);
		}
		else
		{
			return $this->fetch;
		}
	}
}
function read_forum_data()
{
	global $db, $alias_init, $forumid, $threadid;
	
	if(isset($alias_init->alias))
	{
		//fetch tracking data from db
		$sql = $db->query("SELECT `PostCount` FROM `{$db->tb->Forum_Data}` WHERE `Alias`='".val::post($alias_init->alias)."' AND `Forum`='$forumid' AND `Thread`='$threadid';");
		$fetch = $db->fetch_array($sql);
		
		return $fetch['PostCount'];
	}
	else
	{
		//fetch tracking data from cookie
		global $config_init;
		
		if(isset($_COOKIE[$config_init->get_config('cookie_prefix').'forum']))
		{
			$forum_data = array();
			
			foreach(explode(';', $_COOKIE[$config_init->get_config('cookie_prefix').'forum']) as $value)
			{
				$data = explode(':', $value);
				$forum_data[$data[0]] = $data[1];
			}
			
			return (array_key_exists($threadid, $forum_data)) ? $forum_data[$threadid] : 0;
		}
		else
		{
			return false;
		}
	}
}
function write_forum_data($post_count)
{
	global $db, $alias_init, $forumid, $threadid;
	
	if(isset($alias_init->alias))
	{
		//write forum data to db
		$db->query("INSERT INTO `{$db->tb->Forum_Data}`
		(`ID`, `Alias`, `Forum`, `Thread`, `PostCount`)
		VALUES
		(null, '".val::post($alias_init->alias)."', '$forumid', '$threadid', '$post_count')
		ON DUPLICATE KEY
		UPDATE `PostCount`=VALUES(`PostCount`);");
	}
	else
	{
		//write forum data to cookie
		global $config_init;
		
		$forum_data = array();
		
		foreach(explode(';', $_COOKIE[$config_init->get_config('cookie_prefix').'forum']) as $value)
		{
			$data = explode(':', $value);
			$forum_data[$data[0]] = $data[1];
		}
		
		$forum_data[$threadid] = $post_count;
		
		$new_forum_data = array();
		
		foreach($forum_data as $key => $value)
		{
			$new_forum_data[] = $key.':'.$value;
		}
		
		header::setcookie($config_init->get_config('cookie_prefix').'forum', implode(';', $new_forum_data), 2 * time());
	}
}
function legacy()
{
	global $db, $forumid, $threadid, $postid, $status;
	
	$legacy = array('Forum' => val::encode($_SERVER['PHP_SELF']).'?action=forum');
	
	if(isset($_GET['forum']))
	{
		$sql = $db->query("SELECT * FROM `{$db->tb->Forum}` WHERE `ID`='$forumid' AND `Type`='forum';");
		$fetch = $db->fetch_array($sql);
		
		$subject = ($fetch['Subject'] != null) ? val::encode($fetch['Subject']) : (($forumid != 0) ? 'Non-Existent' : 'Global');
		
		//init forum legacy
		$forum_legacy = array(
		$subject => val::encode($_SERVER['PHP_SELF'].'?action=forum&forum='.$forumid)
		);
		
		//check for parent board(s)
		while($fetch['ID'] != $fetch['Forum'])
		{
			$sql = $db->query("SELECT * FROM `{$db->tb->Forum}` WHERE `ID`='{$fetch['Forum']}' AND `Type`='forum';");
			$fetch = $db->fetch_array($sql);
			
			$id = $fetch['ID'];
			$subject = ($fetch['Subject'] != null) ? val::str_trim(val::encode($fetch['Subject']), 25) : 'Non-Existent';
			
			$forum_legacy[$subject] = val::encode($_SERVER['PHP_SELF'].'?action=forum&forum='.$id);
		}
		
		//merge reversed forum_legacy
		$legacy = array_merge($legacy, array_reverse($forum_legacy));
	}
	if(isset($status))
	{
		if(isset($_GET['thread']))
		{
			$sql = $db->query("SELECT * FROM `{$db->tb->Forum}` WHERE `ID`='$threadid' AND `Forum`='$forumid' AND `Type`='thread';");
			$fetch = $db->fetch_array($sql);
			
			$subject = ($fetch['Subject'] != null) ? val::str_trim(val::encode($fetch['Subject']), 25) : 'Non-Existent';
			$id = $fetch['ID'];
			
			$legacy[$subject] = val::encode($_SERVER['PHP_SELF'].'?action=forum&forum='.$forumid.'&status=thread&thread='.$id.'#Post:'.$id);
		}
		if(isset($_GET['post']))
		{
			$sql = $db->query("SELECT * FROM `{$db->tb->Forum}` WHERE `ID`='$postid' AND `Thread`='$threadid' AND `Forum`='$forumid' AND `Type` IN('post', 'thread') /*for links for threads pointing to a postid*/;");
			$fetch = $db->fetch_array($sql);
			
			$subject = ($fetch['Subject'] != null) ? val::str_trim(val::encode($fetch['Subject']), 25) : 'Non-Existent';
			$id = $fetch['ID'];
			$thread = $fetch['Thread'];
			
			$legacy[$subject] = val::encode($_SERVER['PHP_SELF'].'?action=forum&forum='.$forumid.'&status=thread&thread='.$thread.'#Post:'.$id);
		}
		if(!in_array($status, array('forum', 'thread')))
		{
			$legacy[ucwords(str_replace('_', ' ', val::encode($status)))] = val::encode($_SERVER['REQUEST_URI']);
		}
	}
	if(isset($_GET['search']))
	{
		$legacy[val::encode($_GET['search'])] = val::encode($_SERVER['REQUEST_URI']);
	}
	if(isset($_GET['threads']))
	{
		$legacy[val::encode($_GET['threads'])] = val::encode($_SERVER['REQUEST_URI']);
	}
	
	return $legacy;
}
function child_forum($id, $include_forum = false)
{
	//fetch stats for child forums; (includes current forum if (bool) include_forum)
	global $db;
	
	$sql = $db->query("SELECT `F`.`ID`, COUNT(`P`.`ID`) AS `PostCount`, SUM(`P`.`Stats`) AS `Stats`
	FROM `{$db->tb->Forum}` `F`
	LEFT OUTER JOIN `{$db->tb->Forum}` `P` ON `F`.`ID`=`P`.`Forum` AND `P`.`Type` IN('thread', 'post')
	#INNER JOIN `{$db->tb->Forum}` `P` ON `F`.`ID`=`P`.`Forum` AND `P`.`Type` IN('forum', 'thread', 'post')
	WHERE `F`.`Type`='forum' ".(($include_forum === true) ? "AND `F`.`ID`='$id'" : "AND `F`.`Forum`='$id' AND `F`.`ID`!=`F`.`Forum`").
	"GROUP BY `F`.`ID`;");
	
	$children = array('Stats' => 0, 'PostCount' => 0, 'ChildrenCount' => 0);
	
	while($fetch = $db->fetch_array($sql))
	{
		$children['Stats'] += $fetch['Stats'];
		$children['PostCount'] += $fetch['PostCount'];
		
		//recursively add child forums' stats to parent forum's
		$sql2 = $db->query("SELECT `ID` FROM `{$db->tb->Forum}` WHERE `Forum`='{$fetch['ID']}' AND `ID`!=`Forum` AND `Type`='forum';");
		while($fetch2 = $db->fetch_array($sql2))
		{
			$children_children = child_forum($fetch2['ID'], true);
			
			$children['Stats'] += $children_children['Stats'];
			$children['PostCount'] += $children_children['PostCount'];
			$children['ChildrenCount'] += $children_children['ChildrenCount'];
		}
		
		$children['ChildrenCount']++;
	}
	
	return $children;
}
function f_footer()
{
	global $forumid, $config_init, $alias_init, $db, $threadid;
	
	$footer = new stdClass;
	
	//viewing
	$stat = new stat;
	$stats = $stat->fetch_stats('%action=forum%'.((isset($forumid)) ? '%forum='.$forumid.'%' : null));
	
	foreach($stats['aliases'] as &$value)
	{
		$value = template::profile_link(val::encode($value), 'style="color: '.$alias_init->alias_mod($value, 'lite:Mod').';"');
	}
	
	$footer->viewing = $stats;
	
	//active
	if(isset($threadid))
	{
		$sql = $db->query("SELECT `Author`, COUNT(`ID`) AS `PostCount` FROM `{$db->tb->Forum}` WHERE `Forum`='$forumid' AND `Thread`='$threadid' AND `Type` IN('thread', 'post') GROUP BY `Author` ORDER BY `PostCount` DESC;");
		
		$active = array();
		
		while($fetch = $db->fetch_array($sql))
		{
			$author = $fetch['Author'];
			
			$active[] = template::profile_link(val::encode($author), 'style="color: '.$alias_init->alias_mod($author, 'lite:Mod').';"').'<span class="f_legacy" style="font-size: 0.9em;">('.val::number_format($fetch['PostCount']).')</span>';
		}
		
		$footer->active = $active;
	}
	
	//posts
	$query = (isset($forumid)) ? "WHERE `Forum`='$forumid'".((isset($threadid)) ? " AND `Thread`='$threadid'" : null) : null;
	$sql = $db->query("SELECT `Type`, COUNT(`ID`) AS `PostCount` FROM `{$db->tb->Forum}` $query GROUP BY `Type` WITH ROLLUP;");
	
	//init posts
	$posts = array(
	'forum'  => 0,
	'thread' => 0,
	'post'   => 0
	);
	
	//increment posts
	while($fetch = $db->fetch_array($sql))
	{
		$posts[$fetch['Type']] += $fetch['PostCount'];
	}
	//fix posts to include threads
	$posts['post'] += $posts['thread'];
	
	//get child forum stats
	$forum_children = (isset($forumid) && !isset($threadid)) ? child_forum($forumid) : null;
	
	$footer->posts = array(
	'post'   => val::number_format($posts['post']),
	'thread' => val::number_format($posts['thread']),
	'forum'  => val::number_format($posts['forum'])
	);
	
	return $footer;
}

//init forum
//check user's mod status
$mod_init = new mod;
$mod_init->mod();

//assign template vars
$tpl->assign_vars(array(
'forumid'      => $forumid,
'threadid'     => $threadid,
'postid'       => $postid,
'forum_legacy' => legacy()
));
//forum footer
if(in_array($status, array('forum', 'thread')))
{
	$tpl->assign_vars(array(
	'forum_announcement' => parse::parse($config_init->get_config('forum_announcement'), parse::options_parse_code | parse::options_parse_smiley),
	'forum_footer'       => f_footer()
	));
}
//post|search|subscribe links
if(($status == 'forum' && isset($forumid)) || $status == 'thread')
{
	$tpl->assign_vars(array(
	'post_link'           => true,
	'search_link'         => true,
	'subscribe_link'      => true,
	'subscribe_link_type' => ($db->count_rows($db->query("SELECT null
	 FROM `{$db->tb->Forum_Subscription}`
	 WHERE `Alias`='".val::post($alias_init->alias)."' AND `Thread`='".((isset($threadid)) ? $threadid : $forumid)."';")) == 0) ? 'subscribe' : 'unsubscribe'
	 ));
}
//post forum link
else if($status == 'forum' && !isset($forumid) && $alias_init->userlevel >= alias::user(alias::USR_MODERRATOR))
{
	$tpl->assign_vars(array(
	'post_link' => true
	));
}

if($status == 'forum')
{
	//shell forum query
	$page = new page;
	
	//board query
	if(!isset($forumid))
	{
		$sql = $db->query("SELECT `F`.*, COUNT(`P`.`ID`) AS `PostCount`, ".((isset($alias_init->alias)) ? "(SELECT SUM(`D`.`PostCount`) FROM `{$db->tb->Forum_Data}` `D` WHERE `D`.`Alias`='".val::post($alias_init->alias)."' AND `F`.`ID`=`D`.`Forum`) AS `Forum_Data_PostCount`, " : null)."SUM(`P`.`Stats`) AS `Stats`, MAX(`P`.`ID`) AS `LastPost`
		FROM `{$db->tb->Forum}` `F`
		LEFT OUTER JOIN `{$db->tb->Forum}` `P` ON `F`.`ID`=`P`.`Forum` AND `P`.`Type` IN('thread', 'post')
		#INNER JOIN `{$db->tb->Forum}` `P` ON (`F`.`ID`=`P`.`Forum` AND `P`.`Type` IN('thread', 'post')) OR (`F`.`ID`=`P`.`ID` AND `P`.`Type`='forum')
		WHERE `F`.`Type`='forum' AND `F`.`ID`=`F`.`Forum`
		GROUP BY `F`.`ID`
		ORDER BY SUBSTRING_INDEX(`F`.`Status`, ':', -1);");
	}
	//forum query
	else if(isset($forumid))
	{
		//search params
		$search = array(
		'SELECT'    => array(),
		'WHERE'     => ($forumid != 0) ? "WHERE (`F`.`Forum`='$forumid' OR SUBSTRING_INDEX(`F`.`Status`, ':', -1)=2)" : "WHERE `F`.`Forum`=`F`.`Forum`",
		'WHERE2'    => ($forumid != 0) ? "AND ((`F`.`Type`='forum' AND `F`.`ID`!=`F`.`Forum`) OR `F`.`Type`='thread')" : "AND `F`.`Type`='thread'",
		'JOIN'      => null,
		'JOIN_TYPE' => 'LEFT', #'INNER',
		'HAVING'    => null
		);
		
		if(isset($_GET['search']))
		{
			$search['WHERE'] .= forum::search(array("`F`.`Subject`", "`F`.`Post`", "`P`.`Subject`", "`P`.`Post`"), val::post($_GET['search']));
			$search['JOIN_TYPE'] = 'LEFT';
			
			if($_GET['author'] != null)
			{
				$search['WHERE2'] .= "AND `F`.`Author`='".val::post($_GET['author'])."'";
			}
		}
		
		switch((isset($_GET['order'])) ? $_GET['order'] : null)
		{
			case 'Stats':
				$order = "`F`.`Stats` DESC";
			break;
			case 'Posts':
				$order = "`PostCount` DESC";
			break;
			case 'ID':
				$order = "`F`.`ID` DESC";
			break;
			default:
				$order = "`LastPost` DESC";
			break;
		}
		
		if(isset($_GET['threads']))
		{
			switch($_GET['threads'])
			{
				case 'read':
					$search['HAVING'] .= (($search['HAVING'] == null) ? "HAVING" : "AND")."`PostCount`<=`Forum_Data_PostCount`";
				break;
				case 'unread':
					$search['HAVING'] .= (($search['HAVING'] == null) ? "HAVING" : "AND")."`Forum_Data_PostCount` IS null";
				break;
				case 'created':
					$search['WHERE2'] .= "AND `F`.`Author`='".val::post($alias_init->alias)."'";
				break;
				case 'posted':
					#$search['JOIN'] = "INNER JOIN `{$db->tb->Forum}` `P2` ON `F`.`Forum`=`P2`.`Forum` AND `P`.`Thread`=`P2`.`Thread` AND `P2`.`Type` IN('thread', 'post') AND `P2`.`Author`='".val::post($alias_init->alias)."'";
					$search['SELECT'][] = "(SELECT `P2`.`ID` FROM `{$db->tb->Forum}` `P2` WHERE `F`.`Forum`=`P2`.`Forum` AND `P`.`Thread`=`P2`.`Thread` AND `P2`.`Type` IN('thread', 'post') AND `P2`.`Author`='".val::post($alias_init->alias)."' LIMIT 1) AS `Posted`";
					$search['HAVING'] .= (($search['HAVING'] == null) ? "HAVING" : "AND")."`Posted`>0";
				break;
				case 'replied':
					$search['HAVING'] = "HAVING `PostCount`>`Forum_Data_PostCount`";
				break;
			}
		}
		
		$sql = $page->page("SELECT `F`.*, COUNT(`P`.`ID`) AS `PostCount`, IF(`F`.`Type`='forum', (SELECT SUM(`D`.`PostCount`) FROM `{$db->tb->Forum_Data}` `D` WHERE `D`.`Alias`='".val::post($alias_init->alias)."' AND `F`.`ID`=`D`.`Forum`), (SELECT `D`.`PostCount` FROM `{$db->tb->Forum_Data}` `D` WHERE `D`.`Alias`='".val::post($alias_init->alias)."' AND `F`.`Forum`=`D`.`Forum` AND `F`.`ID`=`D`.`Thread`)) AS `Forum_Data_PostCount`, IF(`F`.`Type`='forum', SUM(`P`.`Stats`), `F`.`Stats`) AS `Stats`, MAX(`P`.`ID`) AS `LastPost` ".((count($search['SELECT']) > 0) ? ", " : null).implode(", ", $search['SELECT']).
		"FROM `{$db->tb->Forum}` `F`
		{$search['JOIN_TYPE']} JOIN `{$db->tb->Forum}` `P` ON IF(`F`.`Type`='forum', `F`.`ID`=`P`.`Forum` /*OR `F`.`ID`!=`P`.`Forum`*/, `F`.`ID`=`P`.`Thread`/*)*/ AND `P`.`Type` IN('thread', 'post'))
		{$search['JOIN']}
		{$search['WHERE']}{$search['WHERE2']}
		GROUP BY `F`.`ID`
		{$search['HAVING']}
		ORDER BY IF(`F`.`Type`='forum', 0, 1), IF(SUBSTRING_INDEX(`F`.`Status`, ':', -1)>=1, 1, 2), $order;", 25, &$pagination);
		
		//forum info/restrictions/existence
		$fetch = forum_info();
		$f_status = reset(sticky($fetch['Status']));
		restricted($f_status);
		//check post link type
		if(restricted($f_status, array('closed', 'locked'), false))
		{
			$tpl->assign_vars(array(
			'post_link_type' => 'closed'
			));
		}
	}
	
	//forum
	$forum = array();
	
	while($fetch = $db->fetch_array($sql))
	{
		$id = $fetch['ID'];
		$type = $fetch['Type'];
		$author = $fetch['Author'];
		$subject = val::encode($fetch['Subject']);
		//for global stickies
		$forum_id = $fetch['Forum'];
		//for forums
		$forum_subject = null;
		$unix = val::unix($fetch['Unix']);
		$stats = $fetch['Stats'];
		$post_count = $fetch['PostCount'];
		list($status, $sticky) = sticky($fetch['Status']);
		
		//restrictions: continue hidden threads
		if(restricted($status, 'hidden', false))
		{
			//hidden forums/threads
			if($config_init->get_config('hidden_post_message') != null && $type != 'forum')
			{
				$subject = $config_init->get_config('hidden_post_message');
			}
			else
			{
				continue;
			}
		}
		
		//get last post; join for forums to get post count
		$sql2 = $db->query("SELECT `F`.`Type`, `F`.`Forum`, `F`.`Thread`, `F`.`ID`, `F`.`Author`, `F`.`Subject`, `F`.`Unix` ".(($type == 'forum') ? ", COUNT(`P`.`ID`) AS `PostCount`" : null).
		"FROM `{$db->tb->Forum}` `F` "
		.(($type == 'forum') ? "{$search['JOIN_TYPE']} JOIN `{$db->tb->Forum}` `P` ON `F`.`Thread`=`P`.`Thread` AND `P`.`Type` IN('thread', 'post')" : null).
		"WHERE `F`.`ID`='{$fetch['LastPost']}' AND `F`.`Type` IN('forum', 'thread', 'post')"
		.(($type == 'forum') ? "GROUP BY `F`.`ID`" : null).";");
		$fetch2 = $db->fetch_array($sql2);
		
		$last_post = array(
		'id'      => $fetch2['ID'],
		'type'    => $fetch2['Type'],
		'forum'   => $fetch2['Forum'],
		'thread'  => $fetch2['Thread'],
		'subject' => val::str_trim($fetch2['Subject'], 25),
		'author'  => template::profile_link(val::encode($fetch2['Author']), 'style="color: '.$alias_init->alias_mod($fetch2['Author'], 'lite:Mod').';"'),
		'date'    => val::unix($fetch2['Unix'], 'compressed'),
		'page'    => ceil((($type == 'forum') ? $fetch2['PostCount'] : $post_count) / 25/*thread page*/)
		);
		
		//forum params
		if(!isset($forumid) || $type == 'forum')
		{
			//define forum subject(s)
			$options = $fetch['Options'];
			
			$forum_subject = parse::parse($fetch['Post'], $options & ~parse::options_attach_signature);
			
			//include child forums' stats in parent's
			$fetch2 = child_forum($id);
			$post_count += $fetch2['PostCount'];
			$stats += $fetch2['Stats'];
			
			//fetch child forums for subject
			$sql2 = $db->query("SELECT * FROM `{$db->tb->Forum}` WHERE `Forum`='$id' AND `Type`='Forum' AND `ID`!=`Forum` ORDER BY SUBSTRING_INDEX(`Status`, ':', -1);");
			
			$forum_children = array();
			
			while($fetch2 = $db->fetch_array($sql2))
			{
				//generate forum children (void hidden forums)
				if(!restricted(reset(sticky($fetch2['Status'])), 'hidden', false))
				{
					$forum_children[$fetch2['ID']] = val::encode($fetch2['Subject']);
				}
			}
			
			if($fetch['Mod'] != null)
			{
				$forum_mod = array_filter(explode(',', preg_replace('/\s/', null, $fetch['Mod'])), create_function('$str', 'return !empty($str);')); #function($str){return !empty($str);}
				
				foreach($forum_mod as $key => $value)
				{
					if(stristr($value, 'user:'))
					{
						//usergroup
						$value = end(explode(':', $value));
						$forum_mod[$key] = array(
						'color' => val::encode($alias_init->user[$value]['Mod']),
						'mod'   => $value
						);
					}
					else
					{
						//alias
						$forum_mod[$key] = template::profile_link(val::encode($value), 'style="color: '.$alias_init->user['Moderator']['Mod'].';"');
					}
				}
			}
			else
			{
				$forum_mod = null;
			}
		}
		
		$forum[] = array(
		'id'             => $id,
		'forum'          => $forum_id,
		'type'           => $type,
		'author'         => ($type != 'forum') ? template::profile_link(val::encode($author), 'style="color: '.$alias_init->alias_mod($author, 'lite:Mod').';"') : null,
		'subject'        => ($type == 'forum') ? $subject : val::str_trim($subject, 65),
		'forum_subject'  => (isset($forum_subject)) ? $forum_subject : null,
		'forum_children' => (isset($forum_children)) ? $forum_children : null,
		'forum_mod'      => (isset($forum_mod) && $type == 'forum') ? $forum_mod : null,
		'date'           => $unix,
		'stats'          => val::number_format($stats),
		'post_count'     => val::number_format($post_count),
		'last_post'      => $last_post,
		'closed'         => (restricted($status, array('closed', 'locked'), false) || restricted($status, array('hidden', 'restricted', 'private'), false)) ? true : false,
		'sticky'         => ($type != 'forum' && in_array($sticky, array(1, 2))) ? true : false,
		'read'           => ((int) $fetch['PostCount'] /*void child board's post count*/ <= (int) $fetch['Forum_Data_PostCount']) ? true : false,
		'pages'          => ceil($fetch['PostCount'] / 25)
		);
	}
	
	$tpl->assign_vars(array(
	'pagination'        => (isset($pagination)) ? $pagination : null,
	'forum'             => $forum,
	'search_link'       => true,
	'forum_search_link' => true
	));
	$tpl->buffer($tpl->compile('forum_index'));
}
else if($status == 'thread')
{
	//init thread
	//forum info
	$fetch = forum_info();
	$f_status = reset(sticky($fetch['Status']));
	//for local moderators
	$f_mod = array_filter(explode(',', preg_replace('/\s/', null, $fetch['Mod'])), create_function('$str', 'return !empty($str);')); #function($str){return !empty($str);}
	
	//thread info (alias of thread_info(), including post count (having post count > 0 for thread existence))
	$sql = $db->query("SELECT `F`.*, COUNT(`P`.`ID`) AS `PostCount`
	FROM `{$db->tb->Forum}` `F`
	INNER JOIN `{$db->tb->Forum}` `P` ON (`F`.`ID`=`P`.`Thread` OR `F`.`ID`=`P`.`ID`) AND `P`.`Forum`='$forumid'
	WHERE `F`.`Forum`='$forumid' AND `F`.`ID`='$threadid' AND `F`.`Type`='thread'
	HAVING `PostCount`>0;");
	
	$fetch = $db->fetch_array($sql);
	
	$subject = val::encode($fetch['Subject']);
	list($t_status, $sticky) = sticky($fetch['Status']);
	$t_subject = val::encode($fetch['Subject']);
	//post_count for forum_data
	$post_count = $fetch['PostCount'];
	
	//thread existence
	existence($sql, 'thread');
	//restrictions
	restricted(array($f_status, $t_status));
	
	//increment stats
	if(read_forum_data() < $post_count && (isset($alias_init->alias) || read_forum_data() !== false))
	{
		$db->query("UPDATE `{$db->tb->Forum}` SET `Stats`=(`Stats`+1) WHERE `ID`='$threadid' AND `Type`='thread';");
	}
	//write forum data
	write_forum_data($post_count);
	
	//more restrictions... (reset post link for private/closed/locked status)
	if(restricted(array($f_status, $t_status), array('closed', 'locked'), false))
	{
		$tpl->assign_vars(array(
		'post_link_type' => 'closed'
		));
	}
	
	$page = new page;
	//shell thread query
	//search params
	$search = (isset($_GET['search'])) ? forum::search(array("`Subject`", "`Post`"), val::post($_GET['search'])).(($_GET['author'] != null) ? " AND `Author`='".val::post($_GET['author'])."'" : null) : null;
	$sql = $page->page("SELECT * FROM `{$db->tb->Forum}` WHERE `Forum`='$forumid' AND `Thread`='$threadid' AND `Type` IN('thread', 'post') $search ORDER BY IF(`Type`='thread', 0, IF(SUBSTRING_INDEX(`Status`, ':', -1)>=1, 1, 2)), `ID` ASC;", 25, &$pagination);
	
	//thread
	$thread = array();
	
	while($fetch = $db->fetch_array($sql))
	{
		$id = $fetch['ID'];
		$type = $fetch['Type'];
		$author = $fetch['Author'];
		$date = val::unix($fetch['Unix']);
		$edit = array('date' => val::unix(reset(explode(':', reset(explode(';', $fetch['Edit'])))), 'full'), 'count' => val::number_format(end(explode(':', reset(explode(';', $fetch['Edit']))))), 'author' => template::profile_link(val::encode(end(explode(';', $fetch['Edit']))), 'style="color: '.$alias_init->alias_mod(end(explode(';', $fetch['Edit'])), 'lite:Mod').'"'));
		$options = $fetch['Options'];
		$subject = val::encode($fetch['Subject']);
		$post = parse::parse($fetch['Post'], $options);
		list($status, $sticky) = sticky($fetch['Status']);
		
		//restrictions
		//continue hidden posts
		if(restricted($status, 'hidden', false))
		{
			//hidden posts hack
			if($config_init->get_config('hidden_post_message') != null)
			{
				$post = $config_init->get_config('hidden_post_message');
			}
			else
			{
				continue;
			}
		}
		
		//check mod against posts
		$mod_init->mod($id);
		
		//define permissions
		$permission = array(
		'author'    => (($alias_init->alias == $author || $alias_init->userlevel >= alias::user(alias::USR_MODERRATOR)) && !restricted(array($f_status, $status), array('private', 'closed', 'locked'), false)) ? true : false,
		'moderator' => ($alias_init->userlevel >= alias::user(alias::USR_MODERRATOR)) ? true : false,
		'closed'    => false
		);
		
		//reset permissions for closed/locked status
		if(restricted(array($f_status, $t_status, $status), array('closed', 'locked'), false))
		{
			$permission['closed'] = true;
		}
		//destroy permissions for restricted status
		if(restricted(array($f_status, $t_status, $status), array('hidden', 'restricted', 'private', 'closed', 'locked'), false))
		{
			$permission = array();
		}
		
		$thread[] = array(
		'id'          => $id,
		'type'        => $type,
		'author'      => template::profile_link(val::encode($author), 'style="color: '.$alias_init->alias_mod($author, 'lite:Mod').';"').$alias_init->alias_mod($author, 'full_forum', (in_array($author, $f_mod)) ? true : false),
		'subject'     => $subject,
		'post'        => $post,
		'edit'        => $edit,
		'date'        => $date,
		'permissions' => $permission
		);
	}
	
	$tpl->assign_vars(array(
	'thread'     => $thread,
	't_subject'  => $t_subject,
	'pagination' => $pagination
	));
	$tpl->buffer($tpl->compile('forum_thread'));
}
else if($status == 'search')
{
	$tpl->buffer($tpl->compile('forum_search'));
}
else if($status == 'post')
{
	secure::secure();
	//check if user is posting a new forum, new thread or in a defined thread
	define('post_type', (!isset($forumid)) ? 'FORUM' : ((!isset($threadid)) ? 'THREAD' : 'POST')); #const post_type = (!isset($forumid)) ? 'FORUM' : ((!isset($threadid)) ? 'THREAD' : 'POST');
	//assign post type to template
	$tpl->assign_vars(array(
	'post_type' => post_type
	));
	
	switch(post_type)
	{
		//forum posting
		case 'FORUM':
			secure::restrict(alias::user(alias::USR_MODERRATOR));
			
			if(!form::submitted())
			{
				//parse::parse_options() extension (forum permissions; alias of forum|thread|post_permission)
				$permission = array();
				if($alias_init->userlevel >= alias::user(alias::USR_MODERRATOR))
				{
					//compile permissions for parse extension
					foreach($alias_init->user as $value)
					{
						$permission[] = array(
						'name'      => val::encode($value['Name']),
						'userlevel' => $value['Userlevel'],
						'color'     => val::encode($value['Mod'])
						);
					}
				}
				
				$tpl->assign_vars(array(
				'permissions' => $permission
				));
				$tpl->buffer($tpl->compile('forum_post'));
			}
			else if(form::submitted())
			{
				$subject = val::post($_POST['subject']);
				$forum = val::post($_POST['forum']);
				
				if(trim($subject) == null || trim($forum) == null)
				{
					trigger_error('You cannot post an empty forum.');
				}
				form::unique_check();
				
				//permissions
				$status = val::post($_POST['permission'].':'.(int) $_POST['sticky']);
				//mods
				$mod = $_POST['mod'];
				//options
				$options = parse::parse_options_compile();
				
				$db->query("INSERT INTO `{$db->tb->Forum}` (`ID`, `Type`, `Forum`, `Thread`, `Subject`, `Post`, `Author`, `Unix`, `Edit`, `Stats`, `Status`, `Mod`, `Options`) VALUES (null, 'forum', LAST_INSERT_ID(), '0', '$subject', '$forum', '".val::post($alias_init->alias)."', UNIX_TIMESTAMP(), '0:0;', '0', '$status', '$mod', '$options');");
				//define insert_id here (_insert_id used for forum pointer for parent_forum)
				$insert_id = $_insert_id = $db->insert_id();
				
				//forum pointer hack (including check for parent forum)
				if(!empty($_POST['parent_forum']))
				{
					//point _insert_id to the new forum
					$_insert_id = (int) val::post($_POST['parent_forum']);
					//forum existence
					existence($_insert_id, 'forum');
				}
				
				$db->query("UPDATE `{$db->tb->Forum}` SET `Forum`='$_insert_id' WHERE `ID`='$insert_id' AND `Type`='forum';");
				
				header::location($_SERVER['PHP_SELF'].'?action=forum&forum='.$insert_id);
			}
		break;
		//thread posting
		case 'THREAD':
			//forum info
			$fetch = forum_info();
			$f_status = reset(sticky($fetch['Status']));
			
			//restrictions
			restricted($f_status, array('hidden', 'restricted', 'private', 'closed', 'locked'));
			
			if(!form::submitted())
			{
				//parse::parse_options() extension (thread permissions; alias of forum|thread|post_permission)
				$permission = array();
				if($alias_init->userlevel >= alias::user(alias::USR_MODERRATOR))
				{
					//compile permissions for parse extension
					foreach($alias_init->user as $value)
					{
						$permission[] = array(
						'name'      => val::encode($value['Name']),
						'userlevel' => $value['Userlevel'],
						'color'     => val::encode($value['Mod'])
						);
					}
				}
				
				$tpl->assign_vars(array(
				'permissions' => $permission
				));
				$tpl->buffer($tpl->compile('forum_post'));
			}
			else if(form::submitted())
			{
				$subject = val::post($_POST['subject']);
				$thread = val::post($_POST['thread']);
				
				if(trim($subject) == null || trim($thread) == null)
				{
					trigger_error('You cannot post an empty thread.');
				}
				form::unique_check();
				
				//parse::parse_options() extension (permissions)
				//permissions
				$permission = $_POST['permission'];
				//sticky
				$sticky = (int) $_POST['sticky'];
				//mods
				$mod = $_POST['mod'];
				
				//restore disallowed values
				//check for hidden thread
				$permission = ($alias_init->userlevel < alias::user(alias::USR_MODERRATOR) && (profile::modded_alias($alias_init->alias) || restricted($f_status, 'moderated', false))) ? 'hidden('.alias::user(alias::USR_MODERRATOR).')' : $permission;
				if($alias_init->userlevel < alias::user(alias::USR_MODERRATOR))
				{
					list($sticky, $mod) = array((int) null, (unset) null);
				}
				
				//status
				$status = val::post($permission.':'.$sticky);
				//options
				$options = parse::parse_options_compile();
				
				$db->query("INSERT INTO `{$db->tb->Forum}` (`ID`, `Type`, `Forum`, `Thread`, `Subject`, `Post`, `Author`, `Unix`, `Edit`, `Stats`, `Status`, `Mod`, `Options`) VALUES (null, 'thread', '$forumid', LAST_INSERT_ID(), '$subject', '$thread', '".val::post($alias_init->alias)."', UNIX_TIMESTAMP(), '0:0;', '0', '$status', '$mod', '$options');");
				//define insert_id here
				$insert_id = $db->insert_id();
				
				//thread pointer hack
				$db->query("UPDATE `{$db->tb->Forum}` SET `Thread`='$insert_id' WHERE `ID`='$insert_id' AND `Type`='thread' AND `Forum`='$forumid';");
				
				//check for hidden thread: email
				if($status == 'hidden('.alias::user(alias::USR_MODERRATOR).'):0')
				{
					profile::message($config_init->get_config('site_owner'), $config_init->get_config('site_owner'), $config_init->get_config('email_prefix').'Moderation Post', $config_init->get_config('site_owner').",\n$alias_init->alias has posted thread: ".stripslashes($subject)."\nThis post must be moderated.\nModerate it [url={$_SERVER['PHP_SELF']}?action=forum&forum=$forumid&status=thread_permission&thread=$insert_id]here[/url].", parse::options_parse_code | parse::options_parse_code_ws, 'email');
				}
				
				//subscriptions
				$sql = $db->query("SELECT DISTINCT `Alias` FROM `{$db->tb->Forum_Subscription}` WHERE `Thread`='$forumid' AND `Alias`!='".val::post($alias_init->alias)."';");
				while($fetch = $db->fetch_array($sql))
				{
					profile::message($fetch['Alias'], $config_init->get_config('site_owner'), $config_init->get_config('email_prefix').'Forum Update', "{$fetch['Alias']},\n$alias_init->alias has posted thread: ".stripslashes($subject)."\nView it [url={$_SERVER['PHP_SELF']}?action=forum&forum=$forumid&status=thread&thread=$insert_id#Post:$insert_id]here[/url].", parse::options_parse_code | parse::options_parse_code_ws, 'email');
				}
				
				header::location($_SERVER['PHP_SELF'].'?action=forum&forum='.$forumid.'&status=thread&thread='.$insert_id.'#Post:'.$insert_id);
			}
		break;
		//post posting
		case 'POST':
			//forum info
			$fetch = forum_info();
			$f_status = reset(sticky($fetch['Status']));
			//thread info
			$fetch = thread_info();
			$t_status = reset(sticky($fetch['Status']));
			$t_subject = val::encode($fetch['Subject']);
			
			//restrictions
			restricted(array($f_status, $t_status), array('hidden', 'restricted', 'private', 'closed', 'locked'));
			
			if(!form::submitted())
			{
				check_post();
				
				//reply
				if(isset($_GET['reply']))
				{
					$reply = array_filter(explode(',', preg_replace('/\s/', null, val::post($_GET['reply']))), create_function('$str', 'return !empty($str);')); #function($str){return !empty($str);}
					
					$t_subject = $post = array();
					
					foreach($reply as $value)
					{
						$reply_id = (int) $value;
						
						$sql = $db->query("SELECT * FROM `{$db->tb->Forum}` WHERE `Forum`='$forumid' AND `Thread`='$threadid' AND `ID`='$reply_id' AND `Type` IN('thread', 'post');");
						$fetch = $db->fetch_array($sql);
						
						$reply_forum  = $fetch['Forum'];
						$reply_thread = $fetch['Thread'];
						$reply_author = val::encode($fetch['Author']);
						$reply_post   = val::encode($fetch['Post']);
						$reply_status = reset(sticky($fetch['Status']));
						
						//void hidden posts
						if(!restricted($status, 'hidden', false))
						{
							//replace subject with reply's subject
							$t_subject[] = val::encode($fetch['Subject']);
							//compile quote
							$post[] = '[quote='.$reply_author.' url=/index.php?action=forum&amp;forum='.$reply_forum.'&amp;status=thread&amp;thread='.$reply_thread.'#Post:'.$reply_id.']'.$reply_post.'[/quote]';
						}
					}
				}
				//normal post (no reply)
				else
				{
					$t_subject = $t_subject; // :)
					$post = null;
				}
				
				$tpl->assign_vars(array(
				'subject'         => $t_subject,
				'post'            => $post,
				'parse_extension' => ($alias_init->userlevel >= alias::user(alias::USR_MODERRATOR)) ? true : false
				));
				$tpl->buffer($tpl->compile('forum_post'));
			}
			else if(form::submitted())
			{
				//init functions
				//AJAX
				val::AJAX_decode();
				
				check_post();
				
				$subject = val::post($_POST['subject']);
				$post = val::post($_POST['post']);
				if(trim($subject) == null || trim($post) == null)
				{
					trigger_error('You cannot post an empty post.');
				}
				form::unique_check();
				
				//parse::parse_options() extension (permissions)
				//permissions
				$permission = $_POST['permission'];
				//sticky
				$sticky = ($alias_init->userlevel >= alias::user(alias::USR_MODERRATOR)) ? (int) $_POST['sticky'] : 0;
				
				//check for hidden post
				$permission = ($alias_init->userlevel < alias::user(alias::USR_MODERRATOR) && (profile::modded_alias($alias_init->alias) || restricted(array($f_status, $t_status), 'moderated', false))) ? 'hidden('.alias::user(alias::USR_MODERRATOR).')' : 'open';
				
				//status
				$status = val::post($permission.':'.$sticky);
				//options
				$options = parse::parse_options_compile();
				
				$db->query("INSERT INTO `{$db->tb->Forum}` (`ID`, `Type`, `Forum`, `Thread`, `Subject`, `Post`, `Author`, `Unix`, `Edit`, `Stats`, `Status`, `Mod`, `Options`)VALUE(null, 'post', '$forumid', '$threadid', '$subject', '$post', '".val::post($alias_init->alias)."', UNIX_TIMESTAMP(), '0:0;', '0', '$status', '', '$options');");
				//define insert_id here
				$insert_id = $db->insert_id();
				
				//check for hidden post continued: email
				if($status == 'hidden('.alias::user(alias::USR_MODERRATOR).'):0')
				{
					profile::message($config_init->get_config('site_owner'), $config_init->get_config('site_owner'), $config_init->get_config('email_prefix').'Moderation Post', $config_init->get_config('site_owner').",\n$alias_init->alias has posted: ".stripslashes($subject)."\nThis post must be moderated.\nModerate it [url={$_SERVER['PHP_SELF']}?action=forum&forum=$forumid&status=post_permission&thread=$threadid&post=$insert_id]here[/url].", parse::options_parse_code | parse::options_parse_code_ws, 'email');
				}
				
				//subscriptions
				$sql = $db->query("SELECT DISTINCT `Alias` FROM `{$db->tb->Forum_Subscription}` WHERE `Thread`='$threadid' AND `Alias`!='".val::post($alias_init->alias)."';");
				while($fetch = $db->fetch_array($sql))
				{
					profile::message($fetch['Alias'], $config_init->get_config('site_owner'), $config_init->get_config('email_prefix').'Forum Update', "{$fetch['Alias']},\n$alias_init->alias has posted: ".stripslashes($subject)."\nView it [url={$_SERVER['PHP_SELF']}?action=forum&forum=$forumid&status=thread&thread=$threadid#Post:$insert_id]here[/url].", parse::options_parse_code | parse::options_parse_code_ws, 'email');
				}
				
				//AJAX: return to refered page
				$page = (isset($_GET['ajax'])) ? '&page='.page::get_http_referer('page') : null;
				
				header::location($_SERVER['PHP_SELF'].'?action=forum&forum='.$forumid.'&status=thread&thread='.$threadid.$page.'#Post:'.$insert_id);
			}
		break;
	}
}
else if(in_array($status, array('forum_edit', 'thread_edit', 'post_edit')))
{
	secure::secure();
	
	//forum info
	$fetch = forum_info();
	$f_status = reset(sticky($fetch['Status']));
	//thread info
	$fetch = thread_info();
	$t_status = reset(sticky($fetch['Status']));
	
	$sql = $db->query("SELECT * FROM `{$db->tb->Forum}` WHERE IF(`Type`='forum', `Forum`='$forumid' OR `Forum`!='$forumid', `Forum`='$forumid') AND `ID`='$_id' AND `Type` IN('forum', 'thread', 'post');");
	$fetch = $db->fetch_array($sql);
	
	$type = $fetch['Type'];
	$author = $fetch['Author'];
	$subject = val::encode($fetch['Subject']);
	$thread = $fetch['Thread'];
	$status = $fetch['Status'];
	$post = val::encode($fetch['Post']);
	$options = $fetch['Options'];
	
	//type|post existence
	existence($sql, 'post');
	//restrictions
	if($author != $alias_init->alias)
	{
		secure::restrict(alias::user(alias::USR_MODERRATOR));
	}
	restricted(array($f_status, $t_status, $status), array('hidden', 'restricted', 'private', 'closed', 'locked'));
	
	if(!form::submitted())
	{
		$tpl->assign_vars(array(
		'type'          => $type,
		'subject'       => $subject,
		'post'          => $post,
		'parse_options' => $options
		));
		$tpl->buffer($tpl->compile('forum_edit'));
	}
	else if(form::submitted())
	{
		//AJAX
		val::AJAX_decode();
		
		$subject = val::post($_POST['subject']);
		$post = val::post($_POST['post']);
		//options
		$options = parse::parse_options_compile();
		
		if(trim($subject) == null || trim($post) == null)
		{
			trigger_error('You cannot post an empty edit.');
		}
		form::unique_check();
		
		$db->query("UPDATE `{$db->tb->Forum}` SET `Subject`='$subject', `Post`='$post', `Edit`=CONCAT_WS(';', CONCAT_WS(':', UNIX_TIMESTAMP(), SUBSTRING_INDEX(`Edit`, ':', -1)+1), '".val::post($alias_init->alias)."'), `Options`='$options' WHERE IF(`Type`='forum', `Forum`='$forumid' OR `Forum`!='$forumid', `Forum`='$forumid') AND `Thread`='$threadid' AND `ID`='$_id' AND `Type` IN('forum', 'thread', 'post');");
		
		//AJAX: return to correct page
		$page = (isset($_GET['ajax'])) ? '&page='.page::get_http_referer('page') : null;
		
		header::location($_SERVER['PHP_SELF'].'?action=forum&forum='.$forumid.(($type != 'forum') ? '&status=thread&thread='.$thread.$page.'#Post:'.$_id : null));
	}
}
else if($status == 'subscribe')
{
	secure::secure();
	
	//forum info
	$fetch = forum_info();
	$f_status = reset(sticky($fetch['Status']));
	//thread info
	$fetch = thread_info();
	$t_status = reset(sticky($fetch['Status']));
	
	//restrictions
	restricted(array($t_status, $f_status), array('hidden', 'restricted', 'private', 'closed', 'locked'));
	
	//check if user is subscribing/unsubscribing
	$sql = $db->query("SELECT null FROM `{$db->tb->Forum_Subscription}` WHERE `Alias`='".val::post($alias_init->alias)."' AND `Thread`='$_id';");
	$subscription_type = ($db->count_rows($sql) == 0) ? 'subscribe' : 'unsubscribe';
	
	if(!form::submitted())
	{
		$tpl->assign_vars(array(
		'subscription_type' => $subscription_type
		));
		$tpl->buffer($tpl->compile('forum_subscribe'));
	}
	else if(form::submitted())
	{
		$db->query(
		($subscription_type == 'subscribe') ?
		"INSERT INTO `{$db->tb->Forum_Subscription}` (`ID`, `Thread`, `Alias`) VALUES (null, '$_id', '".val::post($alias_init->alias)."');"
		:
		"DELETE FROM `{$db->tb->Forum_Subscription}` WHERE `Alias`='".val::post($alias_init->alias)."' AND `Thread`='$_id';"
		);
		
		header::location($_SERVER['PHP_SELF'].'?action=forum&forum='.$forumid.((isset($threadid)) ? '&status=thread&thread='.$threadid : null));
	}
}
else if(in_array($status, array('forum_permission', 'thread_permission', 'post_permission')))
{
	secure::secure();
	
	//forum info
	$fetch = forum_info();
	$f_status = reset(sticky($fetch['Status']));
	//thread info
	$fetch = thread_info();
	$t_status = reset(sticky($fetch['Status']));
	
	$sql = $db->query("SELECT * FROM `{$db->tb->Forum}` WHERE IF(`Type`='forum', `Forum`='$forumid' OR `Forum`!='$forumid', `Forum`='$forumid') AND `ID`='$_id' AND `Type` IN('forum', 'thread', 'post');");
	$fetch = $db->fetch_array($sql);
	
	$type = $fetch['Type'];
	$author = $fetch['Author'];
	$thread = $fetch['Thread'];
	$mod = val::encode($fetch['Mod']);
	$edit = val::encode($fetch['Edit']);
	list($status, $sticky) = sticky($fetch['Status']);
	
	//type|post existence
	existence($sql, 'post');
	//restrictions
	secure::restrict(alias::user(alias::USR_MODERRATOR));
	restricted(array($f_status, $t_status, $status), array('hidden', 'restricted', 'private', 'closed', 'locked'));
	
	if(!form::submitted())
	{
		//compile permissions
		$permission = array();
		if($alias_init->userlevel >= alias::user(alias::USR_MODERRATOR))
		{
			foreach($alias_init->user as $value)
			{
				$permission[] = array(
				'name'      => val::encode($value['Name']),
				'userlevel' => $value['Userlevel'],
				'color'     => val::encode($value['Mod'])
				);
			}
		}
		
		$tpl->assign_vars(array(
		'type'        => $type,
		'permission'  => val::encode($status),
		'sticky'      => $sticky,
		'author'      => val::encode($author),
		'mod'         => $mod,
		'edit'        => $edit,
		'permissions' => $permission
		));
		$tpl->buffer($tpl->compile('forum_permission'));
	}
	else if(form::submitted())
	{
		$sticky = (int) $_POST['sticky'];
		$permission = val::post($_POST['permission'].':'.$sticky);
		$mod = val::post($_POST['mod']);
		$author = val::post($_POST['author']);
		$edit = val::post($_POST['edit']);
		
		$db->query("UPDATE `{$db->tb->Forum}` SET `Author`='$author', `Edit`='$edit', `Status`='$permission', `Mod`='$mod' WHERE `ID`='$_id' AND `Type` IN('forum', 'thread', 'post');");
		
		header::location($_SERVER['PHP_SELF'].'?action=forum&forum='.$forumid.(($type != 'forum') ? '&status=thread&thread='.$thread.'#Post:'.$_id : null));
	}
}
else if(in_array($status, array('forum_delete', 'thread_close', 'post_delete')))
{
	secure::secure();
	
	//forum info
	$fetch = forum_info();
	$f_status = reset(sticky($fetch['Status']));
	//thread info
	$fetch = thread_info();
	list($t_status, $sticky) = sticky($fetch['Status']);
	
	$sql = $db->query("SELECT * FROM `{$db->tb->Forum}` WHERE IF(`Type`='forum', `Forum`='$forumid' OR `Forum`!='$forumid', `Forum`='$forumid') AND `ID`='$_id' AND `Type` IN('forum', 'thread', 'post');");
	$fetch = $db->fetch_array($sql);
	
	$type = $fetch['Type'];
	$author = $fetch['Author'];
	$thread = $fetch['Thread'];
	$status = $fetch['Status'];
	
	//type|post existence
	existence($sql, 'post');
	//restrictions
	if($author != $alias_init->alias)
	{
		secure::restrict(alias::user(alias::USR_MODERRATOR));
	}
	restricted(array($f_status, $t_status, $status), array('hidden', 'restricted', 'private', 'closed', 'locked'));
	
	//post count for threads
	if($type == 'thread')
	{
		$sql = $db->query("SELECT null FROM `{$db->tb->Forum}` WHERE `Forum`='$forumid' AND `Thread`='$threadid' AND `Type`='post';");
		$posts = $db->count_rows($sql);
	}
	
	if(!form::submitted())
	{
		$tpl->assign_vars(array(
		'type' => $type
		));
		//if it's a thread, assign what's being done to it (close|delete|purge)...
		if($type == 'thread')
		{
			if(restricted($t_status, array('closed', 'locked'), false))
			{
				$thread_type = 'purge';
			}
			else if($posts == 0)
			{
				$thread_type = 'delete';
			}
			else
			{
				$thread_type = 'close';
			}
			
			$tpl->assign_vars(array(
			'thread_type' => $thread_type
			));
		}
		
		$tpl->buffer($tpl->compile('forum_delete'));
	}
	else if(form::submitted())
	{
		if($type == 'thread' && $posts > 0 && !restricted($t_status, array('closed', 'locked'), false))
		{
			//check if user is closing their own thread
			$status = (($author != $alias_init->alias) ? 'locked' : 'closed').':'.$sticky;
			
			//close thread
			$query = "UPDATE `{$db->tb->Forum}` SET `Status`='$status' WHERE `ID`='$_id' AND `Type`='thread';";
		}
		else
		{
			switch($type)
			{
				case 'forum':
					//check to move or delete forum's posts
					$new_forum = (int) val::post($_POST['posts']);
					
					if(!empty($new_forum))
					{
						//check for new forum's existence
						forum_existence($new_forum, 'forum');
						
						$query = array(
						//delete forum
						"DELETE FROM `{$db->tb->Forum}` WHERE `ID`='$_id' AND `Type`='forum';",
						//update threads' forum pointer
						"UPDATE `{$db->tb->Forum}` SET `Forum`='$new_forum' WHERE `Forum`='$_id' AND `Type` IN('thread', 'post');",
						//update tracking data forum pointer
						"UPDATE `{$db->tb->Forum_Data}` SET `Forum`='$new_forum' WHERE `Forum`='$forumid';"
						);
					}
					else
					{
						$query = array(
						//delete forum, threads & posts
						"DELETE FROM `{$db->tb->Forum}` WHERE (`ID`='$_id' AND `Type`='forum') OR (`Forum`='$_id' AND `Type` IN('thread', 'post'));",
						//delete subscriptions & tracking data
						"DELETE `S`, `D` FROM `{$db->tb->Forum_Subscription}` `S`, `{$db->tb->Forum_Data}` `D` WHERE `S`.`Thread`='$_id' AND `D`.`Thread`='$_id';"
						);
					}
					
				break;
				default:
					if($type == 'thread' && restricted($t_status, array('closed', 'locked'), false))
					{
						$query = array(
						//delete threads & posts
						"DELETE FROM `{$db->tb->Forum}` WHERE (`ID`='$_id' AND `Type`='thread') OR (`Thread`='$_id' AND `Type`='post');"
						);
					}
					else
					{
						//delete thread
						$query = "DELETE FROM `{$db->tb->Forum}` WHERE `ID`='$_id' AND `Type`='$type';";
					}
					
					//delete subscriptions & tracking data
					//for "thread_delete" (no array)
					$query = (array) $query;
					$query[] = "DELETE `S`, `D` FROM `{$db->tb->Forum_Subscription}` `S`, `{$db->tb->Forum_Data}` `D` WHERE `S`.`Thread`='$_id' AND `D`.`Thread`='$_id';";
				break;
			}
		}
		
		//init query
		$query = (array) $query;
		
		foreach($query as $value)
		{
			$db->query($value);
		}
		
		header::location($_SERVER['PHP_SELF'].'?action=forum'.(($type != 'forum') ? '&forum='.$forumid : null).(($type != 'forum') ? (($type == 'post' || (stristr($query[0], 'UPDATE'))) ? '&status=thread&thread='.$threadid.(($type == 'thread') ? '#Post:'.$_id : null) : null) : null));
	}
}
else if($status == 'thread_open')
{
	secure::secure();
	
	//forum info
	$fetch = forum_info();
	$f_status = reset(sticky($fetch['Status']));
	//thread info
	$fetch = thread_info();
	list($t_status, $sticky) = sticky($fetch['Status']);
	
	//restrictions
	secure::restrict(alias::user(alias::USR_MODERRATOR));
	restricted(array($f_status, $t_status), array('hidden', 'restricted', 'private', 'closed', 'locked'));
	
	if(!form::submitted())
	{
		$tpl->buffer($tpl->compile('forum_thread_open'));
	}
	else if(form::submitted())
	{
		$db->query("UPDATE `{$db->tb->Forum}` SET `Status`='open:$sticky' WHERE `Forum`='$forumid' AND `ID`='$threadid' AND `Type`='thread';");
		
		header::location($_SERVER['PHP_SELF'].'?action=forum&forum='.$forumid.'&status=thread&thread='.$threadid.'#Post:'.$threadid);
	}
}
else if($status == 'thread_sticky')
{
	secure::secure();
	
	//forum info
	$fetch = forum_info();
	$f_status = reset(sticky($fetch['Status']));
	//thread info
	$fetch = thread_info();
	list($t_status, $sticky) = sticky($fetch['Status']);
	
	//restrictions
	secure::restrict(alias::user(alias::USR_MODERRATOR));
	restricted(array($f_status, $t_status), array('hidden', 'restricted', 'private', 'closed', 'locked'));
	
	if($sticky == 1)
	{
		$status .= ':0';
		$sticky = 'un-sticky';
	}
	else
	{
		$status .= ':1';
		$sticky = 'sticky';
	}
	
	if(!form::submitted())
	{
		$tpl->buffer($tpl->compile('forum_thread_sticky'));
	}
	else if(form::submitted())
	{
		$db->query("UPDATE `{$db->tb->Forum}` SET `Status`='$status' WHERE `ID`='$threadid' AND `Type`='thread';");
		
		header::location($_SERVER['PHP_SELF'].'?action=forum&forum='.$forumid.'&status=thread&thread='.$threadid.'#Post:'.$threadid);
	}
}
else if($status == 'thread_split')
{
	secure::secure();
	
	//forum info
	$fetch = forum_info();
	$f_status = reset(sticky($fetch['Status']));
	//thread info
	$fetch = thread_info();
	$status = reset(sticky($fetch['Status']));
	
	//restrictions
	secure::restrict(alias::user(alias::USR_MODERRATOR));
	restricted(array($f_status, $status), array('hidden', 'restricted', 'private', 'closed', 'locked'));
	
	if(!form::submitted())
	{
		$tpl->buffer($tpl->compile('forum_thread_split'));
	}
	else if(form::submitted())
	{
		$id = (int) val::post($_POST['thread_split']);
		
		if(empty($id))
		{
			trigger_error('You cannot split on an empty ID.');
		}
		
		//convert post into thread
		$db->query("UPDATE `{$db->tb->Forum}` SET `Type`='thread', `Thread`='$id' WHERE `ID`='$id' AND `Type`='post';");
		//update all preceding posts to point to new thread
		$db->query("UPDATE `{$db->tb->Forum}` SET `Thread`='$id' WHERE `Thread`='$threadid' AND `ID`>'$id' AND `Type`='post';");
		
		header::location($_SERVER['PHP_SELF'].'?action=forum&forum='.$forumid.'&status=thread&thread='.$id.'#Post:'.$id);
	}
}
else if($status == 'thread_join')
{
	secure::secure();
	
	//forum info
	$fetch = forum_info();
	$f_status = reset(sticky($fetch['Status']));
	//thread info
	$fetch = thread_info();
	$status = reset(sticky($fetch['Status']));
	
	//restrictions
	secure::restrict(alias::user(alias::USR_MODERRATOR));
	restricted(array($f_status, $t_status), array('hidden', 'restricted', 'private', 'closed', 'locked'));
	
	if(!form::submitted())
	{
		$tpl->buffer($tpl->compile('forum_thread_join'));
	}
	else if(form::submitted())
	{
		$id = (int) val::post($_POST['thread_join']);
		
		if(empty($id))
		{
			trigger_error('You cannot join on an empty ID.');
		}
		
		//check join thread existence
		existence($id, 'thread');
		
		//convert thread into post
		$db->query("UPDATE `{$db->tb->Forum}` SET `Type`='post' WHERE `ID`='$threadid' AND `Type`='thread';");
		//update all preceding posts to point to new thread
		$db->query("UPDATE `{$db->tb->Forum}` SET `Thread`='$id' WHERE `Thread`='$threadid';");
		
		header::location($_SERVER['PHP_SELF'].'?action=forum&forum='.$forumid.'&status=thread&thread='.$id.'#Post:'.$threadid);
	}
}
else if($status == 'thread_move')
{
	secure::secure();
	
	//forum info
	$fetch = forum_info();
	$f_status = reset(sticky($fetch['Status']));
	//thread info
	$fetch = thread_info();
	$status = reset(sticky($fetch['Status']));
	
	//restrictions
	secure::restrict(alias::user(alias::USR_MODERRATOR));
	restricted(array($f_status, $status), array('hidden', 'restricted', 'private', 'closed', 'locked'));
	
	if(!form::submitted())
	{
		$tpl->buffer($tpl->compile('forum_thread_move'));
	}
	else if(form::submitted())
	{
		$id = (int) val::post($_POST['thread_move']);
		
		if(empty($id))
		{
			trigger_error('You cannot move on an empty ID.');
		}
		
		//check new forum existence
		existence($id, 'forum');
		
		//move thread & point child posts to new thread
		$db->query("UPDATE `{$db->tb->Forum}` SET `Forum`='$id' WHERE `Forum`='$forumid' AND `Thread`='$threadid' AND `Type` IN('thread', 'post');");
		
		header::location($_SERVER['PHP_SELF'].'?action=forum&forum='.$id.'&status=thread&thread='.$threadid.'#Post:'.$threadid);
	}
}
else if($status == 'post_report')
{
	secure::secure();
	
	//forum info
	$fetch = forum_info();
	$f_status = reset(sticky($fetch['Status']));
	//thread info
	$fetch = thread_info();
	$t_status = reset(sticky($fetch['Status']));
	
	$sql = $db->query("SELECT * FROM `{$db->tb->Forum}` WHERE `Forum`='$forumid' AND `Thread`='$threadid' AND `ID`='$postid' AND `Type` IN('thread', 'post');");
	$fetch = $db->fetch_array($sql);
	
	$author = $fetch['Author'];
	$id = $fetch['ID'];
	$thread = $fetch['Thread'];
	$subject = $fetch['Subject'];
	
	//post existence
	existence($sql, 'post');
	//restrictions
	if($alias_init->userlevel < $alias_init->alias_mod($author, 'lite:Userlevel')|| restricted(array($f_status, $t_status), array('hidden', 'restricted', 'closed', 'locked')))
	{
		trigger_error('You do not possess a sufficient userlevel to report this post.');
	}
	
	if(!form::submitted())
	{
		$tpl->buffer($tpl->compile('forum_post_report'));
	}
	else if(form::submitted())
	{
		profile::message($config_init->get_config('site_owner'), $config_init->get_config('site_owner'), $config_init->get_config('email_prefix').'Post Reported', $config_init->get_config('site_owner').",\n$alias_init->alias has reported post: $subject, posted by $author.\nReason: {$_POST['post_report']}\nView this post [url=/index.php?action=forum&forum=$forumid.'&status=thread&thread=$thread#Post:$id]here[/url].", parse::options_parse_code | parse::options_parse_code_ws, 'email');
		
		header::location($_SERVER['PHP_SELF'].'?action=forum&forum='.$forumid.'&status=thread&thread='.$thread.'#Post:'.$id);
	}
}

$tpl->assign_vars(array(
'forum_main' => $tpl->buffer()
));
return $tpl->compile('forum_main');
?>