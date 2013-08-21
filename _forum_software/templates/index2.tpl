<?function index2_main(stdclass $vars, stdclass $globals)
{?>
<div class="title">Index</div>
<div style="width: 90%; margin: 15px auto 0;">
	<div class="box">
		<h1 style="font-size: 1.2em; border-bottom: 1px solid black;">About Jackpf.co.uk</h1>
		<div style="padding-left: 30px; margin-bottom: 20px;">
			<?=$vars->index?>
		</div>
	<ul style="margin-left: 30px; list-style-type: circle;">
		<li style="margin-bottom: 10px;">
			<em style="font-weight: bold; text-decoration: underline;">A few statistics:</em><br />
			<strong><?=$vars->stats['total']?></strong> user<?=($vars->stats['total'] <> 1) ? 's' : null?> online.<br />
			Strangers: <strong><?=$vars->stats['strangers']?></strong><?if(count($vars->stats['bots']) > 0): print ' + <strong>'.count($vars->stats['bots']).'</strong> bot'.((count($vars->stats['bots']) <> 1) ? 's' : null).' ('.implode(', ', $vars->stats['bots']).')'; endif?>,
			Registered Users: <?=(!empty($vars->stats['aliases'])) ? implode(', ', $vars->stats['aliases']) : '<strong>0</strong>'?><?if($vars->stats['aliases_hidden'] > 0): print ' + '.$vars->stats['aliases_hidden'].' hidden'; endif?><br />
			The most users ever online concurrently was <strong><?=$vars->stats['most_online']['count']?></strong>, on <?=$vars->stats['most_online']['date']?><br /><br />
			<?=$vars->post_count['post']?> posts in <?=$vars->post_count['thread']?> threads, in <?=$vars->post_count['forum']?> boards.<br />
		</li>
		<!--<li style="margin-bottom: 10px;">
			<em style="font-weight: bold; text-decoration: underline;">Gameserver Information:</em><br /><br />
			<iframe src="./bin/server/server.php" frameborder="0" style="width: 260px; height: 400px;"></iframe>
			
			<?if($globals->alias_init->userlevel >= $globals->alias_init->user('Administrator')):?>
				<iframe src="./bin/server/RCon.php" frameborder="0" style="width: 100%; height: 125px;"></iframe>
			<?endif?>
			
			<br /><br />
			
			<em style="font-weight: bold; text-decoration: underline;">Voiceserver Information:</em><br />
			<iframe src="./bin/server/vent.php" frameborder="0" style="width: 520px; height: 175px;"></iframe>
		</li>-->
		<li style="margin-bottom: 10px;">
			Until I find a place for them, here are some links:<br />
			<a class="f_legacy" href="?action=misc&amp;status=search_users">Search Users</a> | <a class="f_legacy" href="/?action=misc&amp;status=im">Instant Messenger</a>
		</li>
	</ul>
	</div>
</div>
<?}?>
