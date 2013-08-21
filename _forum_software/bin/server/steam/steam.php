<?php
include_once $_SERVER['DOCUMENT_ROOT'].'/config/lib.php';
include '../resources/Steam.class.php';
include $_SERVER['DOCUMENT_ROOT'].'/bin/server/HLServer.class.php';

class steam_module extends Steam
{
	private
		$steamid,
		$auth = null,
		$profile;
	
	public function __construct($steamID, array $auth)
	{
		$this->steamid = $steamID;
		$this->auth = $this->Authenticate($auth['username'], $auth['password']);
		
		$this->profile = $this->GetProfile($this->steamid, '?xml=1', $this->auth);
	}
	public function create_module()
	{
		$profile = $this->ProfileXMLElement($this->profile);
		
		//all games?
		#$profile->games = $this->ProfileXMLElement($this->GetProfile($this->steamid, '/games?tab=all&xml=1', $this->auth))->games;
		
		return $profile;
	}
	public function isAuth()
	{
		return $this->isAuth;
	}
}

$steam_module = new steam_module($_GET['id'], array('username' => $_GET['username'], 'password' => $_GET['password']));
$vars = (object) array('profile' => $steam_module->create_module());

if($vars->profile->inGameServerIP != null)
{
	$vars->server = array('ip' => (string) $vars->profile->inGameServerIP);
	#$hl_server = new hl_server($vars->server['ip']);
	#$vars->server += val::encode($hl_server->query_server());
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
			background-color: #383838;
			color: #CCCCCC;
		}
		div.server h1
		{
			font-size: 1.5em;
			border-bottom: 1px solid gray;
			margin-bottom: 10px;
			color: #8B8B83;
		}
		div.server div.gameserver h1.gameserver
		{
			font-size: 1.2em;
			margin-bottom: 5px;
		}
		div.server div.game
		{
			padding: 5px 10px 10px;
			margin-bottom: 5px;
			border-bottom: 1px solid #4D4D4D;
			color: #BFBFBF;
		}
		div.server div.steamid
		{
			padding-left: 2.5px;
			color: white;
		}
		div.server div.gameserver
		{
			padding-left: 2.5px;
		}
		div.server div.game a
		{
			color: #BFBFBF;
			text-decoration: none;
		}
	/*]]>*/
</style>

<div class="server">
	<h1>GamePlay Stats <img style="position: absolute; right: 0;" src="./resources/images/steam.gif" /></h1>
	<?if(!$vars->profile->error):
		if($vars->profile->privacyState == 'public' || $steam_module->isAuth()):?>
			<div class="game steamid">
				<img style="float: left;" src="<?=$vars->profile->avatarIcon?>" />
				&nbsp;&nbsp;<?=$vars->profile->steamID?> <span style="font-style: italic; font-size: 0.9em; color: #BFBFBF;">is <?=(substr($vars->profile->stateMessage, 0, 1) != 'L') ? 'Online' : 'Offline'?></span><br />
				&nbsp;&nbsp;&nbsp;&nbsp;<span style="font-size: 0.9em;"><?=$vars->profile->hoursPlayed2Wk?> hrs past 2 weeks</span>
				<br clear="both" />
			</div>
			<?foreach($vars->profile->mostPlayedGames->mostPlayedGame as $game):?>
				<div class="game">
					<img style="float: left;" src="<?=$game->gameIcon?>" />
					&nbsp;&nbsp;<a href="<?=$game->gameLink?>"><?=val::str_trim($game->gameName, 25)?></a><br />
					&nbsp;&nbsp;<span style="font-size: 0.9em;">&nbsp;&nbsp;&nbsp;&nbsp;<?=$game->hoursPlayed?> hrs / <?=$game->hoursOnRecord?> hrs</span>
					<br clear="both" />
				</div>
			<?endforeach?>
			<div class="gameserver">
				<h1 class="gameserver">GameServer</h1>
				<div style="padding-left: 2.5px;">
					<?if($vars->server['ip'] != null):
						print 'Playing on server: '.$vars->server['ip'];
					else:
						print 'Server information not available.';
					endif?>
				</div>
			</div>
		<?else:
			print '<div class="game steamid">
				<img style="float: left;" src="'.$vars->profile->avatarIcon.'" />
				&nbsp;&nbsp;'.$vars->profile->steamID.'<br />
				<br clear="both" />
			</div>
			<div class="game">
				<span style="color: #F26C4F;">This profile is private.</span>
			</div>';
		endif;
	else:
		print '<div class="game"><span style="color: #F26C4F;">'.$vars->profile->error.'</span></div>';
	endif?>
</div>