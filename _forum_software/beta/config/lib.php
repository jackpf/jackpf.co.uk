<?php
/*
 *config/lib
 */
#declare(encoding = 'UTF-8');
#namespace jackpf;
$config_init = config::get_instance();

$_SERVER['DOCUMENT_ROOT'] = $config_init->get_config('document_root');

//set error handling
if((bool) $config_init->get_config('debug_mode'))
{
	ini_set('display_errors', E_ALL /*|*/&~ E_STRICT /*-1*/);
	error_reporting(E_ALL /*|*/&~ E_STRICT /*-1*/);
}
else
{
	ini_set('display_errors', false);
	error_reporting(false);
}
set_error_handler('error_handler');
register_shutdown_function('error_handler2');

function error_handler($error_code, $error_message, $error_file, $error_line)
{
	global $config_init;
	
	if(in_array($error_code, array(E_USER_ERROR, E_USER_NOTICE), true) || (bool) $config_init->get_config('debug_mode'))
	{
		//load templates
		$tpl = new template;
		$tpl->load('lib');
		$tpl->load('index');
		
		if(strcmp($error_code, E_USER_ERROR) == 0 || strcmp($error_code, E_USER_NOTICE) == 0)
		{
			$alias_init = new alias;
			$db = new connection;
			
			if(strcmp($error_code, E_USER_ERROR) == 0)
			{
				$tpl->assign_vars(array(
				'error_message' => ((bool) $config_init->get_config('debug_mode')) ? 'Fatal error: '.$error_message.' in '.$error_file.' on line '.$error_line.'.' : 'Fatal error.'
				));
			}
			else
			{
				$tpl->assign_vars(array(
				'error_message' => $error_message
				));
			}
			
			$tpl->assign_vars(array(
			'index_main' => $tpl->compile('config_error'),
			'alias'      => (isset($alias_init->alias)) ? val::encode($alias_init->alias).$alias_init->alias_mod($alias_init->alias, 'lite') : null,
			'note'       => (isset($alias_init->alias)) ? $db->count_rows($db->query("SELECT null FROM `{$db->tb->Message}` WHERE (`Type`='message' OR `Type`='email') AND `Status`='0' AND `Alias`='".val::post($alias_init->alias)."';")) : 0
			));
			
			//title hack
			$_GET['action'] = 'error';
			
			die($tpl->compile('index_main'));
		}
		else if((bool) $config_init->get_config('debug_mode') && (ini_get('display_errors') & $error_code))
		{
			echo 'General error: '.$error_message.' in '.$error_file.' on line '.$error_line.'.<br />';
		}
	}
}
function error_handler2()
{
	if($error = error_get_last())
	{
		if(in_array($error['type'], array(E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR), true))
		{
			trigger_error($error['message'], E_USER_ERROR);
		}
	}
}

//server configuration
if(ini_get('magic_quotes_gpc'))
{
	foreach(array('_POST', '_GET', '_COOKIE', '_REQUEST') as $value)
	{
		foreach($$value as &$value2)
			if(!is_array($value2))
				$value2 = stripslashes($value2);
			else
				$value2 = array_map('stripslashes', $value2);
	}
}

//autoloading
function __autoload($class)
{
	//not implemented
}

/*
 *class lib
 */
class connection
{
	private
		$connection,
		$server, $admin, $pass,
		$db;
	protected static
		$parallel_connections = 0,
		$_connection;
	public $tb;
	
	public function __construct()
	{
		global $config_init;
		
		//define vars
		$this->server = $config_init->get_config('db_server');
		$this->admin  = $config_init->get_config('db_admin');
		$this->pass   = $config_init->get_config('db_pass');
		$this->db     = $config_init->get_config('db_database');
		
		$this->tb     = (object) array(
		'Alias'              => 'Alias',
		'Alias_Stats'        => 'Alias_Stats',
		'Users'              => 'Users',
		'Message'            => 'Message',
		'Forum'              => 'Forum',
		'Forum_Data'         => 'Forum_Data',
		'Forum_Reputation'   => 'Forum_Reputation',
		'Forum_Subscription' => 'Forum_Subscription',
		'Blog'               => 'Blog',
		'IM'                 => 'IM'
		);
		
		//increment parallel connections
		self::$parallel_connections++;
		
		//connect
		if(self::$parallel_connections == 1 && !is_resource(self::$_connection))
		{
			self::$_connection = $this->connection = mysql_connect($this->server, $this->admin, $this->pass) or $this->trigger_error();
		}
		else
		{
			$this->connection = self::$_connection;
		}
		
		//connect
		$this->connect($this->db);
	}
	public function connect($db)
	{
		mysql_select_db($db, $this->connection) or $this->trigger_error();
	}
	public function query($query)
	{
		$query = mysql_query($query, $this->connection) or $this->trigger_error();
		
		return $query;
	}
	public function fetch_array($sql)
	{
		return mysql_fetch_array($sql, MYSQL_ASSOC);
	}
	public function count_rows($sql)
	{
		return mysql_num_rows($sql);
	}
	public function affected_rows($sql)
	{
		return mysql_affected_rows($sql);
	}
	public function insert_id()
	{
		return mysql_insert_id($this->connection);
	}
	public function trigger_error()
	{
		return trigger_error(mysql_error($this->connection), E_USER_ERROR);
	}
	public function escape_string($string)
	{
		return mysql_real_escape_string($string);
	}
	public function __destruct()
	{
		//decrement parallel connections
		self::$parallel_connections--;
		
		if(self::$parallel_connections == 0)
		{
			mysql_close($this->connection);# or $this->trigger_error();
		}
	}
}
class alias
{
	public
		$user = array(),
		$mods = array(),
		$aliasid,
		$alias, $_alias,
		$userlevel, $_userlevel,
		$usergroup, $_usergroup;
	private static
		$_user = array();
	public static
		$_sql, $_fetch;
	
	//some standard user constants
	const
		USR_ADMINISTRATOR = 'Administrator',
		USR_MODERATOR     = 'Moderator',
		USR_USER          = 'User';
	
	public function __construct()
	{
		global $config_init;
		$db = new connection;
		
		//load user vars
		if(self::$_user == null)
		{
			$sql = $db->query("SELECT * FROM `{$db->tb->Users}` ORDER BY `Userlevel` DESC;");
			while($fetch = $db->fetch_array($sql))
			{
				self::$_user[$fetch['Name']] = array(
				'Name'      => $fetch['Name'],
				'Userlevel' => $fetch['Userlevel'],
				'Mod'       => $fetch['Mod'],
				'Owner'     => $fetch['Owner']
				);
			}
		}
		$this->user = self::$_user;
		
		//load alias vars
		if(val::request_var($config_init->get_config('cookie_prefix').'Alias', (string) val::type, '_COOKIE') != null && isset($this->user[val::request_var($config_init->get_config('cookie_prefix').'Alias(User)', (string) val::type, '_COOKIE')]))
		{
			//load cookie vars
			$this->_alias     = val::request_var($config_init->get_config('cookie_prefix').'Alias', (string) val::type, '_COOKIE');
			$this->_usergroup = val::request_var($config_init->get_config('cookie_prefix').'Alias(User)', (string) val::type, '_COOKIE');
			$this->_userlevel = $this->user[$this->_usergroup]['Userlevel'];
			
			//cookie sanity
			if(isset($this->_alias))
			{
				//load/cache query
				if(self::$_fetch == null)
				{
					self::$_sql = $db->query("SELECT `ID`, `Alias`, `User` FROM `{$db->tb->Alias}` WHERE `Alias`='".val::post($this->_alias)."';");
					self::$_fetch = $db->fetch_array(self::$_sql);
				}
				
				//load database vars
				$this->aliasid   = self::$_fetch['ID'];
				$this->alias     = self::$_fetch['Alias'];
				$this->usergroup = self::$_fetch['User'];
				$this->userlevel = $this->user[$this->usergroup]['Userlevel'];
			}
		}
		else
		{
			//unload vars
			$this->alias = null; #unset($this->alias);
			$this->usergroup = null; #unset($this->usergroup);
			$this->userlevel = 0; #unset($this->userlevel);
		}
	}
	public function alias_mod($alias, $alias_mod = 'full', $forum_mod_local_moderator = false)
	{
		//return cached version (full)
		if(array_key_exists($alias, $this->mods) && !strstr($alias_mod, 'lite'))
		{
			return $this->mods[$alias];
		}
		//return cached version (lite)
		if(strstr($alias_mod, 'lite:') && array_key_exists($alias.':'.end(explode(':', $alias_mod)), $this->mods))
		{
			return $this->mods[$alias.':'.end(explode(':', $alias_mod))];
		}
		#else
		//...
		
		global $config_init;
		
		$db = new connection;
		$tpl = new template('lib');
		
		$sql = $db->query("SELECT `User`, `Picture`, `Status`, `Unix` /*for full_forum mod*/ FROM `{$db->tb->Alias}` WHERE `Alias`='".val::post($alias)."';");
		$fetch = $db->fetch_array($sql);
		
		$usergroup = $fetch['User'];
		$picture = val::encode($fetch['Picture']);
		
		//get post count & permissions for full_forum mod
		if($alias_mod == 'full_forum')
		{
			$permission = profile::profile_status($fetch['Status']);
			$posts = forum::posts($alias, false);
			$join_date = $fetch['Unix'];
		}
		
		//check for lite mod
		if(strstr($alias_mod, 'lite:'))
		{
			$mod = $this->user[$usergroup][end(explode(':', $alias_mod))];
			
			//cache
			$this->mods[$alias.':'.end(explode(':', $alias_mod))] = $mod;
			
			return $mod;
		}
		
		//hack for usergroup if user is a local moderator (forum mod)
		if($alias_mod == 'full_forum' && $forum_mod_local_moderator)
		{
			$usergroup = 'Local Moderator';
			$this->user[$usergroup]['Name'] = $usergroup;
		}
		
		$tpl->assign_vars(array(
		'crlf'     => ($alias_mod != 'lite') ? true : false,
		'mod'      => ($this->user[$usergroup]['Mod'] != null || $forum_mod_local_moderator),
		'mod_type' => $alias_mod,
		'user'     => $this->user[$usergroup]['Name'],
		'_user'    => $usergroup,
		'color'    => val::encode($this->user[$usergroup]['Mod']),
		'alias'    => val::encode($alias),
		'picture'  => $picture
		));
		//assign forum vars
		if($alias_mod == 'full_forum')
		{
			$tpl->assign_vars(array(
			'online'       => stat::online($alias),
			'message_link' => ($permission['message'] == 1) ? true : false,
			'email_link'   => ($permission['email'] == 1) ? true : false,
			'posts'        => ($permission['details'] == 1) ? val::number_format($posts['post_count']) : -1,
			'rep'          => ($permission['details'] == 1) ? val::number_format($posts['rep']) : -1,
			'join_date'    => ($permission['details'] == 1) ? reset(explode(',', val::unix($join_date, 'compressed'))) : null
			));
		}
		
		$mod = $tpl->compile('config_alias_mod');
		
		//cache
		$this->mods[$alias] = $mod;
		
		return $mod;
	}
	public function parse_signature($author)
	{
		global $db;
		static $mods = array();
		
		//return cached version
		if(array_key_exists($author, $mods))
		{
			return $mods[$author];
		}
		
		$sql = $db->query("SELECT `Signature` FROM `{$db->tb->Alias}` WHERE `Alias`='$author';");
		$fetch = $db->fetch_array($sql);
		
		//cache
		$mods[$author] = $fetch['Signature'];
		
		return $mods[$author];
	}
	public static function user($user)
	{
		$class = __CLASS__;
		$self = new $class; #$self = new {__CLASS__};
		
		return (array_key_exists($user, $self->user)) ? $self->user[$user]['Userlevel'] : 1;
	}
}
class user extends alias{}
class parse
{
	//options bitfield constants
	const	options_parse_code			= 1,
			options_parse_code_ws		= 2,
			options_parse_smiley		= 4,
			options_parse_html			= 8,
			options_attach_signature	= 16;
	
	public function &__construct()
	{
		return $this;
	}
	public static function parse_options($options = null, array $extension = null)
	{
		global $config_init, $alias_init;
		$tpl = new template('lib');
		
		//init default options
		$options_default = self::parse_options_compile((!is_null($options)) ? $options : ((val::request_var($config_init->get_config('cookie_prefix').'options', (string) val::type, '_COOKIE') != null) ? val::request_var($config_init->get_config('cookie_prefix').'options', (string) val::type, '_COOKIE') : self::options_parse_code | self::options_parse_code_ws | self::options_parse_smiley | self::options_attach_signature));
		
		$options = array(
		'Code Parsing' => array(
					'Parse Code'         => array('parse_code', $options_default['parse_code']),
					'Insert White-Space' => array('parse_code_ws', $options_default['parse_code_ws']),
					'Parse Smileys'      => array('parse_smiley', $options_default['parse_smiley']),
					),
		'Signature'    => array(
					'Attach Signature'   => array('attach_signature', $options_default['attach_signature']),
					)
		);
		
		//mod
		if($alias_init->userlevel >= alias::user(alias::USR_MODERATOR))
		{
			$options['Code Parsing']['Parse HTML'] = array('parse_html', $options_default['parse_html']);
		}
		
		//compile extensions
		if(!empty($extension) && is_array($extension))
		{
			foreach($extension as $key => $value)
			{
				if(!isset($options[$key]))
				{
					//create new option tab
					$options[$key] = array();
				}
				
				foreach($value as $key2 => $value2)
				{
					//append options to this option tab
					$options[$key][$key2] = $value2;
				}
			}
		}
		
		$tpl->assign_vars(array(
		'options' => $options
		));
		
		return $tpl->compile('config_parse_options', true);
	}
	public static function parse_extension($options, $extension_option, $post = null, $callback = null)
	{
		//return extension option\n		if($post == null || $callback == null)\n		{\n			return ($options & $extension_option) ? true : false;\n		}\n		//execute callback\n		else if($options & $extension_option))\n		{\n			//procedural callback\n			if(!is_array($callback) && function_exists($callback) && is_callable($callback))\n			{\n				return $callback($post);\n			}\n			//OO callback\n			else\n			{\n				list($class, $method) = $callback;\n				\n				if(method_exists($class, $method))\n				{\n					$object = new $class;\n					\n					if($object instanceof $class)\n					{\n						return $object->$method($post);\n					}\n				}\n			}\n		}\n		else\n		{\n			return $post;\n		}
	}
	public static function parse_options_compile($_options = null)
	{
		if(is_null($_options) && form::submitted())
		{
			global $config_init, $alias_init;
			
			//init options bitfield
			$options = 0;
			
			//compile options
			if((bool) val::request_var('parse_code', (bool) val::type))
				$options |= self::options_parse_code;
			if((bool) val::request_var('parse_code_ws', (bool) val::type))
				$options |= self::options_parse_code_ws;
			if((bool) val::request_var('parse_smiley', (bool) val::type))
				$options |= self::options_parse_smiley;
			if((bool) val::request_var('parse_html', (bool) val::type) && $alias_init->userlevel >= alias::user(alias::USR_MODERATOR))
				$options |= self::options_parse_html;
			if((bool) val::request_var('attach_signature', (bool) val::type))
				$options |= self::options_attach_signature;
			
			//set default options cookie
			header::setcookie($config_init->get_config('cookie_prefix').'options', $options, 2 * time());
		}
		else
		{
			$options = array(
			'parse_code'       => (bool) ($_options & self::options_parse_code),
			'parse_code_ws'    => (bool) ($_options & self::options_parse_code_ws),
			'parse_smiley'     => (bool) ($_options & self::options_parse_smiley),
			'parse_html'       => (bool) ($_options & self::options_parse_html),
			'attach_signature' => (bool) ($_options & self::options_attach_signature),
			);
		}
		
		return $options;
	}
	public static function parse($post, $_options)
	{
		//compile options with correct data types
		$options = self::parse_options_compile($_options);
		
		$code = new code;
		
		//code vars
		$code->set_var('parse_html', $options['parse_html']);
		
		//parse options
		if(!$options['parse_html'])
		{
			//encoding
			$post = val::encode($post);
		}
		if($options['parse_code'])
		{
			//parse post
			$post = $code->parse_code($post, $options['parse_code_ws']);
		}
		else if($options['parse_code_ws'])
		{
			//parse post (ws)
			$post = $code->parse_code_ws($post);
		}
		if($options['parse_smiley'])
		{
			$post = $code->smiley($post);
		}
		//check for signature
		if($options['attach_signature'])
		{
			//global author hack for signature
			$alias_init = new alias;
			$signature = $alias_init->parse_signature($GLOBALS['author']);
			
			if($signature != null)
			{
				global $config_init;
				$tpl = new template('lib');
				
				$tpl->assign_vars(array(
				'signature' => self::parse($signature, $_options & ~self::options_attach_signature & ~self::options_parse_code_ws)
				));
				
				$post .= $tpl->compile('config_parse_signature', true);
			}
		}
		
		return $post;
	}
}
class secure
{
	public function __construct()
	{
		return $this;
	}
	public static function restrict($access)
	{
		global $alias_init;
		
		if(!$alias_init instanceof alias)
		{
			$alias_init = new alias;
		}
		
		if($alias_init->userlevel < $access)
		{
			return trigger_error('You either do not possess a sufficient user level to perform this action, or you are not permitted to access this area.');
		}
	}
	public static function secure()
	{
		global $config_init;
		
		$alias_init = new alias;
		
		//logged in!
		if(isset($alias_init->_alias))
		{
			$db = new connection;
			
			//crypt not provided by alias::__construct()
			$crypt = val::request_var($config_init->get_config('cookie_prefix').'Alias(Crypt)', (string) val::type, '_COOKIE');
			
			//check alias & user exists, & hasn't hacked | modified usergroup
			if($db->count_rows(alias::$_sql) < 1 || $alias_init->alias != $alias_init->_alias || empty($alias_init->_usergroup) || sha1($alias_init->aliasid) != $crypt || $alias_init->usergroup != $alias_init->_usergroup)
			{
				//logout
				header::location('/index.php?action=login&status=logout');
				trigger_error(null);
			}
		}
		//not logged in
		else
		{
			//login
			header::location('/index.php?action=login&referer='.urlencode($_SERVER['REQUEST_URI']));
			trigger_error(null);
		}
	}
}
class page
{
	public
		$delimiter  = '&page=',
		$display    = 'page',
		$default    = 'first';
	
	public function __construct()
	{
		return $this;
	}
	public function page($SQL, $limit, &$pagination)
	{
		global $db, $config_init;
		$tpl = new template('lib');
		
		//get result count; hack to remove any joins
		$sql = $db->query($SQL);
		$count = $db->count_rows($sql);
		
		//get end page
		$last_page = ceil($count / $limit);
		//get current page; if not set get first page/last page (for $this->default == 'last')
		$page = (val::request_var('page', (int) val::type) != null) ? (int) val::request_var('page', (int) val::type) : (($this->default == 'last') ? $last_page : 1);
		
		//correct page for sql
		$page -= 1;
		
		//page validation : check page isn't less than 0 or greater than page count
		$page = ($page < 0 || $page > $last_page - 1) ? 0 : $page;
		
		//sql start
		$start = $page * $limit;
		
		//return pagination
		//restore correct page for display
		$page += 1;
		
		//previous page display
		$tpl->assign_vars(array(
		'pages'         => true,
		'display_type'  => $this->display,
		'uri'           => reset(explode(val::encode($this->delimiter), val::encode($_SERVER['REQUEST_URI']))).val::encode($this->delimiter),
		'current_page'  => $page,
		'last_page'     => $last_page
		));
		
		$pagination = $tpl->compile('config_pagination');
		
		//return sql
		//remove trailing semi-colon
		$SQL = (substr($SQL, strlen($SQL) - 1, 1) == ';') ? substr($SQL, 0, strlen($SQL) - 1) : $SQL;
		return $db->query("$SQL LIMIT $start, $limit;");
	}
	public static function header_active($link, $get_key, $get_value, $class = 'style="color: blue;"')
	{
		$get_value = (array) $get_value;
		$get = (val::request_var($get_key) != null) ? val::request_var($get_key) : null;
		
		return (in_array($get, $get_value)) ? '<span '.$class.'>'.$link.'</span>' : $link;
	}
	public static function get_http_referer($array_key)
	{
		$query_string = array();
		
		foreach(explode('&amp;', val::encode($_SERVER['HTTP_REFERER'])) as $value)
		{
			$key = explode('=', $value);
			$query_string[$key[0]] = $key[1];
		}
		
		return (array_key_exists($array_key, $query_string)) ? $query_string[$array_key] : false;
	}
}
class stat
{
	public
		$alias_init,
		$type;
		#$limit;
	
	public static $limit;
	
	const TIMEOUT = 600;
	
	public function __construct()
	{
		global $config_init;
		
		//define timeout limit (static property for static methods)
		$this->limit = self::$limit = time() - self::TIMEOUT;
		
		$this->alias_init = new alias;
		
		if(isset($this->alias_init->alias))
		{
			$this->alias_init->alias = $this->alias_init->alias;
			$this->type = (self::visible()) ? 'alias' : 'hidden';
		}
		else
		{
			$this->alias_init->alias = ($this->human()) ? $_SERVER['REMOTE_ADDR'] : $this->bot();
			$this->type = ($this->human()) ? 'stranger' : 'bot';
		}
	}
	public function update()
	{
		$db = new connection;
		
		//update `Unix` & `Unix_Total`
		$db->query("INSERT INTO `{$db->tb->Alias_Stats}`
		(`ID`, `Alias`, `Type`, `Unix`, `Unix_Total`, `URI`, `Online`)
		VALUES
		(null, '".val::post($this->alias_init->alias)."', '".val::post($this->type)."', UNIX_TIMESTAMP(), '0', '".val::post($_SERVER['REQUEST_URI'])."', '1')
		ON DUPLICATE KEY
		#update `Unix_Total` before time so `Unix`-this->stat is not 0
		UPDATE `Type`=VALUES(`Type`), `Online`=VALUES(`Online`), `Unix_Total`=IF(`Unix`>='$this->limit', `Unix_Total`+(UNIX_TIMESTAMP()-`Unix`), `Unix_Total`), `Unix`=VALUES(`Unix`), `URI`=VALUES(`URI`);");
		
		//define online count for update (alias of this->fetch_stats() query)
		$db->query("SET @online=(SELECT COUNT(`ID`) FROM `{$db->tb->Alias_Stats}` WHERE `Unix`>='$this->limit' AND `Online`='1' AND `Type` IN('alias', 'hidden', 'stranger', 'bot'));");
		//update "most online" if @online > current record
		$db->query("UPDATE `{$db->tb->Alias_Stats}`
		SET `Unix`=IF(@online>`Unix_Total`,
		UNIX_TIMESTAMP(),
		`Unix`),
		`Unix_Total`=IF(@online>`Unix_Total`,
		@online,
		`Unix_Total`)
		WHERE `Alias`='ONLINE' AND `Type`='const';");
	}
	public function fetch_stats($uri = null)
	{
		$db = new connection;
		
		//online viewing
		$query = ($uri !== null) ? "AND `URI` LIKE '$uri'" : null;
		
		$sql = $db->query("SELECT `Alias`, `Type` FROM `{$db->tb->Alias_Stats}` WHERE `Unix`>='$this->limit' AND `Online`='1' AND `Type` IN('alias', 'hidden', 'stranger', 'bot') $query;");
		
		//init stats
		$stats = array(
		'aliases'        => array(),
		'aliases_hidden' => 0,
		'strangers'      => 0,
		'bots'           => array()
		);
		
		while($fetch = $db->fetch_array($sql))
		{
			$alias = $fetch['Alias'];
			$type = $fetch['Type'];
			
			switch($type)
			{
				case 'alias':
					$stats['aliases'][] = $alias;
				break;
				case 'hidden':
					$stats['aliases_hidden'] += 1;
				break;
				case 'stranger':
					$stats['strangers'] += 1;
				break;
				case 'bot':
					$stats['bots'][] = $alias;
				break;
			}
		}
		
		return $stats;
	}
	public static function online($alias)
	{
		$db = new connection;
		
		$sql = $db->query("SELECT null FROM `{$db->tb->Alias_Stats}` WHERE `Alias`='".val::post($alias)."' AND `Unix`>='".self::$limit."' AND `Type` IN('alias', 'hidden') AND `Online`='1';");
		$count = $db->count_rows($sql);
		
		return ($count > 0) ? true : false;
	}
	public static function visible($_login = false)
	{
		return !self::hidden($_login);
	}
	public static function hidden($_login = false)
	{
		global $config_init;
		
		if(!$_login)
		{
			$alias_init = new alias;
			
			return (val::request_var($config_init->get_config('cookie_prefix').'login_hide', (string) val::type, '_COOKIE') != null && $alias_init->userlevel >= alias::user(alias::USR_MODERATOR)) ? true : false;
		}
		else
		{
			global $login;
			
			return ($login->alias_init->userlevel >= alias::user(alias::USR_MODERATOR)) ? true : false;
		}
	}
	private function human()
	{
		return !$this->bot();
	}
	private function bot()
	{
		$bot = false;
		
		$bots = array(
		'bot'       => 'Bot',
		'crawler'   => 'Crawler Bot',
		'spider'    => 'Spider Bot',
		'validator' => 'Validator'
		);
		
		foreach(array_keys($bots) as $value)
		{
			$bot = (stristr($_SERVER['HTTP_USER_AGENT'], $value)) ? $bots[$value] : $bot;
		}
		
		return $bot;
	}
}
class val
{
	const type = 0x0;
	public static function request_var($var_name, $type_cast = '' /*(string) self::type*/, $request_type = '_REQUEST')
	{
		if($var_name !== null)
		{
			$var = eval('return (isset($'.$request_type.'[\''.$var_name.'\'])) ? $'.$request_type.'[\''.$var_name.'\'] : null;'); #$$request_type[$var_name];
			
			switch(gettype($type_cast))
			{
				case 'boolean':						$var =	(bool)		$var;	break;
				case 'integer':						$var =	(int)		$var;	break;
				case 'double': case 'float':		$var =	(float)		$var;	break;
				case 'string':						$var =	(string)	$var;	break;
				case 'array':						$var =	(array)		$var;	break;
				case 'object':						$var =	(object)	$var;	break;
				case 'resource':					$var =	/*(re...)*/	$var;	break;
				case 'null': case 'unkown type':	$var =	/*(null)*/	null;	break;
			}
		}
		else if($var_name == null && gettype($type_cast) == 'array')
		{
			$var = eval('return (array) $'.$request_type.';');
		}
		
		return $var;
	}
	public static function set_var($var_name, $var_value, $type_cast = '' /*(string) self::type*/, $request_type = '_REQUEST')
	{
		if($var_name !== null)
		{
			eval('$'.$request_type.'[\''.$var_name.'\'] = ('.gettype($type_cast).') \''.$var_value.'\';'); #$$request_type[$var_name];
		}
	}
	public static function unix($unix, $format = 'full')
	{
		date_default_timezone_set('Europe/London');
		
		$hour  = date('H', $unix);
		$day   = date('j', $unix);
		$day2  = date('l', $unix);
		$month = date('F', $unix);
		$year  = date('Y', $unix);
		$time  = date('H:i', $unix);
		
		switch($day)
		{
			case 11: case 12: case 13:
				$sup = 'th';
			break;
			default:
				switch($day % 10)
				{
					case 1:
						$sup = 'st';
					break;
					case 2:
						$sup = 'nd';
					break;
					case 3:
						$sup = 'rd';
					break;
					default:
						$sup = 'th';
					break;
				}
			break;
		}
		
		switch($format)
		{
			case 'full':
				return $day2.' the '.$day.'<sup>'.$sup.'</sup> of '.$month.', '.$year.' at '.$time.'<sub>'.(($hour < 12) ? 'AM' : 'PM').'</sub>';
			break;
			case 'compressed':
				return $day.'<sup>'.$sup.'</sup> '.val::str_trim($month, 3, null).' '.$year.', '.$time;
			break;
			case 'lite':
				return date('d/m/Y H:i', $unix);
			break;
		}
	}
	public static function str_trim($str, $limit, $tail = '...')
	{
		return (strlen($str) > $limit) ? substr($str, 0, $limit).$tail : $str;
	}
	public static function array_explode($delimiter = ',', array $array)
	{
		return array_filter(explode(',', preg_replace('/\s/', null, $array)), create_function('$str', 'return !empty($str);')); #function($str){return !empty($str);}
	}
	public static function post($str, $db = true, $quotes = '\'')
	{
		if(!$db && $quotes == '"')
			return str_replace('"', '\"', $str);
		
		if(is_array($str))
		{
			#$str = array_map(array(self, 'post'), $str);
			foreach($str as &$value)
			{
				$value = self::post($value);
			}
		}
		else
		{
			if($db)
			{
				$db = new connection;
				$str = $db->escape_string(trim($str));
			}
			else
			{
				$str = addslashes(trim($str));
			}
		}
		
		return $str;
	}
	public static function number_format($i)
	{
		$float_limit = 2;
		
		$i = round((float) $i, $float_limit);
		
		$float = (strstr($i, '.')) ? strlen(end(explode('.', $i))) : 0;
		$round = ($float <= $float_limit) ? $float : $float_limit;
		
		return number_format($i, $round, '.', ',');
	}
	public static function email_val($email)
	{
		return (preg_match('/(.*?)\@(.*?)\.(.*?)/', filter_var($email, FILTER_VALIDATE_EMAIL)) && checkdnsrr(end(explode('@', $email)), 'MX')) ? $email : null;
	}
	public static function alias_val($alias)
	{
		return (preg_match('/^[\w\d0-9\_\-\.]+$/i', $alias)) ? $alias : null;
	}
	public static function encode($str)
	{
		if(is_array($str))
		{
			return array_map(array('val', 'encode'), $str);
		}
		else
		{
			return htmlentities($str, ENT_QUOTES, 'UTF-8');
		}
	}
	public static function decode($str)
	{
		if(is_array($str))
		{
			return array_map(array('val', decode), $str);
		}
		else
		{
			return html_entity_decode($str, ENT_QUOTES, 'UTF-8');
		}
	}
	public static function AJAX_decode($get_ajax = true)
	{
		if(($get_ajax && val::request_var('ajax', (bool) val::type) != null) || !$get_ajax)
		{
			foreach(val::request_var(null, (array) val::type, '_POST') as $key => $value)
			{
				val::set_var($key, urldecode($value), (string) val::type, '_POST');
			}
		}
	}
}
class form
{
	public static function submitted()
	{
		return ($_SERVER['REQUEST_METHOD'] == 'POST') ? true : false;
	}
	public static function unique_hash()
	{
		return '<input type="hidden" name="key" value="'.uniqid().'" />';
	}
	public static function unique_check()
	{
		global $config_init;
		
		$key = val::request_var('key');
		
		if(val::request_var($config_init->get_config('cookie_prefix').'key', (string) val::type, '_COOKIE') == $key)
		{
			trigger_error('This form has already been submitted.');
		}
		else
		{
			header::setcookie($config_init->get_config('cookie_prefix').'key', $key, 0);
		}
	}
	public static function get_query_string($key = null)
	{
		$form = '';
		
		if($key === null)
		{
			foreach(val::request_var(null, (array) val::type, '_GET') as $key => $value)
			{
				$form .= '<input type="hidden" name="'.val::encode($key).'" value="'.val::encode($value).'" />';
			}
		}
		else
		{
			$key = (array) $key;
			
			foreach($key as $value)
			{
				$form .= '<input type="hidden" name="'.val::encode($value).'" value="'.val::encode(val::request_var($value)).'" />';
			}
		}
		
		return $form;
	}
}
class header
{
	public static function location($location)
	{
		return header('Location: '.val::decode($location));
	}
	public static function setcookie($name, $value, $expire)
	{
		global $config_init;
		
		return setcookie($name, $value, $expire, '/', false);#$config_init->get_config('site_domain'));
	}
	public static function unsetcookie($name)
	{
		return self::setcookie($name, null, -1);
	}
}
class config
{
	//interface
	private
		$cfg,
		$config;
	private static
		$_cfg;
	
	public static function &get_instance()
	{
		return new self;
	}
	private function __construct()
	{
		$this->load(dirname(__FILE__).'/cfg.cfg');
		$this->config = $this->parse()->get_array();
	}
	public function get_config($key = null, $raw = false)
	{
		//return config values (with some debugging...)
		if($key == null)
		{
			//return entire config array
			if(!$raw)
			{
				return (!empty($this->config)) ? $this->config : trigger_error('Config is non-existent!', E_USER_ERROR);
			}
			//return raw config
			else
			{
				return $this->file;
			}
		}
		else
		{
			//extract struct & key
			//...
			if(!strstr($key, '.'))
				$key = reset(array_keys($this->config)).'.'.$key;
			$_eval = '';
			foreach(explode('.', $key) as $struct)
				$_eval .= "['$struct']";
			$_eval = substr($_eval, 1, strlen($_eval) - 2);
			
			//alias of self::_eval()
			eval("\$cfg_value = \$this->config[{$_eval}]; if(\$cfg_value == null) \$cfg_value = \$this->structure[{$_eval}];");
			
			//check struct & key/value exists
			if(isset($cfg_value))
			{
				return preg_replace_callback('/var\((.*?)\)/i', create_function('$matches', 'foreach($GLOBALS as $key => $value){global $$key;} return eval(\'return $\'.$matches[1].\';\');'), $cfg_value); #function($matches){foreach($GLOBALS as $key => $value){global $$key;} return eval('return $'.$matches[1].';');}
			}
			else
			{
				return trigger_error('Config value "'.val::encode($key).'" is non-existent!', E_USER_ERROR);
			}
		}
	}
	
	//parsing
	//file vars
	private
		$filename,
		$file,
		$structure = array();
	
	//parse vars
	private
		$in_name		= false,
		$in_struct		= array(false),
		$in_quote		= false,
		$in_comment		= false,
		$in_varname		= false,
		$struct_count	= 0,
		$open_structs	= array();
	
	//buffer vars
	private
		$buffer,
		$structnames	= array(),
		$varnames		= array();
		
	//load cfg file
	public function &load($file)
	{
		//clear the structure
		$this->structure = array();
		
		if($file != $this->filename)
		{
			if(file_exists($file))
			{
				$this->filename = $file;
				
				$fh = fopen($this->filename, 'r');
				$this->file = fread($fh, filesize($this->filename));
				fclose($fh);
			}
			else
			{
				throw new Exception('File '.$file.' not found.');
			}
		}
		
		return $this;
	}
	
	//parse cfg file
	public function &parse()
	{
		//parse the file!
		for($i = 0; $i < strlen($this->file); $i++)
		{
			//check quote start
			if($this->file[$i] == '"' && !$this->in_quote && !$this->in_comment)
				$this->in_quote = true;
			//check quote end
			else if($this->file[$i] == '"' && $this->file[$i - 1] != '\\' && $this->in_quote && !$this->in_comment)
				$this->in_quote = false;
			
			//check struct start
			if($this->file[$i] == '{' && !$this->in_quote && !$this->in_comment)
			{
				$this->in_struct[$this->struct_count] = true;
				$this->struct_count++;
			}
			//check struct end
			else if($this->file[$i] == '}' && !$this->in_quote && !$this->in_comment)
			{
				$this->in_struct[$this->struct_count] = false;
				unset($this->open_structs[$this->struct_count]);
				$this->struct_count--;
			}
			//check comment start
			if($this->file[$i] == '/' && $this->file[$i + 1] == '/' && !$this->in_quote)
				$this->in_comment = true;
			//check comment end
			else if($this->in_comment && $this->file[$i] == "\n")
				$this->in_comment = false;
			
			//check comment
			if($this->in_comment)
				continue;
			
			//check struct name start
			if(($this->in_name || (!$this->in_quote && !$this->in_comment && $this->_next_token($i, '{'))) && ctype_alnum($this->file[$i]))
			{
				$this->in_name = true;
				$this->buffer .= $this->file[$i];
			}
			//check struct name end
			else if($this->in_name && ($this->file[$i] == "\n" || $i == strlen($this->file) - 1))
			{
				$this->in_name = false;
				$this->structnames[] = $this->buffer;
				$this->buffer = null;
				$this->open_structs[$this->struct_count] = end($this->structnames);
				
				eval("\$this->structure[".$this->_eval()."] = array();");
			}
			//check var name start
			else if(($this->in_varname || ($this->in_struct && !$this->in_quote)) && (ctype_alnum($this->file[$i]) || $this->file[$i] == '_'))
			{
				$this->in_varname = true;
				$this->buffer .= $this->file[$i];
			}
			//check var name end
			else if($this->in_varname && ($this->file[$i] == ' ' || $this->file[$i] == "\t"))
			{
				$this->in_varname = false;
				$this->varnames[] = $this->buffer;
				$this->buffer = null;
				
				eval("\$this->structure[".$this->_eval()."]['".end($this->varnames)."'] = null;");
			}
			//check value start
			else if($this->in_quote && ($this->file[$i] != '"' || ($this->file[$i] == '"' && $this->file[$i - 1] == '\\')) && ($this->file[$i] != '\\' || $this->file[$i - 1] == '\\'))
				$this->buffer .= $this->file[$i];
			//check value end
			else if(!$this->in_quote && $this->file[$i] == '"')
			{
				eval("if(\$this->buffer === null)
					\$this->buffer = '';
				
				if(\$this->structure[".$this->_eval()."][end(\$this->varnames)] === null)
					\$this->structure[".$this->_eval()."][end(\$this->varnames)] = \$this->buffer;
				else
				{
					if(!is_array(\$this->structure[".$this->_eval()."][end(\$this->varnames)]))
						\$this->structure[".$this->_eval()."][end(\$this->varnames)] = array(\$this->structure[".$this->_eval()."][end(\$this->varnames)]);
					
					\$this->structure[".$this->_eval()."][end(\$this->varnames)][] = \$this->buffer;
				}
				");
				/*
				//singular
				if($this->structure[end($this->structnames)][end($this->varnames)] === null)
					$this->structure[end($this->structnames)][end($this->varnames)] = $this->buffer;
				//array
				else
				{
					if(!is_array($this->structure[end($this->structnames)][end($this->varnames)]))
						$this->structure[end($this->structnames)][end($this->varnames)] = array($this->structure[end($this->structnames)][end($this->varnames)]);
					
					$this->structure[end($this->structnames)][end($this->varnames)][] = $this->buffer;
				}
				*/
				$this->buffer = null;
			}
		}
		
		return $this;
	}
	
	private function _next_token($index, $token)
	{
		for($i = $index; $i < strlen($this->file); $i++)
			if(!in_array($this->file[$i], array(" ", "\t", "\n", "\r")) && !ctype_alnum($this->file[$i]))
				return ($this->file[$i] == $token);
	}
	private function _eval()
	{
		$eval = '';
		for($ii = 0; $ii < sizeof($this->open_structs); $ii++)
			$eval .= "['{$this->open_structs[$ii]}']";
		
		return substr($eval, 1, strlen($eval) - 2);
	}
	
	//write cfg file
	public function write($data, $raw = false)
	{
		if(!$raw)
		{
			$file = '';
			
			foreach($data as $key => $value)
			{
				$file .= $key."\n{\n";
				
				foreach($value as $key2 => $value2)
				{
					if(is_array($value2))
						$value2 = implode('" "', val::post($value2, false, '"'));
					else
						$value2 = val::post($value2, false, '"');
					
					$file .= "\t".$key2."\t\"".$value2."\"\n";
				}
				
				$file .= "}\n";
			}
		}
		else
			$file = $data;
		
		$fh = fopen($this->filename, 'w+');
		fwrite($fh, $file);
		fclose($fh);
	}
	
	public function __call($method, array $args)
	{
		//assign code vars
		switch($method)
		{
			case 'get':
				return $this->structure;
			break;
			case 'get_array':
				return (array) $this->structure;
			break;
			case 'get_object':
				return (object) $this->structure;
			break;
		}
	}
}
class profile
{
	public static function message($recipient, $author, $subject, $message, $options, $type)
	{
		global $config_init, $alias_init, $db;
		
		//post vars
		$recipient = val::post($recipient);
		$author    = val::post($author);
		$subject   = val::post($subject);
		$message   = val::post($message);
		$options   = val::post($options);
		$type      = val::post($type);
		
		$db->query("INSERT INTO `{$db->tb->Message}` (`ID`, `Type`, `Alias`, `Subject`, `Message`, `Author`, `Unix`, `Edit`, `Status`, `Options`) VALUES (null, '$type', '$recipient', '$subject', '$message', '$author', UNIX_TIMESTAMP(), '0:0;', '0', '$options');");
		
		include_once 'bin/mail.class.php';
		$mail = new mail($config_init->get_config('site_email'), 'localhost');
		$sql = $db->query("SELECT `Email` FROM `{$db->tb->Alias}` WHERE `Alias`='".val::post($alias_init->alias)."';");
		$fetch = $db->fetch_array($sql);
		$mail->send($fetch['Email'], $config_init->get_config('email_prefix').'You have unread notifications on '.$config_init->get_config('site_domain'), "Follow this link to check your profile.\nhttp://".$config_init->get_config('site_domain')."{$_SERVER['PHP_SELF']}?action=profile&status=profile_self&profile=$type");
	}
	public static function profile_link($alias, $class = null)
	{
		#val::encode($_SERVER['PHP_SELF'])
		return '<a '.$class.' href="./index.php?action=profile&amp;status=profile&amp;profile=index&amp;alias='.$alias.'">'.$alias.'</a>';
	}
	public static function profile_status($status)
	{
		/*
		 *profile_status	(0 = normal, 1 = hidden, 2 = private)
		 *profile_developer (0 = off)
		 *allow_details		(0 = deny, 1 = allow)
		 *allow_messages	(0 = deny, 1 = allow)
		 *allow_emails		(0 = deny, 1 = allow)
		 *alias_mod			(0 = normal, 1 = mod, 2 = ban)
		 */
		
		$profile_status = explode('.', $status);
		
		return array(
		'profile'   => $profile_status[0],
		'developer' => $profile_status[1],
		'details'   => $profile_status[2],
		'message'   => $profile_status[3],
		'email'     => $profile_status[4],
		'alias_mod' => $profile_status[5]
		);
	}
	public static function compile_profile_status(array $status)
	{
		foreach($status as &$value)
		{
			$value = (int) $value;
		}
		
		return implode('.', $status);
	}
	public static function modded_alias($alias)
	{
		global $db;
		
		$sql = $db->query("SELECT `Status` FROM `{$db->tb->Alias}` WHERE `Alias`='".val::post($alias)."';");
		$fetch = $db->fetch_array($sql);
		
		$status = self::profile_status($fetch['Status']);
		
		//check for post ban
		if($status['alias_mod'] == 2)
		{
			return secure::restrict(alias::user(alias::USR_MODERATOR));
		}
		
		return ($status['alias_mod'] == 1) ? true : false;
	}
	public static function clm_len($tb)
	{
		global $db;
		
		//get max lengths for columns
		$sql = $db->query("SHOW COLUMNS FROM `$tb`;");
		
		$clmns = array();
		
		while($fetch = $db->fetch_array($sql))
		{
			$clmns['clm_'.$fetch['Field']] = str_replace(')', null, end(explode('(', ($fetch['Type']))));
		}
		
		return $clmns;
	}
}
class forum
{
	public static function posts($alias, $advanced_posts = true)
	{
		global $db;
		
		//init posts
		$posts = array();
		
		//get post count
		$sql = $db->query("SELECT COUNT(`ID`) AS `PostCount` FROM `{$db->tb->Forum}` WHERE `Author`='".val::post($alias)."' AND `Type` IN('forum', 'thread', 'post');");
		$fetch = $db->fetch_array($sql);
		
		$posts['post_count'] = $fetch['PostCount'];
		
		$sql = $db->query("SELECT SUM(`Reputation`) AS `RepSum` FROM `{$db->tb->Forum_Reputation}` WHERE `Alias`='".val::post($alias)."';");
		$fetch = $db->fetch_array($sql);
		
		$posts['rep'] = $fetch['RepSum'];
		
		//advanced post stats
		if($advanced_posts)
		{
			//get most active forum
			$sql = $db->query("SELECT COUNT(`F`.`ID`) AS `PostCount`, `F`.`Forum` AS `Forum`
			FROM `{$db->tb->Alias}` `A`
			INNER JOIN `{$db->tb->Forum}` `F` ON `A`.`Alias`=`F`.`Author` AND `F`.`Type` IN('thread', 'post')
			WHERE `A`.`Alias`='".val::post($alias)."'
			GROUP BY `F`.`Forum`;");
			
			$forum = array();
			
			while($fetch = $db->fetch_array($sql))
			{
				$forum[$fetch['Forum']] = (!array_key_exists($fetch['Forum'], $forum)) ? 0 : $forum[$fetch['Forum']];
				
				$forum[$fetch['Forum']] += $fetch['PostCount'];
			}
			
			ksort(&$forum);
			$sql = $db->query("SELECT * FROM `{$db->tb->Forum}` WHERE `ID`='".key($forum)."' AND `Type`='forum';");
			
			$posts['active_forum'] = $db->fetch_array($sql);
		}
		
		return $posts;
	}
	public static function search($type, $search)
	{
		$type = (array) $type;
		$forum_search = array();
		
		foreach($type as $value)
		{
			$forum_search[] = "$value LIKE '%".str_replace(' ', '%', $search)."%'";
		}
		
		return 'AND ('.implode(" OR ", $forum_search).')';
	}
	public static function search2($type, $search)
	{
		return "AND $type IN('".implode("', '", $search)."')";
	}
	public static function search3($type, array $search_array, $clause = "HAVING")
	{
		list($search_case, $search) = $search_array;
		
		if($search != null)
		{
			$search_case = (in_array($search_case, array('=', '<=', '>='))) ? $search_case : '=';
			$search = "$clause $type$search_case'$search'";
		}
		
		return $search;
	}
}
class template #extends page, profile, form, parse
{
	private
		$vars = array(),
		$buffer;
	
	public function __construct($template = null)
	{
		if($template != null)
		{
			$this->load($template);
		}
	}
	public function load($template)
	{
		global $config_init;
		
		$theme = $config_init->get_config('site_theme').'/';
		
		$template_file = (file_exists($_SERVER['DOCUMENT_ROOT'].'/templates/'.$theme.$template.'.tpl')) ? $_SERVER['DOCUMENT_ROOT'].'/templates/'.$theme.$template.'.tpl' : $_SERVER['DOCUMENT_ROOT'].'/templates/'.$template.'.tpl';
		
		if(file_exists($template_file))
		{
			//include template
			include_once $template_file;
			
			//execute construct function
			if(function_exists($template) && is_callable($template))
			{
				$template(&$this);
			}
			
			return true;
		}
		else
		{
			//template non-existent
			return trigger_error('Template file "'.$template_file.'" is non-existent!', E_USER_ERROR);
		}
	}
	public function assign_vars(array $vars)
	{
		$this->vars = array_merge($this->vars, $vars);
	}
	private function assign_global_vars()
	{
		$std = new stdclass;
		
		foreach($GLOBALS as $key => $value)
		{
			//void encoding objects etc...
			if(in_array(gettype($value), array('object', 'resource', 'function')))
			{
				$std->$key = $value;
			}
			//strings/arrays/ints
			else if((is_scalar($value) || is_array($value)) && $key != 'GLOBALS') //avoid recursion...
			{
				$std->$key = val::encode($value);
			}
		}
		
		return $std;
	}
	public function compile($function, $return = false)
	{
		global $config_init;
		
		//no return - use output buffering
		if(!$return)
		{
			//flush current output buffer...if it exists
			if(ob_get_length())
			{
				ob_flush();
			}
			//start a new buffer
			else if(ob_get_length() === false)
			{
				ob_start(/*(!(bool) $config_init->get_config('debug_mode')) ? 'ob_gzhandler' : null*/);
			}
			
			//execute template function
			if(function_exists($function) && is_callable($function))
			{
				$function((object) $this->vars, (object) self::assign_global_vars());
			}
			else
			{
				trigger_error('Template function "'.$function.'" is non-existent!', E_USER_ERROR);
			}
			
			//get template contents
			$template = ob_get_contents();
			
			//clear output buffer
			ob_clean();
		}
		//return template
		else
		{
			if(function_exists($function) && is_callable($function))
			{
				$template = $function((object) $this->vars, (object) self::assign_global_vars());
			}
		}
		
		//return contents
		return $template;
	}
	public function buffer($buffer = null)
	{
		//append to buffer
		if($buffer !== null)
		{
			$this->buffer .= $buffer;
		}
		//return buffer
		else
		{
			return $this->buffer;
		}
	}
	public function lang($lang_key)
	{
		global $config_init;
		static $lang_loaded = false;
		
		if(!$lang_loaded)
		{
			$config_init->load($_SERVER['DOCUMENT_ROOT'].'/templates/lang.cfg')->parse();
			$lang_loaded = true;
		}
		
		return $config_init->get_config('lang.'.$lang_key);
	}
	#extends page, profile, form, parse
	public static function header_active($arg1, $arg2, $arg3, $arg4 = 'style="color: blue;"'){return page::header_active($arg1, $arg2, $arg3, $arg4);}
	public static function profile_link($arg1, $arg2 = null){return profile::profile_link($arg1, $arg2);}
	public static function unique_hash(){return form::unique_hash();}
	public static function get_query_string($arg1 = null){return form::get_query_string($arg1);}
	public static function parse_options(array $arg1 = null, $arg2 = null){return parse::parse_options($arg1, $arg2);}
}

//code parsing
/*interface*/abstract class _code extends template
{
	protected $code_vars;
	abstract public function __call($method, array $args);
	abstract public function geshi($code);
	abstract public function parse_code($code, $parse_code_ws = false);
	abstract public function parse_code_ws($code);
	abstract protected function codinate($code);
	abstract public function smiley($code);
	abstract protected function preg_replace_recursive($code, $code_parse);
	abstract public function strip_code_tags($code, $tags = '\[.*?\]');
	abstract public function parse_code_php($code);
	abstract protected function magic_url($code);
} include 'geshi.php';

return $config_init;
?>