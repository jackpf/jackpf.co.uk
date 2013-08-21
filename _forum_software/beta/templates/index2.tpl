<?function index2_main(stdclass $vars, stdclass $globals)
{?>
<div class="title">Index</div>
<div style="width: 90%; margin: 15px auto 0;">
	<div class="box" style="float: left; width: 300px; height: 300px;">
		<span style="font-weight: bold; text-decoration: underline;">A few statistics:</span><br />
		<ul style="margin-left: 30px; list-style-type: circle;">
			<li><strong><?=$vars->stats['total']?></strong> total users, <strong><?=$vars->stats['online']?></strong> user<?=($vars->stats['total'] <> 1) ? 's' : null?> online.<br />
			Strangers: <strong><?=$vars->stats['strangers']?></strong><?if(count($vars->stats['bots']) > 0): print ' + <strong>'.count($vars->stats['bots']).'</strong> bot'.((count($vars->stats['bots']) <> 1) ? 's' : null).' ('.implode(', ', $vars->stats['bots']).')'; endif?>,
			Registered Users: <?=(!empty($vars->stats['aliases'])) ? implode(', ', $vars->stats['aliases']) : '<strong>0</strong>'?><?if($vars->stats['aliases_hidden'] > 0): print ' + '.$vars->stats['aliases_hidden'].' hidden'; endif?><br /></li>
			<li>The most users ever online concurrently was <strong><?=$vars->stats['most_online']['count']?></strong>, on <?=$vars->stats['most_online']['date']?></li>
			<li><?=$vars->post_count['post']?> posts in <?=$vars->post_count['thread']?> threads, in <?=$vars->post_count['forum']?> boards.<br />
		</ul>
	</div>
	<div style="float: left; text-align: left; padding-left: 30px; width: 500px;">
		<strong><em>About Jackpf.co.uk:</em></strong><br /><br />
		
		<div class="box" style="border: 1px solid red; color: red;">
			<strong>NOTE:</strong> I am no longer coding the forum any more. A lot of data was lost due to moving hosts a few times, and I've decided to start coding something new, since a lot of the code here is outdated, and I'd like to start afresh!<br />
			There's still a board open, and you can still register and stuff. Most of the features here should still work :)<br />
			Thanks all. It was a fun 3 years.
		</div>
		<br />
		
		This is Jack's site!!!11one<br />
		Undergoing a bit of reconstruction atm, bare with me.<br /><br />

		Originally developed in 2008, this began as a small project to learn how to code.<br />
		I got quite interested in web development, so registered a domain name and released the site publicly on December 15th 2008.<br /><br />

		A bunch of friends and myself (and a few spammers every now and again) mainly used the site for teh lulz, but I took bug fixing and feature requests very seriously.<br /><br />

		It may be a bit useless (and now slightly dead), but I still update it with features every now and again, and post some stuff here and there.<br /><br />

		Feel free to join!
	</div>
</div>
<?}?>
