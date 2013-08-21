<?
include_once $_SERVER['DOCUMENT_ROOT'].'/config/lib.php';
include $_SERVER['DOCUMENT_ROOT'].'/bin/server/HLServer.class.php';
include $_SERVER['DOCUMENT_ROOT'].'/bin/server/resources/RCon.class.php';
include $_SERVER['DOCUMENT_ROOT'].'/bin/server/resources/Steam.class.php';

$config_init->load($_SERVER['DOCUMENT_ROOT'].'/bin/server/servers.cfg');
$servers = $config_init->parse()->get_object()->servers;

$vars = new stdclass;
$vars->server = array();

foreach($servers as $id => $server)
{
	$vars->server[$id] = array('_address' => $server, 'address' => (preg_match('/[0-9]+\:[0-9]+/', $server)) ? $server : gethostbyname(reset(explode(':', $server))).':'.end(explode(':', $server)), 'password' => $config_init->get_config('gameserver.auth.password'));
	$hl_server = new hl_server($vars->server[$id]['address']);
	$vars->server[$id] += val::encode($hl_server->query_server());
}
?>

<style type="text/css">
	/*<![CDATA[*/
		*
		{
			margin: 0;
			padding: 0;
		}
		div.server
		{
			border: 1px solid black;
			font-family: Arial, Helvetica, sans-serif;
			font-size: 11px;
			padding: 2.5px;
			overflow: hidden;
			font-weight: bold;
			width: 238px;
			height: 375px;
			background-color: #333333;
			color: #CCCCCC;
		}
		div.server a
		{
			color: yellow;
			text-decoration: none;
		}
		div.server div.server_name
		{
			margin: 2px;
			height: 28px;
			padding: 2px;
			border: 1px solid #555555;
			overflow: hidden;
			clear: both;
			width: 228px;
			background-color: #222222;
			color: #FF9900;
		}
		div.server div.block
		{
			clear: both;
		}
		div.server div.players
		{
			margin: 2px;
			padding: 0 3px 3px 3px;
			border: 1px solid #555555;
			overflow: auto;
			height: 115px;
			background-color: #222222;
		}
		div.server div.players table
		{
			width: 100%;
			color: white;
		}
		div.server div.players table th, div.server div.players table td
		{
			width: 33%;
			font-size: 0.7em;
		}
		div.server div.players table td
		{
			color: #CCCCCC;
		}
		div.server a.change_server
		{
			display: block;
			float: left;
			border: 1px solid orange;
			padding: 0 4px;
			margin-right: 4px;
		}
		div.server a.player
		{
			color: white;
		}
	/*]]>*/
</style>

<script type="text/javascript" src="/templates/js/js.js"></script>
<script type="text/javascript">
	/*<![CDATA[*/
		function change_server(id)
		{
			for(var i = 0; i < <?=count($vars->server)?>; i++)
			{
				if(i == id && document.getElementById('gs_' + i).style.display == 'none')
					fade('gs_' + i);
				else if(i != id && document.getElementById('gs_' + i).style.display == 'block')
					document.getElementById('gs_' + i).style.display = 'none';
			}
		}
	/*]]>*/
</script>

<div id="server" class="server">
	<?for($id = 0; $id < count($vars->server); $id++):?>
		<div id="gs_<?=$id?>" style="display: <?=($id == 0) ? 'block' : 'none'?>;">
			<div style="float: left; padding-right: 5px;">
				<?=$vars->server[$id]['gamedesc']?>
			</div>
			<div style="float: left;">
				<img src="./sourcebans/images/games/<?=$vars->server[$id]['gamename']?>.png" alt="<?=$vars->server[$id]['gamedesc']?>" title="<?=$vars->server[$id]['gamedesc']?>" />
			</div>
			<div style="float: left; padding-left: 15px;">
				<img src="./sourcebans/images/<?=$vars->server[$id]['os']?>.png" alt="<?=$vars->server[$id]['os']?>" />
				<?if($vars->server[$id]['secure']):?>
					<img src="./sourcebans/images/shield.png" alt="VAC" />
				<?endif?>
			</div>
			<div style="float: right; text-align: right;">
				<?if(count($vars->server) > 1):
					for($i = 0; $i < count($vars->server); $i++)
						print '<a class="change_server" title="'.$vars->server[$i]['hostname'].'" href="#" onclick="change_server('.$i.');">'.($i + 1).'</a>';
				endif?>
			</div>
			
			<div class="server_name">
				<?=$vars->server[$id]['hostname']?>
			</div>
			
			<div class="block">
				<div style="float: left; font-weight: bold;">Address:</div>
				<div style="float: right; color: yellow;"><img src="./sourcebans/images/<?=(!empty($vars->server[$id]['maxplayers'])) ? 'online' : 'offline'?>.gif" alt="<?=(!empty($vars->server)) ? 'Online' : 'Offline'?>" /> <a href="steam://connect/<?=$vars->server[$id]['address']?>"><?=$vars->server[$id]['address']?></a></div>
			</div>
			<div class="block">
				<div style="float: left; font-weight: bold;">Players:</div>
				<div style="float: right;"><div style="width: 75px; height: 10px; border: 1px solid black; float: left; margin-right: 5px;"><div style="width: <?=$vars->server[$id]['numplayers'] * (100 / $vars->server[$id]['maxplayers'])?>%; height: 10px; background-color: <?$pc_full = $vars->server[$id]['numplayers'] * (100 / $vars->server[$id]['maxplayers']); print ($pc_full < 50) ? 'red' : 'green'?>;"></div></div><?=(int) $vars->server[$id]['numplayers']?> / <?=(int) $vars->server[$id]['maxplayers']?></div>
			</div>
			<div class="block">
				<div style="float: left; font-weight: bold;">Map:</div>
				<div style="float: right; text-align: right; width: 160px;">
					<?=val::str_trim($vars->server[$id]['map'], 25)?>
					<img src="http://image.www.gametracker.com/images/maps/160x120/<?=$vars->server[$id]['_gamename']?>/<?=$vars->server[$id]['map']?>.jpg" alt="<?=$vars->server[$id]['map']?>" />
				</div>
			</div>
			<div class="block">
				<div style="float: left; font-weight: bold;">
					Players Online:
				</div>
			</div>
			<div class="block players">
				<table>
					<tr>
						<th>Name</th>
						<th>Frags</th>
						<th>Time</th>
					</tr>
					<?if(!empty($vars->server[$id]['players'])):
						try
						{
							$rc = new RCon($vars->server[$id]['address'], $vars->server[$id]['password']);
							$status = $rc->rconCommand('status');
							
							preg_match_all('/\#.*?\"(?<name>.*?)\" (?<steamid>.*?) .*?\n/', $status, &$matches); //etc...
							
							$steam = new steam;
							$player_status = array();
							
							foreach($matches['name'] as $player_index => $player_id)
							{
								$player_status[$player_id] = $steam->GetFriendID($matches['steamid'][$player_index]);
							}
						}
						catch(Exception $e)
						{
							print $e->getMessage();
						}
						
						usort(&$vars->server[$id]['players'], create_function('$player1, $player2', 'return ($player1[\'kills\'] < $player2[\'kills\']);')); #function($player1, $player2){return ($player1['kills'] < $player2['kills']);}
						
						foreach($vars->server[$id]['players'] as $index => $player):
							print '<tr>
								<td><a class="player" href="http://'.steam::STEAMURL.'/profiles/'.$player_status[val::decode($player['name'])].'" target="_blank">'.val::encode(val::str_trim(val::decode($player['name']), 10)).'</a></td>
								<td>'.$player['kills'].'</td>
								<td>'.val::str_trim(steam::server_unix($player['time']), 10).'</td>
							</tr>';
						endforeach;
					else:
						print '<tr><td>No players</td></tr>';
					endif?>
				</table>
			</div>
			<div style="bottom: 20px; right: 15px; position: absolute;">
				<a href="#" onclick="window.location.reload(true);"><img style="border: 0;" src="./sourcebans/themes/default/images/refresh.png" /></a>
			</div>
		</div>
	<?endfor?>
</div>