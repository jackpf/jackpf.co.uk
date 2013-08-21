<?php
function dev_init()
{
	global $db, $alias_init;
	
	$sql = $db->query("SELECT `ID` FROM `{$db->tb->Alias}` WHERE `Alias`='".val::post($alias_init->alias)."';");
	$fetch = $db->fetch_array($sql);
	
	$alias_init->aliasid = $fetch['ID'];
}
?>