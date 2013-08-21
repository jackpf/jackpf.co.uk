<?function blog_main(stdclass $vars, stdclass $globals)
{?>
<link rel="icon" type="image/x-icon" href="/templates/css/img/icon.ico" />
<link rel="stylesheet" type="text/css" href="/templates/css/css.css" />
<link rel="stylesheet" type="text/css" href="/templates/css/css-forum.css" />
<link rel="stylesheet" type="text/css" href="/templates/css/css-profile.css" />
<link rel="stylesheet" type="text/css" href="/templates/css/css-blog.css" />
<script type="text/javascript" src="/templates/js/js.js"></script>
<script type="text/javascript" src="/templates/js/ajax.js"></script>
<script type="text/javascript" src="/templates/js/blog.js"></script>
<!--[if lte IE 7]>
	<style type="text/css">
		/*<![CDATA[*/
			html, body
			{
				overflow-x: hidden;
			}
			div.blog_main
			{
				width: 64.9%;
			}
			.h_title
			{
				width: 19.9%;
			}
		/*]]>*/
	</style>
<![endif]-->

<div class="header-side">
<ul class="header-side">
	<li class="title">
		Recent Entries
	</li>
	<?foreach($vars->recent_entries as $recent_entry): print '<li><a href="/blog/'.$recent_entry['id'].'">'.$recent_entry['Subject'].'</a></li>'; endforeach?>
	<li class="legacy">
			<?=$vars->entry_count_total?> Blog Entries<br />
			[<?=$vars->entry_count_visible?> visible, <?=$vars->entry_count_private?> private, <?=$vars->entry_count_hidden?> hidden, <?=$vars->entry_count_archive?> archived]
		</li>
		<li class="subscribe">
			<a href="/blog/subscribe">Subscribe</a>
		</li>
	</ul>
</div>

<div class="header-side header-side-right">
	<ul class="header-side">
		<li class="title">
			Categories
		</li>
		<?foreach($vars->entry_categories as $category): print '<li><a href="/blog/category='.$category.'">'.template::header_active($category, 'category', $category).'</a></li>'; endforeach;?>
		<?if($vars->entry_archive):?>
			<li>
				<a onmouseover="slide.exec('in');" onmouseout="slide.exec('out');" href="/blog/category=archive"><?=template::header_active('Archive', 'category', 'archive')?></a>
				<div id="archive">
					<?foreach($vars->entry_archive as $archive):
						print '&middot; <a href="/blog/'.$archive['id'].'">'.$archive['subject'].'</a><br />';
					endforeach?>
				</div>
				<script type="text/javascript">
					/*<![CDATA[*/
						slide.init(document.getElementById('archive'));
					/*]]>*/
				</script>
			</li>
		<?endif;?>
		<li>
			<form id="search" action="<?=$globals->_SERVER['REQUEST_URI']?>" method="post" enctype="application/x-www-form-urlencoded"><div>
				<input type="text" name="search" value="<?((isset($globals->_GET['search'])) ? $globals->_GET['search'] : null)?>" onkeypress="return s(event);" /><input type="button" class="post" value="Search" onclick="s();" />
			</div></form>
		</li>
	</ul>
</div>
<div class="blog_main">
	<?=$vars->blog_main?>
</div>
<?}?>
<?
//...
?>
<?function blog_index(stdclass $vars, stdclass $globals)
{?>
<?=$vars->entry_pagination?>
<?if($vars->new_entry_link): print '<div class="b_header2"><a href="/blog/entry">New Entry &raquo;</a></div>'; endif?>
<?if(sizeof($vars->entries)):
	foreach($vars->entries as $entry):?>
		<div id="Entry:<?=$entry['id']?>" class="entry_shell">
			<?if($vars->edit_link): print '<div class="entry_control"><a href="/blog/'.$entry['id'].'/edit">Edit</a> / <a href="/blog/'.$entry['id'].'/permission">Permissions &raquo;</a></div>'; endif?>
			<div class="entry_h">
				<a class="entry_subject" href="/blog/<?=$entry['id']?>"><?=$entry['subject']?></a><br />
				<span class="entry_info">Posted in &ldquo;<?=$entry['category']?>&rdquo; on <?=$entry['date'].$entry['edit']?>, by <?=$entry['author']?>.</span>
			</div>
			<div class="entry_body">
				<?=$entry['entry']?><br /><br /><a class="entry_info" style="font-size: 0.9em;" href="/blog/<?=$entry['id']?>">(view entry)</a>
			</div>
			<div class="entry_f">
				<a href="/blog/<?=$entry['id']?>#comments"><?=$entry['comments']?> Comment<?=($entry['comments'] <> 1) ? 's' : null?></a>
			</div>
		</div>
	<?endforeach?>
<?else:?>
	<div class="center">There are no entries to be found.</div>
<?endif?>
<?}?>
<?
//...
?>
<?function blog_view_entry(stdclass $vars, stdclass $globals)
{?>
<div class="entry_shell">
	<?if($vars->entry['edit_link']): print '<div class="entry_control"><a href="/blog/'.$vars->entry['id'].'/edit">Edit</a> / <a href="/blog/'.$vars->entry['id'].'/permission">Permissions &raquo;</a></div>'; endif?>
	<div class="entry_h">
		<span class="entry_subject"><?=$vars->entry['subject']?></span><br />
		<span class="entry_info">Posted in &ldquo;<?=$vars->entry['category']?>&rdquo; on <?=$vars->entry['date'].$vars->entry['edit']?>, by <?=$vars->entry['author']?>.</span>
	</div>
	<div class="entry_body">
		<?=$vars->entry['entry']?>
	</div>
	<div class="entry_f">
		<?=(isset($vars->comment_link)) ? '<a href="#comment" onclick="fade(\'comment\');">Post Comment (AJAX)</a> | <a href="/blog/'.$vars->entry['id'].'/comment">Post Comment</a> | <a href="/blog/'.$vars->entry['id'].'/subscribe">Subscribe</a>' : '<a href="/index.php?action=login&amp;referer='.$globals->_SERVER['REQUEST_URI'].'">Login to comment</a>'?>
	</div>
</div>

<div id="comment" class="box" style="display: none;">
	<div class="action">Comment</div>
	<form id="AJAXComment" action="" method="post" enctype="application/x-www-form-urlencoded"><div>
		<input type="hidden" name="subject" value="<?=$vars->entry['subject']?>" />
		<textarea name="comment" class="post"></textarea><br />
		<div class="textarea_drag" onmousedown="return textarea_drag.init(document.forms['AJAXComment'].comment, event);"></div><br />
		<input type="button" class="post" value="Cancel" onclick="fade('comment');" /><input type="button" class="post" value="Post" onclick="AJAXComment('/blog/<?=$vars->entry['id']?>/comment');" />
	</div></form>
</div>

<a id="comments" href="#comments">Comments</a>
<div id="comments_page">
	<?=$vars->comment_pagination?>
</div>
<?if(sizeof($vars->comments)):?>
	<?foreach($vars->comments as $comment):?>
		<div class="entry_comment_shell <?=$comment['class']?>">
			<?if($comment['edit_link']): print '<div class="entry_control"><a href="/blog/'.$comment['id'].'/edit_comment">Edit</a> / <a href="/blog/'.$comment['id'].'/delete_comment">Delete</a></div>'; endif?>
			<div>
				<span class="Subject"><?=$comment['subject']?></span><br />
				<span class="entry_info"><?=$comment['date'].$comment['edit']?> by <?=$comment['author']?>.</span>
			</div><br />
			<div>
				<?=$comment['comment']?>
			</div>
		</div>
	<?endforeach?>
<?else:?>
	<div class="entry_comment_shell">There are no comments to be found for this entry.</div>
<?endif?>
<?}?>
<?
//...
?>
<?function blog_comment(stdclass $vars, stdclass $globals)
{?>
<div class="center">
	<form id="comment" action="<?=$globals->_SERVER['REQUEST_URI']?>" method="post" enctype="application/x-www-form-urlencoded"><div>
		Subject:<br /><input type="text" name="subject" class="post" value="RE: <?=$vars->entry_subject?>" /><br /><br />
		Comment:<br /><textarea name="comment" class="post"></textarea><br /><br />
		<div class="textarea_drag" onmousedown="return textarea_drag.init(document.forms['comment'].comment, event);"></div><br />
		<input type="button" class="post" value="Preview" onclick="AJAX('comment');" /><input type="submit" class="post" value="Post" />
		<?=template::unique_hash()?>
		<?=template::parse_options()?>
		<div id="ajax"></div>
	</div></form>
</div>
<?}?>
<?
//...
?>
<?function blog_edit_comment(stdclass $vars, stdclass $globals)
{?>
<div class="center">
	<form id="edit" action="<?=$globals->_SERVER['REQUEST_URI']?>" method="post" enctype="application/x-www-form-urlencoded"><div>
		Subject:<br /><input type="text" name="subject" class="post" value="<?=$vars->comment_subject?>" /><br /><br />
		Comment:<br /><textarea name="edit" class="post"><?=$vars->comment_comment?></textarea><br /><br />
		<div class="textarea_drag" onmousedown="return textarea_drag.init(document.forms[\'edit\'].edit, event);"></div><br />
		<input type="button" class="post" value="Preview" onclick="AJAX('edit');" /><input type="submit" class="post" value="Save" />
		<?=template::unique_hash()?>
		<?=template::parse_options($vars->options, null)?>
		<div id="ajax"></div>
	</div></form>
</div>
<?}?>

<?function blog_delete_comment(stdclass $vars, stdclass $globals)
{?>
<div class="center">
	You are about to delete this comment.<br />
	<a class="control" href="#" onclick="form_submit('<?=$globals->_SERVER['REQUEST_URI']?>');">Continue</a>, <a class="control" href="<?=$globals->_SERVER['HTTP_REFERER']?>">Back</a>
</div>
<?}?>
<?
//...
?>
<?function blog_subscribe(stdclass $vars, stdclass $globals)
{?>
<div class="center">
	<?switch($vars->subscription_type):
		case 'blog_subscribe':
			print 'You are about to subscribe to this blog.';
		break;
		case 'blog_unsubscribe':
			print 'You are about to unsubscribe from this blog.';
		break;
		case 'entry_subscribe':
			print 'You are about to subscribe to this entry.';
		break;
		case 'entry_unsubscribe':
			print 'You are about to unsubscribe from this entry.';
		break;
	endswitch?><br />
	<a class="control" href="#" onclick="form_submit('<?=$globals->_SERVER['REQUEST_URI']?>');">Continue</a>, <a class="control" href="<?=$globals->_SERVER['HTTP_REFERER']?>">Back</a>
</div>
<?}?>
<?
//...
?>
<?function blog_entry(stdclass $vars, stdclass $globals)
{?>
<div class="center">
	<div class="action">New Entry</div>
	
	<form id="entry" action="<?=$globals->_SERVER['REQUEST_URI']?>" method="post" enctype="application/x-www-form-urlencoded"><div>
		Subject:<br /><input type="text" name="subject" class="post" /><br /><br />
		Entry:<br /><textarea name="entry" class="post"></textarea><br />
		<div class="textarea_drag" onmousedown="return textarea_drag.init(document.forms[\'entry\'].entry, event);"></div><br />
		<input type="button" class="post" value="Preview" onclick="AJAX('entry');" /><input type="submit" class="post" value="Post" /><br />
		<?=template::unique_hash()?>
		<?foreach($vars->parse_options_categories as &$category):
			$category = '<option value="'.$category.'" onclick="document.forms[\'entry\'].category.value = this.value;">'.$category.'</option>';
		endforeach?>
		<?=template::parse_options(null, array('Code Parsing'  => array('Parse PHP' => '<input type="checkbox" name="parse_php" value="1" />'),
		'Permissions' => array('Visible'  => '<input type="radio" name="visibility" value="visible" checked="true" />',
							   'Private'  => '<input type="radio" name="visibility" value="private" />',
							   'Hidden'   => '<input type="radio" name="visibility" value="hidden" /></dd>'),
		'Entry Info'  => array('Category' => '<input type="text" name="category" /> <select>'.implode($vars->parse_options_categories).'</select>')))?>
		<div id="ajax"></div>
	</div></form>
</div>
<?}?>
<?
//...
?>
<?function blog_edit(stdclass $vars, stdclass $globals)
{?>
<div class="center">
	<div class="action">Edit Entry</div>
	<form id="edit" action="<?=$globals->_SERVER['REQUEST_URI']?>" method="post" enctype="application/x-www-form-urlencoded"><div>
		Subject:<br /><input type="text" name="subject" class="post" value="<?=$vars->subject?>" /><br /><br />
		Entry:<br /><textarea name="edit" class="post"><?=$vars->entry?></textarea><br />
		<div class="textarea_drag" onmousedown="return textarea_drag.init(document.forms[\'edit\'].edit, event);"></div><br />
		<input type="button" class="post" value="Preview" onclick="AJAX('edit');" /><input type="submit" class="post" value="Save" />
		<?=template::unique_hash()?>
		<?=template::parse_options($vars->parse_options, array('Code Parsing' => array('Parse PHP' => '<input type="checkbox" name="parse_php" value="1" '.(($vars->parse_options_parse_php) ? 'checked="checked"' : null).' />')))?>
		<div id="ajax"></div>
	</div></form>
</div>
<?}?>
<?
//...
?>
<?function blog_permission(stdclass $vars, stdclass $globals)
{?>
<div class="center">
	<div class="action">Entry Permissions</div>
	<form id="entry_permissions" action="<?=$globals->_SERVER['REQUEST_URI']?>" method="post" enctype="application/x-www-form-urlencoded"><div>
		Category: <input type="text" name="category" value="<?=$vars->category?>" />
		<dl class="justify">
			<dt>Visible:</dt><dd><input type="radio" name="permission" value="visible" <?=($vars->permission == 'visible') ? 'checked="true"' : null?> /></dd>
			<dt>Private:</dt><dd><input type="radio" name="permission" value="private" <?=($vars->permission == 'private') ? 'checked="true"' : null?> /></dd>
			<dt>Hidden:</dt><dd><input type="radio" name="permission" value="hidden" <?=($vars->permission == 'hidden') ? 'checked="true"' : null?> /></dd>
			<dt>Archive:</dt><dd><input type="radio" name="permission" value="archive" <?=($vars->permission == 'archive') ? 'checked="true"' : null?> /></dd>
			<dt>Delete:</dt><dd><input type="radio" name="permission" value="delete" /></dd>
		</dl>
		<input type="submit" class="post" value="Update" />
	</div></form>
</div>
<?}?>