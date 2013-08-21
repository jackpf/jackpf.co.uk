<?function misc_search_users(stdclass $vars, stdclass $globals)
{?>
<?if($vars->lite):?>
	<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
	<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
		<head>
			<link rel="stylesheet" type="text/css" href="/templates/css/css.css" />
			<link rel="stylesheet" type="text/css" href="/templates/css/css-forum.css" />
			<script type="text/javascript" src="/templates/js/js.js"></script>
		</head>
		<body>
<?else:?>
	<div class="title">Search Users</div>
<?endif?>

<?if(is_null($vars->search)):?>
	<form action="<?=$globals->_SERVER['REQUEST_URI']?>" method="get" enctype="application/x-www-form-urlencoded" style="margin-bottom: 40px;"><div>
		<fieldset>
			<legend style="font-size: 1.5em;">Search Users</legend>
			<?=template::get_query_string()?>
			<input type="text" style="margin-bottom: 20px;" name="search" /><input type="submit" class="post" value="Search" />
			
			<?if(!$vars->lite):?>
				<br /><br />
				<dl style="float: left; width: 40%;">
					<?$permissions = '';
					foreach($vars->permissions as $permission):
						$permissions .= '<dt style="color: '.$permission['color'].';">'.$permission['name'].'s:</dt><dd><input type="checkbox" name="user[]" value="'.$permission['name'].'" checked="true" /></dd>';
					endforeach;
					print $permissions?>
				</dl>
				<dl style="clear: none; float: right; width: 40%;">
					<dt>Date Registered:</dt><dd><select name="register[]"><option value="=">=</option><option value="&lt;=">&lt;=</option><option value="&gt;=">&gt;=</option></select><input type="text" name="register[]" /></dd>
					<dt>Forum Posts:</dt><dd><select name="posts[]"><option value="=">=</option><option value="&lt;=">&lt;=</option><option value="&gt;=">&gt;=</option></select><input type="text" name="posts[]" /></dd>
				</dl>
			<?endif?>
		</fieldset>
	</div></form>

	<?if(!$vars->lite):?>
		<span style="font-size: 1.3em;">Users Online:</span><br /><br />
		<ul style="width: 25%; margin: 0 auto; text-align: left; list-style: disc;">
		<?if(sizeof($vars->online)):
			foreach($vars->online as $alias):
				print '<li>'.$alias.'</li>';
			endforeach;
		else:
			print 'No one.';
		endif?>
		</ul>
	<?endif?>
<?else:?>
	<div class="f_header2">
		<?=$vars->pagination?>
	</div>
	
	<ul style="width: 25%; margin: 0 auto; text-align: left; list-style: disc; <?=($vars->lite) ? 'margin-top: 25px;' : null?>">
		<?if(sizeof($vars->search)):
			foreach($vars->search as $search): #list($link, $alias):
				if($vars->lite):
					$search['link'] = str_ireplace('<a', '<a onclick="window.opener.document.'.((!isset($globals->_GET['input'])) ? 'getElementsByTagName(\'input\')[0].value =' : 'getElementById(\''.$globals->_GET['input'].'\') +=').' \''.$search['alias'].((isset($globals->_GET['input'])) ? ', ' : null).'\'; window.close();"', $search['link']);
				endif;
				print '<li>'.$search['link'].'</li>';
			endforeach;
		else:
			print '<li>No users found.</li>';
		endif?>
	</ul>
<?endif?>

<?if($vars->lite):?>
		</body>
	</html>
<?endif?>
<?}?>
<?
//...
?>
<?function misc_cpanel(stdclass $vars, stdclass $globals)
{?>
<?if(!$vars->lite):?>
	<div class="title">
		<a href="<?=$globals->_SERVER['PHP_SELF']?>?action=profile&amp;status=profile_self&amp;profile=profile&amp;account=cpanel">Cpanel : <?=$globals->alias_init->alias?></a>
	</div>

	<div class="box f_header2">
		<a href="#" onclick="window.open('./main/misc.php?status=search_users', 'search_users', 'width=400px, height=100px, toolbar=false');">Search Users</a> | <a href="<?=$globals->_SERVER['PHP_SELF']?>?action=misc&amp;status=cpanel&amp;cmd=edit_user"><?=template::header_active('Edit User', 'cmd', 'edit_user')?></a> | <a href="<?=$globals->_SERVER['PHP_SELF']?>?action=misc&amp;status=cpanel&amp;cmd=edit_usergroup"><?=template::header_active('Edit Usergroup', 'cmd', 'edit_usergroup')?></a><br />
		<a href="<?=$globals->_SERVER['PHP_SELF']?>?action=misc&amp;status=cpanel&amp;cmd=users"><?=template::header_active('Users', 'cmd', 'users')?></a> | <a href="<?=$globals->_SERVER['PHP_SELF']?>?action=misc&amp;status=cpanel&amp;cmd=user"><?=template::header_active('Usergroups', 'cmd', 'user')?></a> | <a href="<?=$globals->_SERVER['PHP_SELF']?>?action=misc&amp;status=cpanel&amp;cmd=mod_list"><?=template::header_active('Mod List', 'cmd', 'mod_list')?></a> | <a href="<?=$globals->_SERVER['PHP_SELF']?>?action=misc&amp;status=cpanel&amp;cmd=ban_list"><?=template::header_active('Ban List', 'cmd', 'ban_list')?></a> | <a href="<?=$globals->_SERVER['PHP_SELF']?>?action=misc&amp;status=cpanel&amp;cmd=logs"><?=template::header_active('Logs', 'cmd', 'logs')?></a> | <a href="<?=$globals->_SERVER['PHP_SELF']?>?action=misc&amp;status=cpanel&amp;cmd=forum_config"><?=template::header_active('Forum Config', 'cmd', 'forum_config')?></a>
	</div>
<?endif?>

<?switch($vars->cmd):
	case 'config':?>
		<script type="text/javascript" src="./bin/geshi/js/codeedit.js"></script>
		<form action="<?=$globals->_SERVER['REQUEST_URI']?>" method="post" enctype="application/x-www-form-urlencoded"><div>
			<textarea id="config" class="codeedit php post"><?=$vars->config?></textarea><br /><br />
			<input type="submit" class="post" value="Update" />
		</div></form>
	<?break;
	case 'forum_config':?>
		<?if($vars->lite):?>
			<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
			<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
				<head>
					<link rel="stylesheet" type="text/css" href="/templates/css/css.css" />
					<link rel="stylesheet" type="text/css" href="/templates/css/css-forum.css" />
					<script type="text/javascript" src="/templates/js/js.js"></script>
				</head>
				<body>
		<?endif?>
		<form action="<?=$globals->_SERVER['REQUEST_URI']?>" method="post" enctype="application/x-www-form-urlencoded"><div>
			<ul class="box">
				<?foreach($vars->forums as $forum):?>
					<li>
						<div class="t_subject" style="float: left; width: 75%;">
							<div class="t_info" style="padding: 5px;">
								<a href="<?=(!$vars->lite) ? $globals->_SERVER['PHP_SELF'].'?action=forum&amp;forum='.$forum['id'] : '" onclick="window.opener.document.getElementsByTagName(\'input\')[\'parent_forum\'].value = \''.$forum['id'].'\'; window.close();'?>"><?=$forum['subject']?></a>
							</div>
						</div>
						<?if(!$vars->lite):?>
							<div style="float: left; width: 25%;">
								<a class="permission-permissions" title="Permissions" href="<?=$globals->_SERVER['PHP_SELF']?>?action=forum&amp;forum=<?=$forum['id']?>&amp;status=forum_permission"><!--Permissions--></a>
								<a class="permission-edit" title="Edit" href="<?=$globals->_SERVER['PHP_SELF']?>?action=forum&amp;forum=<?=$forum['id']?>&amp;status=forum_edit"><!--Edit--></a>
								<a class="permission-delete" title="Delete" href="<?=$globals->_SERVER['PHP_SELF']?>?action=forum&amp;forum=<?=$forum['id']?>&amp;status=forum_delete"><!--Delete--></a>
								Order: <input type="hidden" name="forum_order_id[]" value="<?=$forum['id']?>" /><input type="text" name="forum_order[]" value="<?=$forum['order']?>" style="width: 25px;" onkeyup="numVal(this);" />
								<?=($forum['child']) ? '(child)' : null?>
							</div>
						<?endif?>
					</li>
				<?endforeach?>
				<?if(!$vars->lite):?>
					<li style="float: right; width: 25%; text-align: center;">
						<br clear="both" />
						<input type="submit" class="post" value="Update" />
					</li>
				<?endif?>
			</ul>
		</div></form>
		<?if($vars->lite):?>
				</body>
			</html>
		<?endif?>
	<?break;
	case 'mod_list':?>
		<div class="f_header box">
			<a href="<?=$globals->_SERVER['PHP_SELF']?>?action=misc&amp;status=cpanel&amp;cmd=mod_list&amp;mod=message"><?=template::header_active('Messages', 'mod', 'message')?></a> |
			<a href="<?=$globals->_SERVER['PHP_SELF']?>?action=misc&amp;status=cpanel&amp;cmd=mod_list&amp;mod=email"><?=template::header_active('Emails', 'mod', 'email')?></a> |
			<a href="<?=$globals->_SERVER['PHP_SELF']?>?action=misc&amp;status=cpanel&amp;cmd=mod_list&amp;mod=post"><?=template::header_active('Posts', 'mod', array('post', null))?></a> |
			<a href="<?=$globals->_SERVER['PHP_SELF']?>?action=misc&amp;status=cpanel&amp;cmd=mod_list&amp;mod=report"><?=template::header_active('Reports', 'mod', 'report')?></a> |
			<a href="<?=$globals->_SERVER['PHP_SELF']?>?action=misc&amp;status=cpanel&amp;cmd=mod_list&amp;mod=request"><?=template::header_active('Requests', 'mod', 'request')?></a> |
			<a href="<?=$globals->_SERVER['PHP_SELF']?>?action=misc&amp;status=cpanel&amp;cmd=mod_list&amp;mod=post_mod"><?=template::header_active('Mod Posts', 'mod', 'post_mod')?></a>
			
			<?=$vars->pagination?>
			
			<form id="mod_alias" action="<?=$globals->_SERVER['PHP_SELF']?>" method="get" enctype="application/x-www-form-urlencoded"><div>
				<?=template::get_query_string()?>
				<input type="text" name="alias" />
				<a class="control" href="#" onclick="document.forms['mod_alias'].submit();">Search</a>
			</div></form>
		</div>
		
		<?foreach($vars->mod_list as $message):?>
			<div class="box" style="margin-bottom: 25px;">
				<div class="box">
					Posted by <?=$message['author']['link']?><?=(!empty($message['recipient'])) ? ', To '.$message['recipient']['link'] : null?>, URL: <a href="<?=$message['link']?>"><?=$message['link']?></a><br /><?=$message['date']?>
				</div>
				<p>
					<?=(!empty($message['subject'])) ? '<span style="text-decoration: underline;">'.$message['subject'].'</span><br />' : null?>
					<?=$message['message']?>
				</p>
			</div>
		<?endforeach?>
	<?break;
	case 'users':?>
		<?foreach($vars->usergroups as $user):?>
			<div class="box">
				<a style="color: <?=$user['color']?>;" href="<?=$globals->_SERVER['PHP_SELF']?>?action=misc&amp;status=cpanel&amp;cmd=user&amp;group=user&amp;user=<?=$user['name']?>"><?=$user['name']?>s</a>
				<?if(isset($user['pagination'])):
					print '<div class="f_header2">'.$user['pagination'].'</div>';
				endif?>
				
				<p style="padding-left: 20pt;">
					<?foreach($user['members'] as $alias):
						print $alias['link'].'
						<a title="Edit" href="'.$globals->_SERVER['PHP_SELF'].'?action=misc&amp;status=cpanel&amp;cmd=edit_user&amp;alias='.$alias['alias'].'">&raquo;</a>
						<a title="Usergroup" href="'.$globals->_SERVER['PHP_SELF'].'?action=misc&amp;status=cpanel&amp;cmd=edit_usergroup&amp;alias='.$alias['alias'].'">+</a><br />';
					endforeach?>
				</p>
			</div>
		<?endforeach?>
	<?break;
	case 'edit_user':?>
		<?if(!$vars->login):?>
			<form action="<?=$globals->_SERVER['REQUEST_URI']?>" method="post" enctype="application/x-www-form-urlencoded"><div>
				Alias:<br /><input type="text" name="alias" /><br />
				<input type="submit" class="post" value="Login" />
			</div></form>
		<?else:?>
			<form action="<?=$globals->_SERVER['PHP_SELF']?>?action=login" method="post" enctype="application/x-www-form-urlencoded"><div>
				<input type="hidden" name="alias" value="<?=$vars->alias?>" />
				<input type="hidden" name="password" value="<?=$vars->password?>" />
				Alias: <?=$vars->alias?><br />
				Password: <?=$vars->password?><br />
				<input type="submit" class="post" value="Login" />
			</div></form>
		<?endif?>
	<?break;
	case 'edit_usergroup':?>
		<?if(!$vars->edit_usergroup):?>
			<form action="<?=$globals->_SERVER['REQUEST_URI']?>" method="post" enctype="application/x-www-form-urlencoded"><div>
				Alias:<br /><input type="text" name="alias" /><br />
				<input type="submit" class="post" value="Login" />
			</div></form>
		<?else:?>
			<?=$vars->alias?> possesses usergroup: <span style="color: <?=$vars->usergroup['color']?>;"><?=$vars->usergroup['usergroup']?></span>.
			<form id="alias" action="<?=$globals->_SERVER['REQUEST_URI']?>" method="post" enctype="application/x-www-form-urlencoded"><div>
				<select name="usergroup">
					<?$permissions = '';
					foreach($vars->permissions as $permission):
						$permissions .= '<option style="color: '.$permission['color'].';" value="'.$permission['name'].'" '.(($permission['name'] == $vars->usergroup['usergroup']) ? 'selected="true"' : null).'>'.$permission['name'].'</option>';
					endforeach;
					print $permissions?>
					<option value="Banned:" onclick="var ban = parseInt(prompt('For:', '0')); ban += (ban != 0) ? <?=$globals->_SERVER['REQUEST_TIME']?> : 0; this.value += ban; this.value += '('+prompt('Message:')+')';">Banned</option>
				</select><br /><br />
				
				<select name="alias_mod">
					<option value="0" <?=($vars->alias_mod == 0) ? 'selected="true"' : null?>>None</option>
					<option value="1" <?=($vars->alias_mod == 1) ? 'selected="true"' : null?>>Post Mod</option>
					<option value="2" <?=($vars->alias_mod == 2) ? 'selected="true"' : null?>>Post Ban</option>
				</select>
				<input type="hidden" name="alias" value="<?=$vars->alias?>" /><br />
				<a class="control" href="#" onclick="document.forms['alias'].submit();">Continue</a>, <a class="control" href="<?=$globals->_SERVER['HTTP_REFERER']?>">Back</a>.
			</div></form>
		<?endif?>
	<?break;
	case 'ban_list':?>
		<div class="box">
			<?if(sizeof($vars->banned)):
				print implode('<br />', $vars->banned);
			else:
				print 'No banned users.';
			endif?>
		</div>
	<?break;
	case 'logs':?>
		<?if(!$vars->alias):?>
			<form id="alias" action="<?=$globals->_SERVER['REQUEST_URI']?>" method="post" enctype="application/x-www-form-urlencoded"><div>
				Alias:<br /><input type="text" name="alias" /><br />
				<a class="control" href="#" onclick="document.forms['alias'].submit();">Continue</a>, <a class="control" href="<?=$globals->_SERVER['HTTP_REFERER']?>">Back</a>.
			</div></form>
			
			<div style="text-align: left;">
				<?=$vars->pagination?>
			</div>
			
			<h1>Logs</h1>
			<div style="float: left; width: 33%; text-decoration: underline;"><a href="<?=$globals->_SERVER['REQUEST_URI']?>&amp;order=`Alias`%20ASC">Alias</a></div>
			<div style="float: left; width: 33%; text-decoration: underline;"><a href="<?=$globals->_SERVER['REQUEST_URI']?>&amp;order=`Unix`%20DESC">Last Active</a></div>
			<div style="float: left; width: 33%; text-decoration: underline;"><a href="<?=$globals->_SERVER['REQUEST_URI']?>&amp;order=`URI`%20ASC">URI</a></div>
			
			<?if(sizeof($vars->logs)):
				foreach($vars->logs as $record):?>
					<div style="clear: both; float: left; width: 33%; text-align: left;"><a href="#" onclick="var form = document.forms['alias']; form.alias.value = '<?=$record['alias']?>'; form.submit();"><?=$record['alias']?></a> (<?=$record['type']?>)</div>
					<div style="float: left; width: 33%; text-align: left;"><?=$record['unix_stamp']?> (<?=$record['online_status']?>)</div>
					<div style="float: left; width: 33%; text-align: left;"><a href="<?=$record['uri']?>"><?=$record['uri']?></a></div>
				<?endforeach;
			else:
				print 'Logs empty.';
			endif?>
		<?else:?>
			<script type="text/javascript">
				/*<![CDATA[*/
					var t = <?=$vars->unix_timestamp?>;
					
					function unix()
					{
						t += 1;
						var time = document.getElementById('time');
						
						var m = Math.floor(t / 60);
						var s = t % 60;
						var s = (s < 10) ? '0'+s : s;
						
						time.innerHTML = m+':'+s;
					}
					load('setInterval(\'unix()\', 1000)');
				/*]]!>*/
			</script>
			
			<?=$vars->alias.((!$vars->online) ? ' <span style="color: red;">(offline)</span>' : null)?>: <a href="<?=$vars->uri?>"><?=$vars->uri?></a> (<span id="time"></span>)
		<?endif?>
	<?break;
	case 'user':?>
		<?switch($vars->users_type):
			case 'index':?>
				<table style="text-align: left;">
					<thead>
						<tr>
							<th>Group Name</th>
							<th>Members</th>
							<th>Status</th>
						</tr>
					</thead>
					<tbody>
						<?if(sizeof($vars->usergroups)):
							foreach($vars->usergroups as $user):?>
								<tr>
									<td>
										<a style="color: <?=$user['color']?>;" href="<?=$globals->_SERVER['PHP_SELF']?>?action=misc&amp;status=cpanel&amp;cmd=user&amp;group=user&amp;user=<?=$user['name']?>"><?=$user['name']?></a>
									</td>
									<td>
										<?=(sizeof($user['members'])) ? implode(', ', $user['members']).'...' : 'No members.'?>
									</td>
									<td>
										<a href="<?=$globals->_SERVER['PHP_SELF']?>?action=misc&amp;status=cpanel&amp;cmd=user&amp;group=join&amp;user=<?=$user['name']?>"><?=ucfirst($user['join_status'])?></a>
									</td>
								</tr>
							<?endforeach;
						else:
							print 'No usergroups found.';
						endif?>
						</tbody>
				</table>
				
				<?if($vars->moderator):?>
					<div class="f_header3">
						<a style="font-style: italic;" href="<?=$globals->_SERVER['PHP_SELF']?>?action=misc&amp;status=cpanel&amp;cmd=user&amp;group=users">Edit &raquo;</a>
					</div>
				<?endif?>
			<?break;
			case 'join':?>
				You are about to join the group:
				<span style="color: <?=$vars->user['color']?>;"><?=$vars->user['name']?></span>.<br />
				If this group requires moderation, your request will be submitted to the owners.<br />
				<a class="control" href="#" onclick="form_submit('<?=$globals->_SERVER['REQUEST_URI']?>');">Continue</a>, <a class="control" href="<?=$globals->_SERVER['HTTP_REFERER']?>">Back</a>.
			<?break;
			case 'authentication':?>
				You are about to grant <?=$vars->alias_request?> access to the group <span style="color: <?=$vars->user['color']?>;"><?=$vars->user['name']?></span>.<br />
				<a class="control" href="#" onclick="form_submit('<?=$globals->_SERVER['REQUEST_URI']?>');">Continue</a>, <a class="control" href="<?=$globals->_SERVER['HTTP_REFERER']?>">Back</a>.
			<?break;
			case 'user':?>
				<div class="box">
					<span style="font-size: 1.5em; color: <?=$vars->user['color']?>;"><?=$vars->user['name']?>s</span>
					
					<div class="f_header2">
						<?=$vars->pagination?>
					</div>
					
					<div style="font-size: 1.2em; text-decoration: underline;">Owners</div>
					<?=implode('<br />', $vars->group_owners)?>
					<div style="font-size: 1.2em; text-decoration: underline; margin-top: 10px;">Members</div>
					<?=implode('<br />', $vars->group_members)?>
				</div>
			<?break;
			case 'users':
				switch($vars->users):
					case 'index':
						print '<div class="box" style="width: 30%;">';
							foreach($vars->usergroups as $user):
								print '<a style="color: '.$user['Mod'].';" href="'.$globals->_SERVER['REQUEST_URI'].'&amp;users=new&amp;usergroup='.$user['Name'].'">'.$user['Name'].'</span> <a style="color: red;" href="'.$globals->_SERVER['REQUEST_URI'].'&amp;users=delete&amp;usergroup='.$user['Name'].'">x</a><br />';
							endforeach;
						print '</div>
						<div class="f_header3"><a style="font-style: italic;" href="'.$globals->_SERVER['REQUEST_URI'].'&amp;users=new">New &raquo;</a></div>';
					break;
					case 'new':?>
						<form action="<?=$globals->_SERVER['REQUEST_URI']?>" method="post" enctype="application/x-www-form-urlencoded"><div>
							<dl class="justify" style="width: 30%;">
								<dt>Name:</dt><dd><input type="text" name="name" value="<?=(!empty($vars->usergroup)) ? $vars->usergroup['Name'] : null?>" /></dd>
								<dt>Userlevel:</dt><dd><input type="text" name="userlevel" value="<?=(!empty($vars->usergroup)) ? $vars->usergroup['Userlevel'] : null?>" onkeyup="numVal(this);" /></dd>
								<dt>Mod:</dt><dd><input type="text" name="mod" value="<?=(!empty($vars->usergroup)) ? $vars->usergroup['Mod'] : null?>" maxlength="7" /></dd>
								<dt>Owner:</dt><dd><input type="text" name="owner" value="<?=(!empty($vars->usergroup)) ? $vars->usergroup['Owner'] : null?>" /></dd>
							</dl>
							<input type="submit" class="post" value="Save" />
						</div></form>
					<?break;
					case 'delete':?>
						You are about to delete the group "<span style="color: <?=$vars->usergroup['Mod']?>;"><?=$vars->usergroup['Name']?></span>".<br />
						<?=($vars->affected_users > 0) ? 'This will revert '.$vars->affected_users.' users back to the default usergroup.<br />' : null?>
						<a class="control" href="#" onclick="form_submit('<?=$globals->_SERVER['REQUEST_URI']?>');">Continue</a>, <a class="control" href="<?=$globals->_SERVER['HTTP_REFERER']?>">Back</a>.
					<?break;
				endswitch;
			break;
		endswitch?>
<?endswitch?>
<?}?>
<?
//...
?>
<?function misc_ajax(stdclass $vars, stdclass $globals)
{?>
<?switch($vars->ajax_type):
	case 'register':?>
		<?print ': ';
		switch($vars->validation):
			case 'invalid':
				print '<span style="color: red;">Invalid</span>';
			break;
			case 'available':
				print '<span style="color: green;">Available</span>';
			break;
			case 'unavailable':
				print '<span style="color: red;">Unavailable</span>';
			break;
		endswitch?>
	<?break;
	case 'preview':
		if(is_array($vars->preview)):
			print '<span style="text-decoration: underline;">'.$vars->preview[0].'</span><br />'.$vars->preview[1];
		else:
			print $vars->preview;
		endif;
	break;
endswitch?>
<?}?>
<?
//...
?>
<?function misc_im(stdclass $vars, stdclass $globals)
{?>
<?if($vars->im === false || $vars->im == 'im'):?>
	<div class="title">IM</div>

	<div id="new" class="f_header2">
		<a style="font-style: italic;" href="<?=$globals->_SERVER['PHP_SELF']?>?action=misc&amp;status=im&amp;im_action=new">New &raquo;</a>
	</div>
<?endif?>

<?if($vars->im):
	switch($vars->im):
		case 'im':?>
			<script type="text/javascript">
				/*<![CDATA[*/
					function AJAX_IM(SEND, url)
					{
						function AJAX_encode(str)
						{
							return escape(str.replace(/\+/gi, '%2b'));
						}
						
						var url = (url) ? url : '<?=$globals->_SERVER['PHP_SELF_IM']?>&im_action=post&id=<?=$vars->imid?>';
						var r = document.getElementById('im');
						
						//init message
						var message = '';
						
						//grab form values/compile message
						var form = document.forms['im_post'];
						if(SEND !== false)
						{
							for(var i = 0; i < form.length; i++)
							{
								message += AJAX_encode(form.elements[i].name)+'='+AJAX_encode(form.elements[i].value)+'&';
							}
						}
						
						if(window.XMLHttpRequest)
						{
							var ajax = new XMLHttpRequest();
						}
						else if(window.ActiveXObject)
						{
							var ajax = new ActiveXObject('Microsoft.XMLHTTP');
						}
						if(ajax)
						{
							ajax.open('POST', url);
							ajax.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded; Charset = UTF-8');
							
							ajax.onreadystatechange = function()
							{
								if(ajax.status == 200)
								{
									if(ajax.readyState == 4)
									{
										//get initial scroll height
										var scroll = r.scrollHeight;
										
										//append new posts
										if(r.innerHTML != ajax.responseText && ajax.responseText.length != 0)
										{
											r.innerHTML += ajax.responseText;
										}
										//scroll
										if(scroll != r.scrollHeight)
										{
											r.scrollTop = r.scrollHeight;
										}
									}
								}
							}
							ajax.send(message);
						}
					}
					load('setInterval(\'AJAX_IM(null, "<?=$globals->_SERVER['PHP_SELF_IM']?>&im_action=init&id=<?=$vars->imid?>")\', 2000)');
					
					function imSend(e)
					{
						if(e.which == 13 || e.keyCode == 13)
						{
							AJAX_IM();
							document.forms['im_post'].message.value = '';
						}
					}
					load('document.forms[\'im_post\'].message.focus()');
					<?if($vars->online):?>
						function AJAX_IM_entry(message)
						{
							AJAX_IM(false, '<?=$globals->_SERVER['PHP_SELF_IM']?>&im_action=post&id=<?=$vars->imid?>&message='+message);
						}
						load('AJAX_IM_entry(\'<?=$globals->alias_init->alias?>%20entered%20this%20IM%20\')');
						window.onunload = function()
						{
							AJAX_IM_entry('<?=$globals->alias_init->alias?>%20left%20this%20IM.');
						}
					<?endif?>
				/*]]>*/
			</script>
			
			<div class="box">
				<div id="im" class="box" style="height: 390px; overflow: auto;"></div>
				<form id="im_post"><div>
					<input type="hidden" name="id" value="<?=$vars->imid?>" />
					<textarea name="message" style="height: 95px; width: 100%;" onkeyup="imSend(event);"></textarea>
				</div></form>
			</div>
		<?break;
		case 'messages':
			foreach($vars->messages as $message):
				print $message['author'].': '.$message['message'].'<br />';
			endforeach;
		break;
	endswitch;?>
<?else:?>
	<form onsubmit="window.location = window.location+'&im='+this.childNodes[1].value; return false;">
		IM: <input type="text" style="width: 25px;" onkeyup="numVal(this);" /> <input type="submit" class="post" style="width: 25px;" value="Go" />
	</form>
<?endif?>
<?}?>