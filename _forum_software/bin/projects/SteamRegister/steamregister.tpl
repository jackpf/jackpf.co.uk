<?function steamregister_main(stdclass $vars, stdclass $globals)
{?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
	<head>
		<title>SteamRegister</title>
		<link rel="icon" type="image/x-icon" href="http://steamcommunity.com/favicon.ico" />
		<link rel="stylesheet" type="text/css" href="http://steamcommunity.com/public/css/skin_1/global.css?v=649315749" />
		<link rel="stylesheet" type="text/css" href="http://steamcommunity.com/public/css/skin_1/profile.css?v=3701321926" />
		<link rel="stylesheet" type="text/css" href="http://steamcommunity.com/public/css/skin_1/header.css?v=2256890707" />
		<?if(stristr($globals->_SERVER['HTTP_USER_AGENT'], 'msie'))
			include $globals->_SERVER['DOCUMENT_ROOT'].'/templates/css/css-conditional.css';?>
	</head>
	<body><center>
		<div id="global_header">
			<div class="content">
				<div class="logo">
					<span id="logo_holder">
						<a href="http://store.steampowered.com/">
							<img src="http://steamcommunity.com/public/images/header/globalheader_logo.png" width="176" height="44" border="0" />
						</a>
					</span>
				</div>
				
				<span class="menuitem">STEAMREGISTER</span>
			</div>
		</div>
		
		<div id="mainBody">
<!-- main contents -->
	<div id="mainContents" class="clearfix" style="height: 800px;">
hi
</div>
<div id="footer">
	<div id="footerText">

		This site is in no way affiliated with the VALVe corporation. All data is property of its respective owner(s).</div>
</div>

	</center></body>
</html>
<?}?>