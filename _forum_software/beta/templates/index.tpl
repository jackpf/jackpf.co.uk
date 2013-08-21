<?function index_main(stdclass $vars, stdclass $globals)
{?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
	<head>
		<title>Jackpf.co.uk: <?=ucwords((isset($globals->_GET['action'])) ? $globals->_GET['action'] : 'index')?></title>
		<link rel="icon" type="image/x-icon" href="./templates/css/img/icon.ico" />
		<link rel="stylesheet" type="text/css" href="./templates/css/css.css" />
		<link rel="stylesheet" type="text/css" href="./templates/css/css-forum.css" />
		<link rel="stylesheet" type="text/css" href="./templates/css/css-profile.css" />
		<script type="text/javascript" src="./templates/js/js.js"></script>
		<script type="text/javascript" src="./templates/js/ajax.js"></script>
		<?if(stristr($globals->_SERVER['HTTP_USER_AGENT'], 'msie'))
			include $globals->_SERVER['DOCUMENT_ROOT'].'/templates/css/css-conditional.css';?>
	</head>
	<body>
		<div class="shell">
			<div class="header">
				<ul class="header">
					<li class="site_header">
						Jackpf.co.uk
						<div id="alias">
							<?=(isset($vars->alias)) ? 'You are logged in as <strong>'.$vars->alias.'</strong>' : /*$globals->alias_init->user['Stranger']['Name'].'...'*/'You are not logged in...'?><br />
							&nbsp;&nbsp;&nbsp;&nbsp;<?=($vars->note > 0) ? 'You have <a style="font-weight: bold; color: white;" href="'.$globals->_SERVER['PHP_SELF'].'?action=profile&amp;status=profile_self&amp;profile=index">'.$vars->note.'</a> unread notification'.(($vars->note <> 1) ? 's' : null).'.' : null?>
						</div>
					</li>
					<li>
						<a href="<?=$globals->_SERVER['PHP_SELF']?>"><?=template::header_active('Index', 'action', array('index', null), 'class="header-active"')?></a>
					</li>
					<li>
						<a href="<?=(!isset($vars->alias)) ? $globals->_SERVER['PHP_SELF'].'?action=login' : $globals->_SERVER['PHP_SELF'].'?action=login&amp;status=logout'?>"><?=template::header_active((!isset($vars->alias)) ? 'Login' : 'Logout', 'action', array('login', 'logout', 'register'), 'class="header-active"')?></a>
					</li>
					<li>
						<a href="<?=$globals->_SERVER['PHP_SELF']?>?action=forum"><?=template::header_active('Forum', 'action', 'forum', 'class="header-active"')?></a>
					</li>
					<li>
						<a href="<?=$globals->_SERVER['PHP_SELF']?>?action=profile&amp;status=profile_self&amp;profile=index"><?=template::header_active('Profile', 'action', 'profile', 'class="header-active"')?></a>
					</li>
					<li>
						<a href="/blog"><?=template::header_active('Blog', 'action', 'blog', 'class="header-active"')?></a>
					</li>
					<li id="footer">
						[<?=template::profile_link($globals->config_init->get_config('site_owner'))?>]
					</li>
				</ul>
			</div>
			<div class="main">
				<?=$vars->index_main?>
			</div>
			<div class="_footer"></div><div class="footer"><p>coded & designed by <a href="/index.php?action=profile&status=profile&profile=index&alias=jackpf" style="border-bottom: 1px dotted purple;"><span style="font-weight: bold;">jack</span></a> &copy; 2008-2011 &bull; <a style="border-bottom: 1px dotted purple;" href="mailto:webmaster@jackpf.co.uk">contact me</a></p></div>
		</div>
	</body>
</html>
<?}?>