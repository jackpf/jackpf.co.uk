<?function forum_main(stdclass $vars, stdclass $globals)
{?>
<div class="title">Forum</div>

<div class="f_header">
	Legacy:
	<?foreach($vars->forum_legacy as $subject => $link):
		$vars->forum_legacy[$subject] = '<a class="f_legacy" href="'.$link.'">'.ucfirst($subject).'</a>';
	endforeach;
	print implode(' <img src="./templates/css/img/f_legacy.gif" alt="&gt;" /> ', $vars->forum_legacy)?>
</div>

<div class="f_header page">
	<?=(isset($vars->pagination)) ? $vars->pagination : null?>
</div>

<div class="f_header2">
	<?if(isset($vars->post_link) && $vars->post_link):
		print '<a class="post" href="'.$globals->_SERVER['PHP_SELF'].'?action=forum'.((isset($vars->forumid)) ? '&amp;forum='.$vars->forumid : null).((isset($vars->threadid)) ? '&amp;thread='.$vars->threadid : null).'&amp;status=post">'.((!isset($vars->post_link_type)) ? 'Post' : ucfirst($vars->post_link_type)).'</a>';
	endif?>
	<?if(isset($vars->search_link) && $vars->search_link):
		print '<a class="search" href="'.$globals->_SERVER['PHP_SELF'].'?action=forum&amp;forum='.((isset($vars->forumid)) ? $vars->forumid : 0).'&amp;status=search'.((isset($vars->threadid)) ? '&amp;thread='.$vars->threadid : null).'">Search<img src="./templates/css/img/search.gif" alt="Search" /></a>';
	endif?>
	<?if(isset($vars->subscribe_link) && $vars->subscribe_link):
		print '<a class="subscribe" href="'.val::encode($_SERVER['PHP_SELF']).'?action=forum&amp;forum='.$vars->forumid.'&amp;status=subscribe'.((isset($vars->threadid)) ? '&amp;thread='.$vars->threadid : null).'">'.ucfirst($vars->subscribe_link_type).'<img src="./templates/css/img/'.$vars->subscribe_link_type.'.gif" alt="'.ucfirst($vars->subscribe_link_type).'" /></a>';
	endif?>
</div>

<?if(!empty($vars->forum_announcement)):?>
	<div class="announcement">
		<span class="announcement">Announcement</span><br />
		<p class="t_info">
			<?=$vars->forum_announcement?>
		</p>
	</div>
<?endif?>

<?=$vars->forum_main?>

<?if(isset($vars->forum_search_link) && $vars->forum_search_link):?>
	<div class="f_footer f_footer_search">
		<span class="f_footer_title">Threads</span>
		
		<img src="./templates/css/img/search_link.gif" /> <a class="f_legacy" href="<?=$globals->_SERVER['PHP_SELF']?>?action=forum&amp;forum=<?=($vars->forumid) ? $vars->forumid : 0?>">View New Threads</a><br />
		<img src="./templates/css/img/search_link.gif" /> <a class="f_legacy" href="<?=$globals->_SERVER['PHP_SELF']?>?action=forum&amp;forum=<?=($vars->forumid) ? $vars->forumid : 0?>&amp;threads=created">View Your Threads</a><br />
		<img src="./templates/css/img/search_link.gif" /> <a class="f_legacy" href="<?=$globals->_SERVER['PHP_SELF']?>?action=forum&amp;forum=<?=($vars->forumid) ? $vars->forumid : 0?>&amp;threads=posted">View Threads You've Posted In</a><br />
		<img src="./templates/css/img/search_link.gif" /> <a class="f_legacy" href="<?=$globals->_SERVER['PHP_SELF']?>?action=forum&amp;forum=<?=($vars->forumid) ? $vars->forumid : 0?>&amp;threads=replied">View Replied Threads</a><br />
		<img src="./templates/css/img/search_link.gif" /> <a class="f_legacy" href="<?=$globals->_SERVER['PHP_SELF']?>?action=forum&amp;forum=<?=($vars->forumid) ? $vars->forumid : 0?>&amp;threads=unread">View Unread Threads</a><br />
		<img src="./templates/css/img/search_link.gif" /> <a class="f_legacy" href="<?=$globals->_SERVER['PHP_SELF']?>?action=forum&amp;forum=<?=($vars->forumid) ? $vars->forumid : 0?>&amp;threads=read">View Read Threads</a><br />
	</div>
<?endif?>

<?if(isset($vars->forum_footer)):?>
	<div class="f_footer">
		<span class="f_footer_title">Forum Statistics</span>
		
		<p style="background: url(./templates/css/img/forum_viewing.gif) no-repeat; padding-left: 30px !important; min-height: 27px;">Viewing <?=((!isset($vars->forumid)) ? 'the forum' : ((!isset($vars->threadid)) ? 'this board' : 'this thread'))?>:
			<?if(count($vars->forum_footer->viewing['aliases']) + $vars->forum_footer->viewing['aliases_hidden'] + $vars->forum_footer->viewing['strangers'] > 0):
				print implode(', ', $vars->forum_footer->viewing['aliases']);
				if($vars->forum_footer->viewing['aliases_hidden'] > 0):
					print ((count($vars->forum_footer->viewing['aliases']) > 0) ? ', + ' : null).$vars->forum_footer->viewing['aliases_hidden'].' hidden';
				endif;
				if($vars->forum_footer->viewing['strangers'] > 0):
					print ((count($vars->forum_footer->viewing['aliases']) > 0 || $vars->forum_footer->viewing['aliases_hidden'] > 0) ? ', + ' : null).$vars->forum_footer->viewing['strangers'].' stranger'.(($vars->forum_footer->viewing['strangers'] > 1) ? 's' : null);
				endif;
			else:
				print 'No one';
			endif?>
			
			<?if(isset($vars->forum_footer->active)):
				print '<br />Active poster'.((count($vars->forum_footer->active) <> 1) ? 's' : null).' in this thread: '.implode(', ', $vars->forum_footer->active);
			endif?>
		</p>
		
		<p style="background: url(./templates/css/img/forum_statistics.gif) no-repeat; padding-left: 30px !important; min-height: 27px;">
			<?=$vars->forum_footer->posts['post']?> post<?=($vars->forum_footer->posts['post'] <> 1) ? 's' : null?> in <?=(!isset($vars->threadid)) ? $vars->forum_footer->posts['thread'].' thread'.(($vars->forum_footer->posts['thread'] <> 1) ? 's' : null) : 'this thread'?> in <?=(!isset($vars->forumid)) ? $vars->forum_footer->posts['forum'].' board'.(($vars->forum_footer->posts['forum'] <> 1) ? 's' : null) : 'this board'?>
		</p>
	</div>
<?endif?>
<?}?>
<?
//...
?>
<?function forum_index(stdclass $vars, stdclass $globals)
{?>
<table class="forum">
	<thead>
		<tr>
			<td class="thread_main">
				<a href="<?=reset(explode('&amp;order', $globals->_SERVER['REQUEST_URI']))?>&amp;order=ID">Subject</a>
			</td>
			<td>
				<a href="<?=reset(explode('&amp;order', $globals->_SERVER['REQUEST_URI']))?>&amp;order=Stats">Views</a>
			</td>
			<td>
				<a href="<?=reset(explode('&amp;order', $globals->_SERVER['REQUEST_URI']))?>&amp;order=Posts">Posts</a>
			</td>
			<td class="last_post">
				<a href="<?=reset(explode('&amp;order', $globals->_SERVER['REQUEST_URI']))?>&amp;order=LastPost">Last Post</a>
			</td>
		</tr>
	</thead>
	<tbody>
		<?if(sizeof($vars->forum)):
			foreach($vars->forum as $thread):?>
				<tr>
					<td id="<?=ucfirst($thread['type'].':'.$thread['id'])?>" class="thread_main">
						<div class="thread <?=$thread['type'].'_'.(($thread['read']) ? 'read' : 'unread').(($thread['sticky']) ? '_sticky' : null).(($thread['closed']) ? '_closed' : null)?>">
							<a class="thread" href="<?=$globals->_SERVER['PHP_SELF']?>?action=forum&amp;<?=($thread['type'] == 'forum') ? 'forum='.$thread['id'] : 'forum='.$thread['forum'].'&amp;status=thread&amp;thread='.$thread['id']?>">
								<?=$thread['subject']?>
							</a>
							<?if($thread['type'] == 'thread' && $thread['pages'] > 1):
									print '<div class="thread_pagination">';
										print config_pagination((object) array('current_page' => 1, 'uri' => $globals->_SERVER['PHP_SELF'].'?action=forum&amp;forum='.$thread['forum'].'&amp;status=thread&amp;thread='.$thread['id'].'&amp;page=', 'last_page' => $thread['pages']), new stdclass);
									print '</div>';
								endif?>
							<?if(!empty($thread['forum_subject']) || !empty($thread['forum_children']) || !empty($thread['forum_mod'])):
								print '<p class="t_info">';
									if(!empty($thread['forum_subject'])):
										print $thread['forum_subject'];
									endif;
									if(!empty($thread['forum_children'])):
										print '<br />
										<span class="f_legacy" style="font-size: 0.9em;">Child Boards: ';
										foreach($thread['forum_children'] as $id => &$subject)
											$subject = '<a class="f_legacy" href="'.$globals->_SERVER['PHP_SELF'].'?action=forum&amp;forum='.$id.'">'.$subject.'</a>';
										print implode(', ', $thread['forum_children']).'</span>';
									endif;
									if(!empty($thread['forum_mod'])):
										print '<br />
										<span class="f_legacy" style="font-size: 0.9em;">Moderators: ';
										if(is_array(reset($thread['forum_mod'])))
											foreach($thread['forum_mod'] as &$user)
												$user = '<a style="color: '.$user['color'].';" href="'.$globals->_SERVER['PHP_SELF'].'?action=misc&amp;status=user&amp;group=user&amp;user='.$user['value'].'">'.$user['value'].'s</a>';
										print implode(', ', $thread['forum_mod']).'</span>';
									endif;
								print '</p>';
							endif?>
							<?if(!empty($thread['author'])):
								print '<p class="t_info">By '.$thread['author'].' on '.$thread['date'].'</p>';
							endif?>
						</div>
					</td>
					<td>
						<?=$thread['stats']?>
					</td>
					<td>
						<?=$thread['post_count']?>
					</td>
					<td class="last_post">
						<span class="t_info">
							<?if(!empty($thread['last_post']['id'])):
								print '<a class="last_post" href="'.$globals->_SERVER['PHP_SELF'].'?action=forum&amp;forum='.$thread['last_post']['forum'].((in_array($thread['last_post']['type'], array('thread', 'post'))) ? '&amp;status=thread&amp;thread='.$thread['last_post']['thread'].(($thread['last_post']['page'] > 1) ? '&amp;page='.$thread['last_post']['page'] : null).'#Post:'.$thread['last_post']['id'] : null).'">'.$thread['last_post']['subject'].'</a>
								By '.$thread['last_post']['author'].' on '.$thread['last_post']['date'];
							else:
								print 'No posts';
							endif?>
						</span>
					</td>
				</tr>
			<?endforeach;
		else:
			print '<tr>
				<td class="thread_main" colspan="100%">No '.((!isset($vars->forumid)) ? 'boards' : 'threads').' found.</td>
			</tr>';
		endif?>
	</tbody>
</table>
<?}?>
<?
//...
?>
<?function forum_thread(stdclass $vars, stdclass $globals)
{?>
<table class="thread">
	<thead>
		<tr>
			<td class="author">
				Author
			</td>
			<td class="thread_main">
				Post
			</td>
		</tr>
	</thead>
	<tbody>
	<?if(sizeof($vars->thread)):
		foreach($vars->thread as $post):?>
			<tr>
				<td class="author">
					<?=$post['author']?>
				</td>
				<td id="Post:<?=$post['id']?>" class="thread_main">
				<div class="t_subject">
					<div class="t_info">
						<a href="#Post:<?=$post['id']?>"><?=$post['subject']?></a>
					</div>
					<p class="t_subject t_info">
						<?=$post['date']?>
					</p>
				</div>
				<div class="t_permission">
					<a class="permission" href="<?=$globals->_SERVER['PHP_SELF']?>?action=forum&amp;forum=<?=$vars->forumid?>&amp;thread=<?=$vars->threadid?>&amp;status=post&amp;reply=<?=$post['id']?>">Reply</a>
					<a class="permission" href="#Post:<?=$post['id']?>" onclick="quote.add(this, <?=$post['id']?>);" >Quote</a>
					<a class="permission" href="<?=$globals->_SERVER['PHP_SELF']?>?action=forum&amp;forum=<?=$vars->forumid?>&amp;thread=<?=$vars->threadid?>&amp;status=post_report&amp;post=<?=$post['id']?>">Report</a>
				</div>
				<div class="t_post">
					<?=$post['post']?>
					<?if($post['edit']['count'] > 0):?>
						<div class="t_edit">
							Edited <?=$post['edit']['count']?> time<?=($post['edit']['count'] <> 1) ? 's' : null?>; Last edited on <?=$post['edit']['date']?> by <?=$post['edit']['author']?>.
						</div>
					<?endif?>
				</div>
				<div class="t_permission2">
					<div class="t_permission3">
						<?if(!empty($post['permissions']) && $post['permissions']['author']):
							if(!$post['permissions']['closed']):?>
								<a class="permission-edit" title="Edit" href="<?=$globals->_SERVER['PHP_SELF']?>?action=forum&amp;forum=<?=$vars->forumid?>&amp;status=<?=$post['type']?>_edit&amp;thread=<?=($post['type'] == 'thread') ? $post['id'] : $vars->threadid.'&amp;post='.$post['id']?>"><!--Edit--></a>
								<a class="permission-delete" title="<?=($post['type'] == 'thread') ? 'Close' : 'Delete'?>" href="<?=$globals->_SERVER['PHP_SELF']?>?action=forum&amp;forum=<?=$vars->forumid?>&amp;status=<?=($post['type'] == 'thread') ? 'thread_close' : 'post_delete'?>&amp;thread=<?=$vars->threadid?><?=($post['type'] == 'post') ? '&amp;post='.$post['id'] : null?>"><!--<?=($post['type'] == 'thread') ? 'Close' : 'Delete'?>--></a>
							<?elseif($post['permissions']['moderator']):?>
								<a class="permission-edit" title="Edit" href="<?=$globals->_SERVER['PHP_SELF']?>?action=forum&amp;forum=<?=$vars->forumid?>&amp;status=<?=$post['type']?>_edit&amp;thread=<?=$vars->threadid?><?=($post['type'] == 'post') ? '&amp;post='.$post['id'] : null?>"><!--Edit--></a>
								<?if($post['type'] == 'thread'):?>
									<a class="permission-open" title="Open" href="<?=$globals->_SERVER['PHP_SELF']?>?action=forum&amp;forum=<?=$vars->forumid?>&amp;status=thread_open&amp;thread=<?=$post['id']?>"><!--Open--></a>
								<?elseif($post['permissions']['moderator']):?>
									<a class="permission-delete" title="Delete" href="<?=$globals->_SERVER['PHP_SELF']?>?action=forum&amp;forum=<?=$vars->forumid?>&amp;status=post_delete&amp;thread=<?=$vars->threadid?>&amp;post=<?=$post['id']?>"><!--Delete--></a>
								<?endif;
							endif;
						endif;
						if(!empty($post['permissions']) && $post['permissions']['moderator']):
							print '<a class="permission-permissions" title="Permissions" href="'.$globals->_SERVER['PHP_SELF'].'?action=forum&amp;forum='.$vars->forumid.'&amp;thread='.(($post['type'] == 'thread') ? $post['id'] : $vars->threadid).'&amp;status='.$post['type'].'_permission'.(($post['type'] == 'post') ? '&amp;post='.$post['id'] : null).'"><!--Permissions--></a>';
						endif?>
					</div>
				</div>
				</td>
			</tr>
		<?endforeach;
	else:
		print '<td class="thread_main" colspan="100%">No posts found.</td>';
	endif?>
	</tbody>
</table>

<script type="text/javascript">
	/*<![CDATA[*/
		function AJAXPost()
		{
			var form = document.forms['post'];
			var AJAX_post = form.elements['AJAX_post'];
			var action = '<?=$globals->_SERVER['PHP_SELF']?>?action=forum&forum=<?=$vars->forumid?>&status=post&thread=<?=$vars->threadid?>&ajax=1';
			
			//IE Hack
			if(navigator.userAgent.match(/MSIE/))
			{
				AJAX_post.checked = false;
			}
			
			if(AJAX_post.checked == true)
			{
				AJAX('post', document.getElementsByTagName('body')[0], action, false);
			}
			else
			{
				form.action = action;
				form.submit();
			}
		}
	/*]]>*/
</script>

<div class="f_header3">
	<div style="float: left;">
		<?=(isset($vars->pagination)) ? $vars->pagination : null?>
		<?if(!empty($post['permissions']) && $post['permissions']['moderator']):?>
			<br />
			<select style="color: <?=$globals->alias_init->user['Moderator']['Mod']?>;" onchange="window.location = this.value;">
			<option onclick="return false;">Moderate Thread</option>
				<?if(!$post['permissions']['closed']):?>
					<option value="<?=$globals->_SERVER['PHP_SELF']?>?action=forum&amp;forum=<?=$vars->forumid?>&amp;thread=<?=$vars->threadid?>&amp;status=thread_permission">Permissions</option>
					<option value="<?=$globals->_SERVER['PHP_SELF']?>?action=forum&amp;forum=<?=$vars->forumid?>&amp;thread=<?=$vars->threadid?>&amp;status=thread_close">Lock</option>
					<option value="<?=$globals->_SERVER['PHP_SELF']?>?action=forum&amp;forum=<?=$vars->forumid?>&amp;status=thread_sticky&amp;thread=<?=$vars->threadid?>">Sticky</option>
					<option value="<?=$globals->_SERVER['PHP_SELF']?>?action=forum&amp;forum=<?=$vars->forumid?>&amp;status=thread_split&amp;thread=<?=$vars->threadid?>">Split</option>
					<option value="<?=$globals->_SERVER['PHP_SELF']?>?action=forum&amp;forum=<?=$vars->forumid?>&amp;status=thread_join&amp;thread=<?=$vars->threadid?>">Join</option>
					<option value="<?=$globals->_SERVER['PHP_SELF']?>?action=forum&amp;forum=<?=$vars->forumid?>&amp;status=thread_move&amp;thread=<?=$vars->threadid?>">Move</option>
				<?else:?>
					<option value="<?=$globals->_SERVER['PHP_SELF']?>?action=forum&amp;forum=<?=$vars->forumid?>&amp;status=thread_open&amp;thread=<?=$vars->threadid?>">Open</option>
					<option value="<?=$globals->_SERVER['PHP_SELF']?>?action=forum&amp;forum=<?=$vars->forumid?>&amp;status=thread_close&amp;thread=<?=$vars->threadid?>">Purge</option>
				<?endif?>
			</select>
		<?endif?>
	</div>
	<a class="post" href="#post" onclick="<?=((!isset($vars->post_link_type) || $vars->post_link_type != 'closed') && isset($globals->alias_init->alias)) ? 'fade(\'post\');' : 'return false;'?>"><?=((!isset($vars->post_link_type)) ? 'Post' : ucfirst($vars->post_link_type))?></a><br />
</div>
<script type="text/javascript">
	/*<![CDATA[*/
		load('wysiwyg.init()');
		var wysiwyg = {
			editor: null,
			enabled: true,
			
			init: function()
			{
				document.getElementById('load').parentNode.removeChild(document.getElementById('load'));
				
				this.editor = document.getElementById('_post');
				
				//IE hack
				if(navigator.userAgent.match(/MSIE/))
				{
					this.switchMode();
					return;
				}
				
				this.editor.contentDocument.designMode = 'on';
			},
			exec: function(type, arg)
			{
				if(this.enabled)
				{
					switch(type)
					{
						case 'colour':
							this.editor.contentDocument.execCommand('forecolor', false, arg);
						break;
						case 'bold': case 'italic': case 'underline':
							this.editor.contentDocument.execCommand(type, false, arg);
						break;
						case 'link':
							this.editor.contentDocument.execCommand('createlink', false, arg);
						break;
						case 'image':
							this.editor.contentDocument.execCommand('insertimage', false, arg);
						break;
					}
				}
				else
				{
					switch(type)
					{
						case 'colour':
							code('[color='+arg+']', '[/color]');
						break;
						case 'bold': case 'italic': case 'underline':
							code('['+type.substr(0, 1)+']', '[/'+type.substr(0, 1)+']');
						break;
						case 'link':
							code('[url='+arg+']', '[/url]');
						break;
						case 'image':
							code('[img='+arg+']', '');
						break;
					}
				}
			},
			get: function()
			{
				var post = this.editor.contentDocument.body.innerHTML;
				
				post = post.replace('&lt;', '<').replace('&gt;', '>');
				post = post.replace(new RegExp('\<br.*?\>', 'g'), '\n');
				
				post = post.replace(new RegExp('\<span style\=\"color\: (.*?)\;\"\>(.*?)\<\/span\>', 'g'), '[color=$1]$2[/color]');
				post = post.replace(new RegExp('\<span style\=\"font\-weight\: bold\;\"\>(.*?)\<\/span\>', 'g'), '[b]$1[/b]');
				post = post.replace(new RegExp('\<span style\=\"font\-style\: italic\;\"\>(.*?)\<\/span\>', 'g'), '[i]$1[/i]');
				post = post.replace(new RegExp('\<span style\=\"text\-decoration\: underline\;\"\>(.*?)\<\/span\>', 'g'), '[u]$1[/u]');
				post = post.replace(new RegExp('\<a href\=\"(.*?)\"\>(.*?)\<\/a\>', 'g'), '[url=$1]$2[/url]');
				post = post.replace(new RegExp('\<img.*? src\=\"(.*?)\"\>', 'g'), '[img=$1]');
				
				return post;
			},
			switchMode: function()
			{
				this.enabled = !this.enabled;
				fade(this.editor);
				this.editor.style.display = 'none';
				this.editor.style.margin = '0 auto';
				fade(document.forms['post'].post);
				document.forms['post'].post.style.margin = '0 auto';
			}
		};
	/*]]>*/
</script>
<form id="post" action="" method="post" enctype="application/x-www-form-urlencoded" style="display: none;"><div>
	<div style="border: 1px dotted purple; padding: 10px 5px 0; position: relative; margin: 50px auto 0; width: 75%;">
		<div style="text-align: left;">
			<span style="font-style: italic; font-weight: bold; text-decoration: underline; font-size: 1.1em;">WYSIWYG Post <img id="load" src="./templates/css/img/icons/wysiwyg/load.gif" alt="Loading..." /></span><br />
			<div style="float: left;">
				<a href="#post" onclick="fade('colours');"><img src="./templates/css/img/icons/wysiwyg/colour.gif" style="border: 1px solid white;" onmouseover="this.style.borderColor = '#000000';" onmouseout="this.style.borderColor = 'white';" alt="Colour" title="Colour" /></a>
				<div id="colours" style="width: 50px; height: 10px; position: absolute; display: none; border: 1px dotted #C1CDCD;">
					<?foreach(array('black', 'blue', 'green', 'red', 'yellow') as $colour):?>
						<a style="display: block; float: left; width: 10px; height: 10px; background-color: <?=$colour?>;" href="#post" onclick="wysiwyg.exec('colour', '<?=$colour?>');"></a>
					<?endforeach?>
				</div>
				<a href="#post" onclick="wysiwyg.exec('bold');"><img src="./templates/css/img/icons/wysiwyg/bold.gif" style="border: 1px solid white;" onmouseover="this.style.borderColor = '#000000';" onmouseout="this.style.borderColor = 'white';" alt="Bold" title="Bold" /></a>
				<a href="#post" onclick="wysiwyg.exec('italic');"><img src="./templates/css/img/icons/wysiwyg/italic.gif" style="border: 1px solid white;" onmouseover="this.style.borderColor = '#000000';" onmouseout="this.style.borderColor = 'white';" alt="Italic" title="Italic" /></a>
				<a href="#post" onclick="wysiwyg.exec('underline');"><img src="./templates/css/img/icons/wysiwyg/underline.gif" style="border: 1px solid white;" onmouseover="this.style.borderColor = '#000000';" onmouseout="this.style.borderColor = 'white';" alt="Underline" title="Underline" /></a>
				<a href="#post" onclick="wysiwyg.exec('link', prompt('Link:'));"><img src="./templates/css/img/icons/wysiwyg/link.gif" style="border: 1px solid white;" onmouseover="this.style.borderColor = '#000000';" onmouseout="this.style.borderColor = 'white';" alt="Link" title="Link" /></a>
				<a href="#post" onclick="wysiwyg.exec('image', prompt('Image:'));"><img src="./templates/css/img/icons/wysiwyg/image.gif" style="border: 1px solid white;" onmouseover="this.style.borderColor = '#000000';" onmouseout="this.style.borderColor = 'white';" alt="Image" title="Image" /></a>
				<select style="position: relative; top: -7px;" onchange="code('[code='+this.value+']', '[/code]');">
					<option>Insert Code</option>
					<?//alias of geshi::smiley() (for code)
					foreach(glob($_SERVER['DOCUMENT_ROOT'].'/bin/geshi/lang/*.php') as $file):
						$lang = basename($file, '.php');
						print '<option value="'.$lang.'">'.$lang.'</option>';
					endforeach?>
				</select>
				<a href="#post" onclick="wysiwyg.switchMode();"><img src="./templates/css/img/icons/wysiwyg/mode.gif" style="border: 1px solid white;" onmouseover="this.style.borderColor = '#000000';" onmouseout="this.style.borderColor = 'white';" alt="Mode" title="Mode" /></a>
			</div>
			<div style="float: right;">
				<em>Smileys</em>:
				<?//alias of geshi::smiley()
				$dir = opendir($_SERVER['DOCUMENT_ROOT'].'/templates/css/img/icons/smileys');
				while($file = readdir($dir)):
					$smiley = basename($file, '.gif');
					if(!in_array($file, array('.', '..'))):
						print '<a href="javascript: void(0);" onclick="code(\''.$smiley.'\', \' \');"><img src="./templates/css/img/icons/smileys/'.$file.'"></a> ';
					endif;
				endwhile;
				closedir($dir);
				?>
			</div>
		</div>
		<iframe id="_post" class="post"></iframe>
		<input type="hidden" name="subject" value="RE: <?=$vars->t_subject?>" />
		<textarea name="post" class="post" style="display: none;"></textarea><br /><br />
		<input type="button" class="post" value="Preview" onclick="if(wysiwyg.enabled) this.parentNode.parentNode.parentNode.post.value = wysiwyg.get(); AJAX('post');" /><input type="button" class="post" value="Post" onclick="if(wysiwyg.enabled) this.parentNode.parentNode.parentNode.post.value = wysiwyg.get(); AJAXPost();" />
		<?=template::unique_hash()?>
		<?=parse::parse_options(null, array('Post Options' => array('AJAX' => '<input type="checkbox" id="AJAX_post" checked="true" />')))?>
		<div id="ajax"></div>
	</div>
</div></form>
<?}?>
<?
//...
?>
<?function forum_search(stdclass $vars, stdclass $globals)
{?>
<div class="action">Search <?=((!isset($vars->threadid)) ? 'Forum' : 'Thread')?></div>
<form id="search" action="<?=$globals->_SERVER['PHP_SELF']?>" method="get" enctype="application/x-www-form-urlencoded"><div>
	<?=template::get_query_string(array(
	'action',
	#'status',
	'forum',
	(isset($vars->threadid)) ? 'thread' : null
	))?>
	<input type="hidden" name="status" value="<?=((!isset($vars->threadid)) ? 'forum' : 'thread')?>" />
	
	<dl class="justify">
		<dt>Author:</dt><dd><input type="text" name="author" /></dd>
		
		<dt>Search:</dt><dd><input type="text" name="search" /></dd>
	</dl><br />
	<input type="submit" class="post" value="Search" />
</div></form>
<?}?>
<?
//...
?>
<?function forum_post(stdclass $vars, stdclass $globals)
{?>
<?switch($vars->post_type)
{
	case 'FORUM':?>
		<div class="action">Post Forum</div>
		<form id="forum" action="<?=$globals->_SERVER['REQUEST_URI']?>" method="post" enctype="application/x-www-form-urlencoded"><div>
			Subject:<br /><input type="text" name="subject" class="post" /><br /><br />
			Forum:<br /><textarea name="forum" class="post"></textarea><br /><br />
			<input type="button" class="post" value="Preview" onclick="AJAX('forum');" /><input type="submit" class="post" value="Post" />
			<?=template::unique_hash()?>
			<?$permissions = '';
			foreach($vars->permissions as $permission):
				$permissions .= '<option style="color: '.$permission['color'].';" value="%1$s('.$permission['userlevel'].')">'.$permission['name'].'s</option>';
			endforeach?>
			<?=template::parse_options(null, ($vars->parse_extension) ? array('Permissions' => array(
			'Permission'   => '<select name="permission">
				<option value="open">Open</option>
				<option value="moderated('.$globals->alias_init->user('Moderator').')">Moderated</option>
				<option value="closed('.$globals->alias_init->user('Moderator').')">Closed</option>
				<optgroup label="Private">'
					.sprintf($permissions, 'private').
					'<option id="private_specify" onclick="document.getElementById(\'specify_display\').setAttribute(\'onkeyup\', \'populate_permission(\\\'private\\\')\'); fade(\'specify_display\');">Specify &raquo;</option>
				</optgroup>
				<optgroup label="Restrict">'
					.sprintf($permissions, 'restricted').
					'<option id="restrict_specify" onclick="document.getElementById(\'specify_display\').setAttribute(\'onkeyup\', \'populate_permission(\\\'restricted\\\')\'); fade(\'specify_display\');">Specify &raquo;</option>
				</optgroup>
				<optgroup label="Hide">'
					.sprintf($permissions, 'hidden').
					'<option id="hidden_specify" onclick="document.getElementById(\'specify_display\').setAttribute(\'onkeyup\', \'populate_permission(\\\'hidden\\\')\'); fade(\'specify_display\');">Specify &raquo;</option>
				</optgroup>
			</select>
			<div id="specify_display" style="display: none;">
				<input type="text" id="specify_opt" />
			</div>',
			'Mods'         => '<input type="text" name="mod" />',
			'Order'        => '<input type="text" name="sticky" value="0" style="width: 25px;" onkeyup="numVal(this);" />',
			'Parent Forum' => '<input type="text" name="parent_forum" style="width: 25px;" onkeyup="numVal(this);" /> (<a href="#" onclick="window.open(\'./main/misc.php?status=cpanel&amp;cmd=forum_config\', \'forum_config\', \'width=400px, height=100px, toolbar=false\');">Search Forums</a>)'
			)) : null)?>
			<div id="ajax"></div>
		</div></form>
	<?break;
	case 'THREAD':?>
		<div class="action">Post Thread</div>
		<form id="thread" action="<?=$globals->_SERVER['REQUEST_URI']?>" method="post" enctype="application/x-www-form-urlencoded"><div>
			Subject:<br /><input type="text" name="subject" class="post" /><br /><br />
			Thread:<br /><textarea name="thread" class="post"></textarea><br /><br />
			<input type="button" class="post" value="Preview" onclick="AJAX('thread');" /><input type="submit" class="post" value="Post" />
			<?=template::unique_hash()?>
			<?$permissions = '';
			foreach($vars->permissions as $permission):
				$permissions .= '<option style="color: '.$permission['color'].';" value="%1$s('.$permission['userlevel'].')">'.$permission['name'].'s</option>';
			endforeach?>
			<?=template::parse_options(null, ($vars->parse_extension) ? array('Permissions' => array(
			'Permission'     => '<select name="permission">
				<option value="open">Open</option>
				<option value="moderated('.$globals->alias_init->user('Moderator').')">Moderated</option>
				<option value="closed('.$globals->alias_init->user('Moderator').')">Closed</option>
				<optgroup label="Private">'
					.sprintf($permissions, 'private').
					'<option id="private_specify" onclick="document.getElementById(\'specify_display\').setAttribute(\'onkeyup\', \'populate_permission(\\\'private\\\')\'); fade(\'specify_display\');">Specify &raquo;</option>
				</optgroup>
				<optgroup label="Restrict">'
					.sprintf($permissions, 'restricted').
					'<option id="restrict_specify" onclick="document.getElementById(\'specify_display\').setAttribute(\'onkeyup\', \'populate_permission(\\\'restricted\\\')\'); fade(\'specify_display\');">Specify &raquo;</option>
				</optgroup>
				<optgroup label="Hide">'
					.sprintf($permissions, 'hidden').
					'<option id="hidden_specify" onclick="document.getElementById(\'specify_display\').setAttribute(\'onkeyup\', \'populate_permission(\\\'hidden\\\')\'); fade(\'specify_display\');">Specify &raquo;</option>
				</optgroup>
			</select>
			<div id="specify_display" style="display: none;">
				<input type="text" id="specify_opt" />
			</div>',
			'Mods'          => '<input type="text" name="mod" />',
			'Global Sticky' => '<input type="radio" name="sticky" value="2" />',
			'Sticky'        => '<input type="radio" name="sticky" value="1" />',
			'Normal'        => '<input type="radio" name="sticky" value="0" checked="true"a />'
			)) : null)?>
			<div id="ajax"></div>
		</div></form>
	<?break;
	case 'POST':?>
		<div class="action">Post</div>
		<form id="post" action="<?=$globals->_SERVER['REQUEST_URI']?>" method="post" enctype="application/x-www-form-urlencoded"><div>
			Subject:<br /><input type="text" name="subject" class="post" value="RE: <?=implode(', ', (array) $vars->subject)?>" /><br /><br />
			Post:<br /><textarea name="post" class="post"><?=implode("\n", (array) $vars->post)?></textarea><br /><br />
			<input type="button" class="post" value="Preview" onclick="AJAX('post');" /><input type="submit" class="post" value="Post" />
			<?=template::unique_hash()?>
			<?=parse::parse_options(null, ($vars->parse_extension) ? array('Permissions' => array('Announcement' => '<input type="checkbox" name="sticky" value="1" />')) : null)?>
			<div id="ajax"></div>
		</div></form>
	<?break;
}?>
<?}?>
<?
//...
?>
<?function forum_edit(stdclass $vars, stdclass $globals)
{?>
	<div class="action">Edit <?=ucfirst($vars->type)?></div>
	<form id="edit" action="<?=$globals->_SERVER['REQUEST_URI']?>" method="post" enctype="application/x-www-form-urlencoded"><div>
		Subject:<br /><input type="text" name="subject" class="post" value="<?=$vars->subject?>" /><br /><br />
		Post:<br /><textarea name="post" class="post"><?=$vars->post?></textarea><br /><br />
		<input type="button" class="post" value="Preview" onclick="AJAX('edit');" /><input type="submit" class="post" value="Save" />
		<?=template::unique_hash()?>
		<?=template::parse_options($vars->parse_options, ($vars->parse_extension) ? array('Permissions' => array('Edit' => '<a href="#" onclick="window.location = String(window.location).replace(\'_edit\', \'_permission\');">&raquo</a>')) : null)?>
		<div id="ajax"></div>
	</div></form>
<?}?>
<?
//...
?>
<?function forum_subscribe(stdclass $vars, stdclass $globals)
{?>
You are about to <?=$vars->subscription_type?> to this <?=(!isset($vars->threadid)) ? 'forum' : 'thread'?>.<br />
<a class="control" href="#" onclick="form_submit('<?=$globals->_SERVER['REQUEST_URI']?>');">Continue</a>, <a class="control" href="<?=$globals->_SERVER['HTTP_REFERER']?>">Back</a>.
<?}?>
<?
//...
?>
<?function forum_permission(stdclass $vars, stdclass $globals)
{?>
<div class="action"><?=ucfirst($vars->type)?> Permissions</div>
This <?=$vars->type?> possesses permissions: "<?=$vars->permission?>".<br /><br />
<form id="permission" action="<?=$globals->_SERVER['REQUEST_URI']?>" method="post" enctype="application/x-www-form-urlencoded"><div>
	<dl class="justify" style="width: 30%;">
		<dt>Author:</dt><dd><input type="text" name="author" value="<?=$vars->author?>" /></dd>
		<dt>Edit:</dt><dd><input type="text" name="edit" value="<?=$vars->edit?>" /></dd>
		<dt style="margin-bottom: 10px;">Mods:</dt><dd><input type="text" name="mod" value="<?=$vars->mod?>" /></dd>
		
		<?$permissions = '';
		foreach($vars->permissions as $permission):
			$permissions .= '<option style="color: '.$permission['color'].';" value="%1$s('.$permission['userlevel'].')">'.$permission['name'].'s</option>';
		endforeach?>
		<dt><a href="#permissions_edit" onclick="fade('permissions_edit'); with(document.getElementById('permissions_edit')){name = 'permission'; disabled = false;}">Permissions:</a></dt><dd><select id="permission" name="permission">
			<option value="open">Open</option>
			<option value="moderated(<?=$globals->alias_init->user('Moderator')?>)">Moderated</option>
			<option value="closed(<?=$globals->alias_init->user('Moderator')?>)">Closed</option>
			<optgroup label="Private">
					<?=sprintf($permissions, 'private')?>
					<option id="private_specify" onclick="document.getElementById('specify_opt').setAttribute('onkeyup', 'populate_permission(\'private\')'); fade('specify_opt');">Specify &raquo;</option>
				</optgroup>
				<optgroup label="Restrict">
					<?=sprintf($permissions, 'restricted')?>
					<option id="restrict_specify" onclick="document.getElementById('specify_opt').setAttribute('onkeyup', 'populate_permission(\'restricted\')'); fade('specify_opt');">Specify &raquo;</option>
				</optgroup>
				<optgroup label="Hide">
					<?=sprintf($permissions, 'hidden')?>
					<option id="hidden_specify" onclick="document.getElementById('specify_opt').setAttribute('onkeyup', 'populate_permission(\'hidden\')'); fade('specify_opt');">Specify &raquo;</option>
				</optgroup>
		</select><br />
		<input type="text" id="specify_opt" style="display: none;" />
		<input type="text" id="permissions_edit" name="_permission" style="display: none;" disabled="true" value="<?=$vars->permission?>" /></dd>
		
		<?if($vars->type == 'forum'):?>
			<dt>Order</dt><dd><input type="text" name="sticky" style="width: 25px;" value="<?=$vars->sticky?>" onkeyup="numVal(this);" /></dd>
		<?else:
			if($vars->type == 'thread'):?>
				<dt>Global Sticky</dt><dd><input type="radio" name="sticky" value="2" <?=($vars->sticky == 2) ? 'checked="true"' : null?> /></dd>
			<?endif?>
			<dt><?=($vars->type == 'thread') ? 'Sticky' : 'Announcement'?></dt><dd><input type="radio" name="sticky" value="1" <?=($vars->sticky == 1) ? 'checked="true"' : null?> /></dd>
			<dt>Normal</dt><dd><input type="radio" name="sticky" value="0" <?=($vars->sticky == 0) ? 'checked="true"' : null?> /></dd>
		<?endif?>
		</dl>
	<a class="control" href="#" onclick="document.forms['permission'].submit();">Continue</a>, <a class="control" href="<?=$globals->_SERVER['HTTP_REFERER']?>">Back</a>.
</div></form>
<?}?>
<?
//...
?>
<?function forum_delete(stdclass $vars, stdclass $globals)
{?>
<?switch($vars->type):
	case 'forum':?>
	<form id="close" action="<?=$globals->_SERVER['REQUEST_URI']?>" method="post" enctype="application/x-www-form-urlencoded"><div>
		You are about to delete this forum.<br /><br />
		With posts:<br />
		<dl class="justify">
			<dt>Delete</dt><dd><input type="radio" name="posts" checked="true" /></dd>
			<dt>Move</dt><dd><input type="text" name="posts" style="width: 25px;" onkeyup="numVal(this); document.forms['post_delete'].posts[0].disabled = (this.value.length != 0) ? true : false;" /></dd>
		</dl>
		<a class="control" href="#" onclick="document.forms['close'].submit();">Continue</a>, <a class="control" href="<?=$globals->_SERVER['HTTP_REFERER']?>">Back</a>.
	</div></form>
	<?break;
	case 'thread':?>
		<?switch($vars->thread_type):
			case 'close':
				print 'You are about to close this thread.';
			break;
			case 'delete':
				print 'This thread appears to have no posts, thus can only be deleted.';
			break;
			case 'purge':
				print 'You are about to purge this thread.';
			break;
		endswitch?><br />
		<a class="control" href="#" onclick="form_submit('<?=$globals->_SERVER['REQUEST_URI']?>');">Continue</a>, <a class="control" href="<?=$globals->_SERVER['HTTP_REFERER']?>">Back</a>.
	<?break;
	case 'post':?>
		You are about to delete this post.<br />
		<a class="control" href="#" onclick="form_submit('<?=$globals->_SERVER['REQUEST_URI']?>');">Continue</a>, <a class="control" href="<?=$globals->_SERVER['HTTP_REFERER']?>">Back</a>.
	<?break;
endswitch?>
<?}?>
<?
//...
?>
<?function forum_thread_open(stdclass $vars, stdclass $globals)
{?>
You are about to open this thread.<br />
<a class="control" href="#" onclick="form_submit('<?=$globals->_SERVER['REQUEST_URI']?>');">Continue</a>, <a class="control" href="<?=$globals->_SERVER['HTTP_REFERER']?>">Back</a>.
<?}?>
<?
//...
?>
<?function forum_thread_sticky(stdclass $vars, stdclass $globals)
{?>
You are about to sticky this thread.<br />
<a class="control" href="#" onclick="form_submit('<?=$globals->_SERVER['REQUEST_URI']?>');">Continue</a>, <a class="control" href="<?=$globals->_SERVER['HTTP_REFERER']?>">Back</a>.
<?}?>
<?
//...
?>
<?function forum_thread_split(stdclass $vars, stdclass $globals)
{?>
You are about to split this thread.<br />
<form id="split" action="<?=$globals->_SERVER['REQUEST_URI']?>" method="post" enctype="application/x-www-form-urlencoded"><div>
	Split on: <input type="text" name="thread_split" style="width: 25px;" onkeyup="numVal(this);" /><br />
	<a class="control" href="#" onclick="document.forms['split'].submit();">Continue</a>, <a class="control" href="<?=$globals->_SERVER['HTTP_REFERER']?>">Back</a>.
</div></form>
<?}?>
<?
//...
?>
<?function forum_thread_join(stdclass $vars, stdclass $globals)
{?>
You are about to join this thread.<br />
<form id="join" action="<?=$globals->_SERVER['REQUEST_URI']?>" method="post" enctype="application/x-www-form-urlencoded"><div>
	Join on: <input type="text" name="thread_join" style="width: 25px;" onkeyup="numVal(this);" /><br />
	<a class="control" href="#" onclick="document.forms['join'].submit();">Continue</a>, <a class="control" href="<?=$globals->_SERVER['HTTP_REFERER']?>">Back</a>.
</div></form>
<?}?>
<?
//...
?>
<?function forum_thread_move(stdclass $vars, stdclass $globals)
{?>
You are about to move this thread.<br />
<form id="move" action="<?=$globals->_SERVER['REQUEST_URI']?>" method="post" enctype="application/x-www-form-urlencoded"><div>
	Move to forum: <input type="text" name="thread_move" style="width: 25px;" onkeyup="numVal(this);" /><br />
	<a class="control" href="#" onclick="document.forms['move'].submit();">Continue</a>, <a class="control" href="<?=$globals->_SERVER['HTTP_REFERER']?>">Back</a>.
</div></form>
<?}?>
<?
//...
?>
<?function forum_post_report(stdclass $vars, stdclass $globals)
{?>
You are about to report this post.<br />
<form id="report" action="<?=$globals->_SERVER['REQUEST_URI']?>" method="post" enctype="application/x-www-form-urlencoded"><div>
	Reason: <input type="text" name="post_report" /><br />
	<a class="control" href="#" onclick="document.forms['report'].submit();">Continue</a>, <a class="control" href="<?=$globals->_SERVER['HTTP_REFERER']?>">Back</a>.
</div></form>
<?}?>