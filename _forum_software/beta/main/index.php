<?php
$db = new connection;
$alias_init = new alias;
$tpl = new template('index2');

//stats
$stat = new stat;
$stats = $stat->fetch_stats();

$sql = $db->query("SELECT COUNT(`ID`) as `AliasCount` FROM `{$db->tb->Alias}`;");
$fetch = $db->fetch_array($sql);
$stats['total'] = $fetch['AliasCount'];
$stats['online'] = count($stats['aliases']) + $stats['aliases_hidden'] + $stats['strangers'] + count($stats['bots']);

foreach($stats['aliases'] as &$value)
{
	$value = template::profile_link(val::encode($value), 'style="color: '.$alias_init->alias_mod($value, 'lite:Mod').';"');
}

$sql = $db->query("SELECT `Unix`, `Unix_Total` FROM `{$db->tb->Alias_Stats}` WHERE `Alias`='ONLINE' AND `Type`='const';");
$fetch = $db->fetch_array($sql);

$stats['most_online'] = array('count' => $fetch['Unix_Total'], 'date' => val::unix($fetch['Unix']));

$sql = $db->query("SELECT `Type`, COUNT(*) AS `PostCount` FROM `{$db->tb->Forum}` GROUP BY `Type` WITH ROLLUP;");

$post_count = array(
'forum'  => 0,
'thread' => 0,
'post'   => 0
);

while($fetch = $db->fetch_array($sql))
{
	$post_count[$fetch['Type']] += $fetch['PostCount'];
}

$post_count['post'] += $post_count['thread'];
$post_count['total'] = $post_count['post'] + $post_count['forum'];

foreach($post_count as &$value)
{
	$value = val::number_format($value);
}

$tpl->assign_vars(array(
#'index'      => parse::parse(str_replace(/*hack: replace cfg formatting*/"\t", null, $config_init->get_config('index')), parse::options_parse_code | parse::options_parse_code_ws | parse::options_parse_smiley),
'stats'      => $stats,
'post_count' => $post_count
));
return $tpl->compile('index2_main');
?>