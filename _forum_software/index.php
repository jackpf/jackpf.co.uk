<?php
//beta
header('Location: beta');
//init
include 'config/lib.php';

$alias_init = new alias;

$stat = new stat;
$stat->update();

$action = (isset($_GET['action'])) ? $_GET['action'] : 'index';

ob_start(/*(!(bool) $config_init->get_config('debug_mode')) ? 'ob_gzhandler' : null*/);

header('Content-Type: text/html; Charset = UTF-8');

//construction mode (void login)
if((bool) $config_init->get_config('construction_mode') && $action != 'login')
{
	$alias_init = new alias;
	
	if($alias_init->userlevel < alias::user('Administrator'))
	{
		trigger_error($config_init->get_config('construction_mode_message'));
	}
}

//check valid actions
$files = array();

foreach(glob('main/*.php') as $value)
{
	$value = reset(explode('.', basename($value)));
	$files[$value] = $value;
}

//main
if(array_key_exists($action, $files) && in_array($action, $files))
{
	$index = $files[$action];
}
else
{
	header('HTTP/1.1 404 Not Found');
	trigger_error('This page does not exist.');
}

$index_main = include 'main/'.$index.'.php';

//load index
$tpl = new template('index');

$tpl->assign_vars(array(
'index_main' => $index_main,
'alias'      => (isset($alias_init->alias)) ? val::encode($alias_init->alias).$alias_init->alias_mod($alias_init->alias, 'lite') : null,
'title'      => array($config_init->get_config('site_header'), $index),
'header'     => $config_init->get_config('site_header'),
'footer'     => $config_init->get_config('site_footer'),
));

echo $tpl->compile('index_main');

ob_end_flush();
?>