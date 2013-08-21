<?php
	include('conf/config.php');
	session_name('jackpf_alt_server');
	session_start();
	ob_start();
	header('Content-Type: text/html; Charset = UTF-8');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
	<head>
		<title>Jack's Website - Alternate Server</title>
		<link rel="stylesheet" type="text/css" href="./css/css.css" />
		<script type="text/javascript" src="./js/js.js"></script>
	</head>
	<body>
		<div class="main">
			<div class="login">
				<?php
					if(isset($_SESSION['Alias']))
					{
						echo $_SESSION['Alias'].' -> <a href="'.$_SERVER['PHP_SELF'].'?action=userpanel">Userpanel</a> | <a href="'.$_SERVER['PHP_SELF'].'?action=userpanel&amp;status=logout">Logout</a>';
					}
					else
					{
						echo 'Stranger -> <a href="'.$_SERVER['PHP_SELF'].'?action=userpanel&amp;status=login">Login</a>';
					}
				?>
			</div>
			<div class="jackpf">
				<a href="http://www.jackpf.co.uk">Jackpf.co.uk &raquo;</a><br />
				<a href="<?php echo $_SERVER['PHP_SELF']; ?>?action=upload">Upload &raquo;</a>
			</div>
			<h1>Jackpf.co.uk Alt Server</h1>
			<?php
				$action = (isset($_GET['action'])) ? $_GET['action'] : null;
				switch($action)
				{
					case null: case 'index': case 'userpanel':
						include('userpanel.php');
					break;
					case 'upload':
						include('upload.php');
					break;
				}
			?>
		</div>
	</body>
</html>
<?php
	ob_end_flush();
?>