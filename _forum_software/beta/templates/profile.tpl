<?function profile_main(stdclass $vars, stdclass $globals)
{?>
<div class="title">
	<?if(!empty($vars->profile_init->alias_init2->alias)):
		print ($vars->profile_init->profile_self) ? 'My Profile' : $vars->profile_init->alias_init2->alias.'\'s Profile';
	else:
		print 'Unknown Profile';
	endif?>
</div>

<table class="profile">
	<tbody>
		<tr>
			<td>
				<a href="<?=$vars->profile_init->uri?>&amp;profile=index"><?=template::header_active('Index', 'profile', array('index', null), 'class="header-active"')?></a>
			</td>
			<td>
				<a href="<?=$vars->profile_init->uri?>&amp;profile=message"><?=template::header_active('Message', 'profile', 'message', 'class="header-active"')?><?=($vars->profile_data['messages_unread']['message'] > 0) ? '<span style="font-size: 0.8em;">['.$vars->profile_data['messages_unread']['message'].']</span>' : null?></a>
			</td>
			<td>
				<a href="<?=$vars->profile_init->uri?>&amp;profile=email"><?=template::header_active('Email', 'profile', 'email', 'class="header-active"')?><?=($vars->profile_data['messages_unread']['email'] > 0) ? '<span style="font-size: 0.8em;">['.$vars->profile_data['messages_unread']['email'].']</span>' : null?></a>
			</td>
			<?if($vars->profile_init->profile_self):?>
				<td>
					<a href="<?=$vars->profile_init->uri?>&amp;profile=profile&amp;account=profile"><?=template::header_active('Profile', 'profile', 'profile', 'class="header-active"')?></a>
				</td>
			<?endif?>
		</tr>
	</tbody>
</table>

<div class="p_profile">
	<div class="p_alias p_alias_1">
		<div style="padding: 10px 10px 0;">
			<a style="font-size: 1.2em;" href="<?=$vars->profile_init->uri3.'&amp;alias='.$vars->profile_init->alias_init2->alias?>"><?=$vars->profile_init->alias_init2->alias?></a>
		</div>
		<div style="padding: 1px; border-bottom: 1px solid #ECF1EF; margin-bottom: 10px;"></div>
		<?if(!$vars->profile_init->profile_self || $vars->profile_main == null):?>
			<div style="float: left; width: 180px;">
				<div class="p_picture">
					<?if(!empty($vars->profile_data['picture'])):?>
						<img class="p_picture" src="<?=$vars->profile_data['picture']?>" alt="<?=$vars->profile_init->alias_init2->alias?>'s Picture" />
					<?else:?>
						<img class="p_picture" src="./templates/css/img/avatar.jpg" alt="<?=(($vars->profile_init->profile_self) ? 'You have' : $vars->profile_init->alias_init2->alias.' has').' no picture.'?>" />
					<?endif?>
				</div>
			</div>
			<span style="font-size: 1.1em; font-weight: bold;"><?=$vars->profile_data['name']?> <?=($vars->profile_init->profile_self || $vars->moderator) ? ' (<a href="mailto:'.$vars->profile_data['email'].'">'.$vars->profile_data['email'].'</a>)' : null?></span><br />
			<div class="profile_main">
				<?=(!isset($vars->profile_data['p_private_profile'])) ? $vars->profile_data['p_profile_display'] : '<span style="color: #F26C4F;">This profile is private.</span>'?>
			</div>
			<br clear="both" />
			<div style="padding: 10px; border-bottom: 1px solid #ECF1EF; margin-bottom: 10px;"></div>
		<?endif?>
		<div class="profile_main profile_main2">
			<?=(!isset($vars->profile_data['p_private_profile'])) ? $vars->profile_main : null?>
		</div>
	</div>

	<div class="p_alias p_alias_2">
		<?if($vars->profile_data['p_status']['details'] || $vars->profile_init->profile_self || $vars->moderator):?>
			<div style="height: 15px; border-bottom: 1px solid #ECF1EF; padding: 2.5px 0; font-size: 1.1em; font-weight: bold;">
				<?=(!in_array($vars->profile_data['usergroup'], array('hidden', 'none'))) ? '<a style="color: '.$vars->profile_data['usergroup']['color'].';" href="'.$globals->_SERVER['PHP_SELF'].'?action=misc&amp;status=cpanel&amp;cmd=user&amp;group=user&amp;user='.$vars->profile_data['usergroup']['name'].'">'.$vars->profile_data['usergroup']['name'].'</a>' : ucfirst($vars->profile_data['usergroup'])?>
			</div>
			<div style="height: 15px; border-bottom: 1px solid #ECF1EF; padding: 2.5px 0;">
				Profile status:
				<?switch((int) $vars->profile_data['p_status']['profile']):
					case 0:
						print 'Public';
					break;
					case 1:
						print 'Hidden';
					break;
					case 2:
						print 'Private';
					break;
				endswitch?>
			</div>
			<div style="height: 15px; border-bottom: 1px solid #ECF1EF; padding: 2.5px 0;">
				Member since <?=strip_tags(reset(explode(',', $vars->profile_data['register_date'])))?>
			</div>
			<div style="height: 15px; border-bottom: 1px solid #ECF1EF; padding: 2.5px 0;">
				Online for <?=$vars->profile_data['online_time']['unix']?> <?=$vars->profile_data['online_time']['period']?>
			</div>
			<div style="height: 15px; border-bottom: 1px solid #ECF1EF; padding: 2.5px 0;">
				<a class="f_legacy" href="<?=$globals->_SERVER['PHP_SELF']?>?action=forum&amp;forum=0&amp;status=forum&amp;author=<?=$vars->profile_init->alias_init2->alias?>"><?=$vars->profile_data['forumposts']['post_count']?></a> forum posts (<?=$vars->profile_data['forumposts']['post_count_per_day']?> per day)
			</div>
			<div style="height: 15px; border-bottom: 1px solid #ECF1EF; padding: 2.5px 0;">
				<?=$vars->profile_data['forumposts']['rep']?> reputation
			</div>
			<div style="height: 15px; border-bottom: 1px solid #ECF1EF; padding: 2.5px 0;">
				Most Active Board: <?=(array_filter($vars->profile_data['forumposts']['active_forum']) != null) ? '<a class="f_legacy" href="'.$globals->_SERVER['PHP_SELF'].'?action=forum&amp;forum='.$vars->profile_data['forumposts']['active_forum']['ID'].'">'.$vars->profile_data['forumposts']['active_forum']['subject'].'</a>' : 'N/A'?>
			</div>
			<div style="height: 15px; border-bottom: 1px solid #ECF1EF; padding: 2.5px 0;">
				<?=($vars->profile_data['online']) ? ' <span style="color: green;">Currently online</span>' : ' <span class="control">Last online '.((!empty($vars->profile_data['last_online']['unix'])) ? $vars->profile_data['last_online']['unix'].' '.$vars->profile_data['last_online']['period'].' ago' : 'Never'.'</span>')?>
			</div>
		<?else:
			print $vars->profile_init->alias_init2->alias.'\'s details are hidden.';
		endif?>
	</div>
</div>
<?}?>
<?
//...
?>
<?function profile_email_array(stdclass $vars, stdclass $globals)
{?>
You are about to affect <?=count($vars->emails)?> email<?=(count($vars->emails) > 1) ? 's' : null?>.<br />
<form id="email_array" action="<?=$globals->_SERVER['REQUEST_URI']?>" method="post" enctype="application/x-www-form-urlencoded"><div>
	<input type="hidden" name="_submit" value="1" />
	<?foreach($vars->emails as $email):?>
		<input type="hidden" name="email[]" value="<?=$email?>" />
	<?endforeach?>
	<a class="control" href="#" onclick="document.forms['email_array'].submit();">Continue</a>, <a class="control" href="<?=$globals->_SERVER['HTTP_REFERER']?>">Back</a>.
</div></form>
<?}?>
<?
//...
?>
<?function profile_index(stdclass $vars, stdclass $globals)
{?>
<?=null/*$vars->profile_data['p_profile']*/?>
<?}?>
<?
//...
?>
<?function profile_profile(stdclass $vars, stdclass $globals)
{?>
<ul class="p_profile_header">
	<li><a href="<?=$vars->profile_init->uri2?>&amp;profile=index">View Profile &raquo;</a></li>
	<li><a href="<?=$vars->profile_init->uri?>&amp;profile=profile&amp;account=profile"><?=template::header_active('Profile', 'account', array('profile', null))?> &raquo;</a></li>
	<li><a href="<?=$vars->profile_init->uri?>&amp;profile=profile&amp;account=account"><?=template::header_active('Account', 'account', 'account')?> &raquo;</a></li>
	<li><a href="<?=$vars->profile_init->uri?>&amp;profile=profile&amp;account=forum_subscription"><?=template::header_active('Forum Subscriptions', 'account', 'forum_subscription')?> &raquo;</a></li>
	<li><a href="<?=$vars->profile_init->uri?>&amp;profile=profile&amp;account=forum_reputation"><?=template::header_active('Forum Reputation', 'account', 'forum_reputation')?> &raquo;</a></li>
	<?=($vars->moderator) ? '<li><a href="'.$vars->profile_init->uri.'&amp;profile=profile&amp;account=cpanel">'.template::header_active('CPanel', 'account', 'cpanel').' &raquo;</a></li>' : null?>
</ul>
<?switch($vars->profile_type):
	case 'profile':?>
		<form id="profile" action="<?=$vars->profile_init->uri?>&amp;profile=update&amp;update=profile" method="post" enctype="application/x-www-form-urlencoded"><div>
			<div class="p_profile_center">
				<textarea name="p_profile" class="post"><?=$vars->profile_data['p_profile']?></textarea><br /><br />
				<input type="button" class="post" value="AJAX Update" onclick="AJAX('profile', document.getElementsByTagName('body')[0], '<?=$globals->_SERVER['PHP_SELF']?>?action=profile&status=profile_self&profile=update&update=profile&ajax=1', false); document.getElementById('updated').innerHTML = 'Updated'; /*IE hack*/ if(navigator.userAgent.match(/MSIE/)){window.location.reload();}" /><input type="submit" class="post" value="Update" />
				<br />
				<?=(isset($globals->_GET['update'])) ? '<span style="color: green;">Updated</span>' : '<br />'?>
				<span id="updated" style="color: green;"></span>
			</div>
			<div class="p_profile_left">
				Picture<br /><input type="text" name="picture" value="<?=$vars->profile_data['picture']?>" /><br /><br />
				Signature<br /><input type="text" name="signature" value="<?=$vars->profile_data['signature']?>" />
			</div>
			<div class="p_profile_right">
				Display Details<br /><select name="details"><option value="1" <?=($vars->profile_data['p_status']['details']) ? 'selected="true"' : null?>>Yes</option><option value="0" <?=(!$vars->profile_data['p_status']['details']) ? 'selected="true"' : null?>>No</option></select><br /><br />
				Allow Messages<br /><select name="message"><option value="1" <?=($vars->profile_data['p_status']['message']) ? 'selected="true"' : null?>>Yes</option><option value="0" <?=(!$vars->profile_data['p_status']['message']) ? 'selected="true"' : null?>>No</option></select><br /><br />
				Allow Emails<br /><select name="email"><option value="1" <?=($vars->profile_data['p_status']['email']) ? 'selected="true"' : null?>>Yes</option><option value="0" <?=(!$vars->profile_data['p_status']['email']) ? 'selected="true"' : null?>>No</option></select><br /><br />
				Hide Profile<br /><select name="profile"><option value="0" <?=($vars->profile_data['p_status']['profile'] == 0) ? 'selected="true"' : null?>>Public</option><option value="1" <?=($vars->profile_data['p_status']['profile'] == 1) ? 'selected="true"' : null?>>Hidden</option><option value="2" <?=($vars->profile_data['p_status']['profile'] == 2) ? 'selected="true"' : null?>>Private</option></select><br /><br />
				Developer Profile<br /><input type="checkbox" name="developer" value="1" <?=($vars->profile_data['p_status']['developer']) ? 'checked="true"' : null?> />
			</div>
		</div></form>
	<?break;
	case 'account':?>
		<form id="profile" action="<?=$vars->profile_init->uri?>&amp;profile=update&amp;update=account" method="post" enctype="application/x-www-form-urlencoded"><div>
			Name:<br /><input type="text" name="name" value="<?=$vars->profile_data['name']?>" maxlength="<?=$vars->account_data['clm_Name']?>" /><br /><br />
			Email Address:<br /><input type="text" name="email" value="<?=$vars->profile_data['email']?>" maxlength="<?=$vars->account_data['clm_Email']?>" /><br /><br />
			Alias:<br /><input type="text" name="alias" value="<?=$vars->profile_init->alias_init2->alias?>" <?=(!$vars->moderator) ? 'readonly="true"' : null?> maxlength="<?=$vars->account_data['clm_Alias']?>" /><br /><br />
			Password:<br /><input type="password" name="password" value="<?=$vars->profile_data['password']?>" maxlength="<?=$vars->account_data['clm_Password']?>" /><br /><br />
			<input type="submit" class="post" value="Update" />
		</div></form>
		
		<?=(isset($globals->_GET['update'])) ? '<span style="color: green;">Updated</span>' : null?>
	<?break;
	case 'forum_subscription':?>
		<div class="box" style="width: 300px;">
			<?foreach($vars->subscriptions as $subscription):?>
				<a href="<?=$globals->_SERVER['PHP_SELF']?>?action=forum&amp;forum=<?=$subscription['forum']?>&status=thread&thread=<?=$subscription['thread']?>"><?=$subscription['subject']?></a>
				<a href="<?=$globals->_SERVER['PHP_SELF']?>?action=forum&amp;forum=<?=$subscription['forum']?>&status=subscribe&thread=<?=$subscription['thread']?>"><img src="./templates/css/img/unsubscribe.gif" /></a><br />
			<?endforeach?>
		</div>
	<?break;
	case 'forum_reputation':?>
		<div class="box" style="width: 300px;">
			<?foreach($vars->reputation as $reputation):
				print '<a href="'.$globals->_SERVER['PHP_SELF'].'?action=forum&amp;forum='.$reputation['forum'].'&status=thread&thread='.$reputation['thread'].'#Post:'.$reputation['id'].'">'.$reputation['subject'].'</a> for +'.$reputation['reputation'].' by '.$reputation['author'].'<br />';
			endforeach?>
		</div>
	<?break;
	case 'cpanel':?>
		<div class="action">CPanel : <?=$vars->profile_init->alias_init->alias?></div><br /><br />
		<ul class="box" style="width: 250px;">
			<li><a href="<?=$globals->_SERVER['PHP_SELF']?>?action=misc&amp;status=cpanel&amp;cmd=users">Users</a></li>
			<li><a href="<?=$globals->_SERVER['PHP_SELF']?>?action=misc&amp;status=cpanel&amp;cmd=edit_user">Edit User</a></li>
			<li><a href="<?=$globals->_SERVER['PHP_SELF']?>?action=misc&amp;status=cpanel&amp;cmd=edit_usergroup">Edit Usergroup</a></li>
			<li><a href="<?=$globals->_SERVER['PHP_SELF']?>?action=misc&amp;status=cpanel&amp;cmd=mod_list">Mod List</a></li>
			<li><a href="<?=$globals->_SERVER['PHP_SELF']?>?action=misc&amp;status=cpanel&amp;cmd=ban_list">Ban List</a></li>
			<li><a href="<?=$globals->_SERVER['PHP_SELF']?>?action=misc&amp;status=cpanel&amp;cmd=logs">Logs</a></li>
			<li><a href="<?=$globals->_SERVER['PHP_SELF']?>?action=misc&amp;status=cpanel&amp;cmd=forum_config">Forum Config</a></li>
			<?if($vars->administrator):?>
				<li><a href="<?=$globals->_SERVER['PHP_SELF']?>?action=misc&amp;status=cpanel&amp;cmd=config">Config</a></li>
			<?endif?>
		</ul>
	<?break;
endswitch?>
<?}?>
<?
//...
?>
<?function profile_message(stdclass $vars, stdclass $globals)
{?>
<div class="f_header">
	<?=$vars->pagination?>
</div>

<?if(!$vars->profile_init->profile_self):?>
	<div class="f_header2">
		<a style="font-style: italic;" href="<?=$vars->profile_init->uri?>&amp;profile=message&amp;message_send=1">Message <?=$vars->profile_init->alias_init2->alias?> &raquo;</a>
	</div>
<?endif?>
<table class="message">
	<thead>
		<tr>
			<td class="author">
				Author
			</td>
			<td class="message_main">
				Message
			</td>
			<td class="author">
				Date
			</td>
		</tr>
	</thead>
	<tbody>
		<?if(sizeof($vars->messages)):
			foreach($vars->messages as $message):?>
				<tr>
					<td class="author">
						<?=$message['author']['link']?>
					</td>
					<td id="Message:<?=$message['id']?>" class="message_main">
						<div class="message">
							<?=$message['message']?>
						</div>
						<div class="t_permission2">
							<div class="t_permission3">
								<?if($message['permission']['alias']):?>
									<a class="permission" href="<?=$vars->profile_init->uri3?>&amp;profile=message&amp;message_send=1&amp;alias=<?=$message['author']['author']?>">Reply</a>
								<?endif?>
								<?if($message['permission']['author']):?>
									<a class="permission-edit" title="Edit" href="<?=$vars->profile_init->uri?>&amp;profile=message&amp;message_edit=<?=$message['id']?>"><!--Edit--></a>
									<a class="permission-delete" title="Delete" href="<?=$vars->profile_init->uri?>&amp;profile=message&amp;message_delete=<?=$message['id']?>"><!--Delete--></a>
								<?elseif($message['permission']['alias2']):?>
									<a class="permission-delete" title="Delete" href="<?=$vars->profile_init->uri?>&amp;profile=message&amp;message_delete=<?=$message['id']?>"><!--Delete--></a>
								<?endif?>
							</div>
						</div>
					</td>
					<td class="author">
						<span style="font-size: 0.8em;"><?=$message['date']?></span>
					</td>
				</tr>
			<?endforeach;
		else:
			print '<tr>
				<td class="message_main" colspan="100%">No messages found.</td>
			</tr>';
		endif?>
	</tbody>
</table>
<?}?>
<?
//...
?>
<?function profile_message_send(stdclass $vars, stdclass $globals)
{?>
<div class="action">Message <?=$vars->profile_init->alias_init2->alias?></div>

<form id="message" action="<?=$globals->_SERVER['REQUEST_URI']?>" method="post" enctype="application/x-www-form-urlencoded"><div>
	<textarea name="message" class="post"></textarea><br /><br />
	<input type="button" class="post" value="Preview" onclick="AJAX('message');" /><input type="submit" class="post" value="Send" />
	<?=template::unique_hash()?>
	<?=template::parse_options()?>
	<div id="ajax"></div>
</div></form>
<?}?>
<?
//...
?>
<?function profile_message_delete(stdclass $vars, stdclass $globals)
{?>
You are about to delete this message.<br />
<a class="control" href="#" onclick="form_submit('<?=$globals->_SERVER['REQUEST_URI']?>');">Continue</a>, <a class="control" href="<?=$globals->_SERVER['HTTP_REFERER']?>">Back</a>.
<?}?>
<?
//...
?>
<?function profile_message_edit(stdclass $vars, stdclass $globals)
{?>
<div class="action">Edit Message</div>
<form id="message_edit" action="<?=$globals->_SERVER['REQUEST_URI']?>" method="post" enctype="application/x-www-form-urlencoded"><div>
	<textarea name="message" class="post"><?=$vars->message?></textarea><br /><br />
	<input type="button" class="post" value="Preview" onclick="AJAX('message_edit');" /><input type="submit" class="post" value="Save" />
	<?=template::unique_hash()?>
	<?=template::parse_options($vars->parse_options, null)?>
	<div id="ajax"></div>
</div></form>
<?}?>
<?
//...
?>
<?function profile_email(stdclass $vars, stdclass $globals)
{?>
<div class="f_header2">
	<div style="float: right;">
		<?=$vars->pagination?>
	</div>
	<br clear="both" />
	<a href="<?=$vars->profile_init->uri?>&amp;profile=email&amp;box=email"><?=template::header_active('Inbox', 'box', array('email', null))?> &raquo;</a> |
	<a href="<?=$vars->profile_init->uri?>&amp;profile=email&amp;box=email2"><?=template::header_active('Outbox', 'box', 'email2')?> &raquo;</a> |
	<a href="<?=$vars->profile_init->uri?>&amp;profile=email<?=(isset($vars->box['type'])) ? '&amp;box='.$vars->box['type'] : null?>&amp;box_status=all"><?=template::header_active('All', 'box_status', array('all', null))?> &raquo;</a> |
	<a href="<?=$vars->profile_init->uri?>&amp;profile=email<?=(isset($vars->box['type'])) ? '&amp;box='.$vars->box['type'] : null?>&amp;box_status=0"><?=template::header_active('Unread', 'box_status', '0')?> &raquo;</a> |
	<a href="<?=$vars->profile_init->uri?>&amp;profile=email<?=(isset($vars->box['type'])) ? '&amp;box='.$vars->box['type'] : null?>&amp;box_status=1"><?=template::header_active('Read', 'box_status', 1)?> &raquo;</a> |
	<a href="<?=$vars->profile_init->uri?>&amp;profile=email<?=(isset($vars->box['type'])) ? '&amp;box='.$vars->box['type'] : null?>&amp;box_status=2"><?=template::header_active('Replied', 'box_status', 2)?> &raquo;</a>
</div>

<form id="email" action="<?=$vars->profile_init->uri?>&amp;profile=email" method="post" enctype="application/x-www-form-urlencoded"><div>
	<div class="f_header">
		<script type="text/javascript">
			/*<![CDATA[*/
				var email = {
					timeout: '',
					email_header: function(e, e_hide)
					{
						document.getElementById(e).style.display = 'block';
						
						for(var i = 0; i < e_hide.length; i++)
						{
							document.getElementById(e_hide[i]).style.display = 'none';
						}
						this.email_header_timeout();
					},
					email_header_clear: function(e, hide)
					{
						if(hide)
						{
							document.getElementById(e).style.display = 'none';
						}
						else
						{
							this.timeout = setTimeout(function(_this){_this.email_header_clear(e, true);}, 1000, this);
						}
					},
					email_header_timeout: function()
					{
						clearTimeout(this.timeout);
					}
				}
			/*]]>*/
		</script>
		
		<a href="#email_select" onmouseover="email.email_header('email_select', new Array('email_action', 'search'));" onmouseout="email.email_header_clear('email_select');">Select</a> |
		<a href="#email_action" onmouseover="email.email_header('email_action', new Array('email_select', 'search'));" onmouseout="email.email_header_clear('email_action');">With Selected</a> |
		<a href="#search" onmouseover="email.email_header('search', new Array('email_action', 'email_action'));" onmouseout="email.email_header_clear('search');">Search <img src="./templates/css/img/search.gif" alt="Search" /></a>
		<div id="email_select" class="box" style="display: none;" onmouseover="email.email_header_timeout();" onmouseout="email.email_header_clear('email_select');">
			<a class="control" href="#email_select" onclick="checkAll(true);">All</a><br />
			<a class="control" href="#email_select" onclick="checkAll(false);">None</a>
		</div>
		<div id="email_action" class="box" style="display: none;" onmouseover="email.email_header_timeout();" onmouseout="email.email_header_clear('email_action');">
			<a class="control" href="#email_action" onclick="document.forms['email'].action += '&amp;email_delete=array'; document.forms['email'].submit();">Delete</a><br />
			<a class="control" href="#email_action" onclick="document.forms['email'].action += '&amp;email_read=array'; document.forms['email'].submit();">Mark As Read</a><br />
			<a class="control" href="#email_action" onclick="document.forms['email'].action += '&amp;email_unread=array'; document.forms['email'].submit();">Mark As Unread</a>
		</div>
		<div id="search" class="box" style="display: none;">
			<input type="text" name="search" onfocus="email.email_header_timeout();" onblur="email.email_header_clear('search');" onkeydown="return enter(event);" />
			<a class="control" href="#search" onclick="return window.location = window.location.href+'&amp;search='+this.parentNode.children[0].value; document.forms['email'].submit();">Search</a>
		</div>
	</div>
	
	<table class="message">
		<thead>
			<tr>
				<td class="author">
					Author
				</td>
				<td class="email_main">
					Subject
				</td>
				<td class="author">
					Date
				</td>
			</tr>
		</thead>
		<tbody>
			<?if(sizeof($vars->emails)):
				foreach($vars->emails as $email):?>
					<tr>
						<td class="author">
							<?=$email['author']?>
						</td>
						<td id="Email:<?=$email['id']?>" class="email_main">
							<div class="email_subject">
								<input type="checkbox" name="email[]" value="<?=$email['id']?>" />
								<a class="email_subject <?=$email['status']?>" href="<?=$vars->profile_init->uri?>&amp;profile=email&amp;email=<?=$email['id']?>"><?=$email['subject']?></a>
							</div>
						</td>
						<td class="author">
							<span style="font-size: 0.8em;"><?=$email['date']?></span>
						</td>
					</tr>
				<?endforeach;
			else:
				print '<tr>
					<td class="email_main" colspan="100%">No emails found.</td>
				</tr>';
			endif?>
		</tbody>
	</table>
</div></form>
<?}?>
<?
//...
?>
<?function profile_email_email(stdclass $vars, stdclass $globals)
{?>
<table class="email_header">
	<tbody>
		<tr>
			<td>
				<?=(!empty($vars->email_links['previous'])) ? '<a class="control" href="'.$vars->profile_init->uri.'&amp;profile=email&amp;email='.$vars->email_links['previous'].'">&laquo; Previous</a>' : '<span class="control" style="color: gray;">&laquo; Previous</span>'?> |
				<?=(!empty($vars->email_links['next'])) ? '<a class="control" href="'.$vars->profile_init->uri.'&amp;profile=email&amp;email='.$vars->email_links['next'].'">Next &raquo;</a>' : '<span class="control" style="color: gray;">Next &raquo;</span>'?>
			</td>
			<td>
				<a class="control" href="<?=$vars->profile_init->uri2?>&amp;profile=email&amp;alias=<?=$vars->author['author']?>&amp;reply=<?=$vars->id?>">Reply</a>
			</td>
			<td>
				<a class="control" href="<?=$vars->profile_init->uri?>&amp;profile=email&amp;email_delete=<?=$vars->id?>">Delete</a>
			</td>
			<td>
				<a class="control" href="<?=$vars->profile_init->uri?>&amp;profile=email&amp;email_unread=<?=$vars->id?>">Mark As Unread</a>
			</td>
			<?if($vars->moderator):
				print '<td>
					<a class="control" href="'.$vars->profile_init->uri.'&amp;profile=email&amp;email_edit='.$vars->id.'">Edit</a>
				</td>';
			endif?>
		</tr>
	</tbody>
</table>

<table class="email">
	<tbody>
		<tr>
			<td>
				Author:
			</td>
			<td class="email">
				<?=$vars->author['link']?>
				<span style="font-size: 0.8em;">(<?=$vars->date?>)</span>
			</td>
		</tr>
		<tr>
			<td>
				Recipient:
			</td>
			<td class="email">
				<?=$vars->alias?>
			</td>
		</tr>
		<tr>
			<td>
				Subject:
			</td>
			<td class="email">
				<?=$vars->subject?>
			</td>
		</tr>
		<tr>
			<td>
				Email:
			</td>
			<td class="email">
				<?=$vars->email?>
			</td>
		</tr>
	</tbody>
</table>
<?}?>
<?
//...
?>
<?function profile_email_delete(stdclass $vars, stdclass $globals)
{?>
You are about to delete this email.<br />
<a class="control" href="#" onclick="form_submit('<?=$globals->_SERVER['REQUEST_URI']?>');">Continue</a>, <a class="control" href="<?=$globals->_SERVER['HTTP_REFERER']?>">Back</a>.
<?}?>
<?
//...
?>
<?function profile_email_edit(stdclass $vars, stdclass $globals)
{?>
<div class="action">Edit Email</div>
<form id="email_edit" action="<?=$globals->_SERVER['REQUEST_URI']?>" method="post" enctype="application/x-www-form-urlencoded"><div>
	Subject:<br /><input type="text" name="subject" class="post" value="<?=$vars->subject?>" /><br /><br />
	Email:<br /><textarea name="email" class="post"><?=$vars->email?></textarea><br /><br />
	<input type="button" class="post" value="Preview" onclick="AJAX('email_edit');" /><input type="submit" class="post" value="Save" />
	<?=template::unique_hash()?>
	<?=template::parse_options($vars->parse_options, null)?>
	<div id="ajax"></div>
</div></form>
<?}?>
<?
//...
?>
<?function profile_email_send(stdclass $vars, stdclass $globals)
{?>
<form id="email" action="<?=$globals->_SERVER['REQUEST_URI']?>" method="post" enctype="application/x-www-form-urlencoded"><div>
	Subject:<br /><input type="text" name="subject" class="post" value="<?=((!isset($globals->_GET['subject']) && !empty($vars->subject)) ? 'RE: ' : null).$vars->subject?>" /><br /><br />
	Email:<br /><textarea name="email" class="post"></textarea><br /><br />
	<input type="button" class="post" value="Preview" onclick="AJAX('email');" /><input type="submit" class="post" value="Send" />
	<?=template::unique_hash()?>
	<?=template::parse_options(null, array('Email Options' => array('CC'        => '<input type="hidden" name="recipient" /><input type="text" id="new_cc" onkeydown="return enter(event);" /><input type="button" class="post" value="Add" onclick="email_options.cc();" /> <a style="font-size: 0.8em;" href="#new_cc" onclick="window.open(\'./main/misc.php?status=search_users&amp;input=new_cc\', \'search_users\', \'width=400px, height=100px, toolbar=false\');">(Search)</a>',
															  'Save Copy' => '<input type="checkbox" name="box" value="1" checked="true" /></dd>')))?>
	<div id="ajax"></div>
</div></form>
<?}?>