<?function config_error(stdclass $vars, stdclass $globals)
{?>
<div class="title">Error</div>
<div class="box" style="width: 75%; min-height: 150px; border: 1px solid red; background-color: #FEE0C6;">
	<div style="padding: 10px; font-size: 1.2em; font-weight: bold; border-bottom: 1px solid red; margin-bottom: 10px;">
		An Error Has Occurred!
	</div>
	<?=$vars->error_message?>
	<div style="left: 10px; bottom: 10px; position: absolute;">
		<a class="control" style="font-size: 1.1em;" href="<?=(!empty($globals->_SERVER['HTTP_REFERER'])) ? $globals->_SERVER['HTTP_REFERER'] : $globals->_SERVER['PHP_SELF']?>" onclick="history.go(-1); return false;">&laquo; Back</a>
	</div>
	<div style="right: 10px; bottom: 10px; position: absolute;">
		<?if(form::submitted()):
			print '<form id="config_error" action="'.$globals->_SERVER['REQUEST_URI'].'" method="post" enctype="application/x-www-form-urlencoded"><div>';
				foreach($globals->_POST as $key => $value):
					print '<input type="hidden" name="'.$key.'" value="'.$value.'" />';
				endforeach;
			print '</div></form>';
		endif?>
		<a class="control" style="font-size: 1.1em;" href="<?=$globals->_SERVER['REQUEST_URI']?>" <?=(form::submitted()) ? 'onclick="document.forms[\'config_error\'].submit(); return false;"' : null?>>Continue &raquo;</a>
	</div>
</div>
<?}?>
<?
//...
?>
<?function config_pagination(stdclass $vars, stdclass $globals)
{?>
<?print ($vars->pages) ? '<em class="pagination_pages">Pages &bull;</em>' : null;
if($vars->current_page > 3):
	print '<a class="pagination'.(($vars->current_page == 1) ? ' pagination_active' : null).'" href="'.$vars->uri.'1">1</a>'.(($vars->current_page > 4) ? '<span class="pagination_seperator">&hellip;</span>' : null);
endif;

for($i = $vars->current_page - 2; $i <= $vars->current_page + 2 && $i < $vars->last_page; $i++):
	if($i <= 0)
		continue;
	
	print '<a class="pagination'.(($vars->current_page == $i) ? ' pagination_active' : null).'" href="'.$vars->uri.$i.'">'.$i.'</a>';
	print ($i == $vars->current_page + 2 && $vars->last_page > $vars->current_page + 3) ? '<span class="pagination_seperator">&hellip;</span>' : null;
endfor;

print '<a class="pagination'.(($vars->current_page == $vars->last_page) ? ' pagination_active' : null).'" href="'.$vars->uri.(($vars->last_page > 0) ? $vars->last_page : 1).'">'.(($vars->last_page > 0) ? $vars->last_page : 1).'</a>'?>
<?}?>
<?
//...
?>
<?function config_alias_mod(stdclass $vars, stdclass $globals)
{?>
<?#print template::profile_link($vars->alias, 'style="color: '.(($vars->mod_type != 'lite') ? $vars->color : null).';"');
if($vars->mod):
	print ($vars->crlf) ? '<br />' : ' ';
	print '<span class="mod'.((in_array($vars->mod_type, array('full', 'full_forum'))) ? '_full' : '" style="color: '.$vars->color).'">'.$vars->user.'</span>';
elseif(reset(explode(':', $vars->_user)) == 'Banned'):
	print ($vars->crlf) ? '<br />' : ' ';
	print '<span class="mod'.((in_array($vars->mod_type, array('full', 'full_forum'))) ? '_full' : '" style="color: '.$vars->color).'">'.reset(explode(':', $vars->_user)).'</span>';
endif;
print ($vars->crlf) ? '<br />' : null;
if(!empty($vars->picture) && in_array($vars->mod_type, array('full', 'full_forum'))):
	print '<img class="alias_mod_picture" src="'.$vars->picture.'" alt="'.$vars->alias.'\'s Picture" /><br />';
endif;
if($vars->mod_type == 'full_forum'):
	print ($vars->online) ? '<span class="mod_full_2" style="color: green;font-size:0.8em;">Online</span><br />' : null;
	print '<br />';
	print ($vars->posts > -1) ? '<span class="mod_full"><span class="mod_full_2">Posts:</span> '.$vars->posts.'</span><br />' : null;
	print ($vars->rep > -1) ? '<span class="mod_full"><span class="mod_full_2">Reputation:</span> '.$vars->rep.'</span><br />' : null;
	print ($vars->join_date != null) ? '<span class="mod_full"><span class="mod_full_2">Join Date:</span> '.$vars->join_date.'</span><br /><br />' : null;
	print ($vars->message_link) ? '<a class="profile_message" href="'.$globals->_SERVER['PHP_SELF'].'?action=profile&amp;status=profile&amp;profile=message&amp;message_send=1&amp;alias='.$vars->alias.'">&nbsp;</a>' : null;
	print ($vars->email_link) ? '<a class="profile_email" href="'.$globals->_SERVER['PHP_SELF'].'?action=profile&amp;status=profile&amp;profile=email&amp;alias='.$vars->alias.'">&nbsp;</a>' : null;
endif?>
<?}?>
<?
//...
?>
<?function config_parse_options(stdclass $vars, stdclass $globals)
{?>
<?
$return = '<div style="text-align: left;">
	<span style="text-decoration: underline;">Options</span>
	(<a style="font-size: 0.8em;" href="#options" onclick="fade(\'options\'); this.innerHTML = (this.innerHTML == \'show\') ? \'hide\' : \'show\';">show</a>)
	
	<div id="options" style="display: none;">';
	
	$header = $main = array();
	
	foreach($vars->options as $key => $value):
		$option_id = preg_replace('/\s/', '_', $key);
		
		$header[] = '<a id="'.$option_id.'_header" '.((count($header) == 0) ? 'class="header-active"' : null).' href="#options" onclick="option(\''.$option_id.'\', new Array(\''.implode('\', \'', array_map(create_function('$options', 'return preg_replace(\'/\s/\', \'_\', $options);'), array_keys($vars->options))).'\'));">'.$key.'</a>'; #function($options){return preg_replace('/\s/\', '_', $options);}
		
		$option_main = '';
		foreach($value as $key2 => $value2):
			if(!is_array($value2)):
				$option_main .= '<dt>'.$key2.':</dt><dd>'.$value2.'</dd>';
			elseif(is_array($value2)):
				$option_main .= '<dt>'.$key2.':</dt><dd><input type="checkbox" name="'.$value2[0].'" '.(($value2[1]) ? 'checked="true"' : null).' /></dd>';
			endif;
		endforeach;
		
		$main[] = '<dl id="'.$option_id.'" style="display: '.((count($main) == 0) ? 'block' : 'none').';">'.$option_main.'</dl>';
	endforeach;
	
	$return .= '<div id="options_header">'.implode(' | ', $header).'</div>'.implode($main).
	'</div>
</div>';

return $return;
?>
<?}?>
<?
//...
?>
<?function config_parse_signature(stdclass $vars, stdclass $globals)
{?>
<?
$return = '<div class="signature">'.$vars->signature.'</div>';

return $return;
?>
<?}?>