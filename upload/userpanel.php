<?php
	$s = new server;
	$mysql = mysql_connect(server, admin, pass) or trigger_error(mysql_error(), E_USER_ERROR);
	mysql_select_db(db) or trigger_error(mysql_error(), E_USER_ERROR);
	$status = (isset($_GET['status'])) ? $_GET['status'] : null;
	
	if(isset($_GET['dir']))
	{
		$s->dir .= $_GET['dir'];
	}
	
	if($status == null || $status == 'index' || $status == 'userpanel')
	{
		$dir = opendir($s->dir) or trigger_error('Error reading directory.', E_USER_ERROR);
		echo '<table><tr><td style="font-size: 20px;">Files: ';
		foreach(explode('/', $s->dir) as $key => $value)
		{
			if($key != 0 && $key != 1)
			{
				echo '<a href="'.$_SERVER['PHP_SELF'].'?action=userpanel&amp;dir=/'.$value.'">'.$value.'</a>/';
			}
			else if($key == 1)
			{
				echo '<a href="'.$_SERVER['PHP_SELF'].'?action=userpanel">'.$value.'</a>/';
			}
		}
		echo '</td></tr>';
		while($file = readdir($dir))
		{
			if(reset(explode('.', $file)) != null)
			{
				if($s->dir($file))
				{
					echo '<tr>
						<td>
							<a href="'.$_SERVER['PHP_SELF'].'?action=userpanel&amp;dir='.$_GET['dir'].'/'.$file.'">'.$file.'</a>
						</td>
						<td></td>';
						if(isset($_SESSION['Alias']))
						{
							echo '<td>
								&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
								<a href="'.$_SERVER['PHP_SELF'].'?action=userpanel&amp;status=delete&amp;file='.$_GET['dir'].'/'.urlencode($file).'">
									<img src="./css/img/delete.gif" alt="Delete" title="Delete" />
								</a>
								<a href="'.$_SERVER['PHP_SELF'].'?action=userpanel&amp;status=rename&amp;file='.$_GET['dir'].'/'.urlencode($file).'">
									<img src="./css/img/rename.gif" alt="Rename" title="Rename" />
								</a>
							</td>';
						}
						echo '<td>
							Directory
						</td>
					</tr>';
				}
				else
				{
					echo '<tr>
						<td class="file">
							<a href="./file.php?file='.str_ireplace('files/', null, $s->dir).'/'.urlencode($file).'">'.$s->trim($file, 30).'</a>
						</td>
						<td>
							<a href="javascript: file(\''.urlencode(str_ireplace('files/', null, $s->dir)).'/'.$file.'\');">URL</a>
						</td>';
						if(isset($_SESSION['Alias']))
						{
							echo '<td>
								&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
								<a href="./download.php?file='.str_ireplace('files/', null, $s->dir).'/'.urlencode($file).'">
									<img src="./css/img/download.gif" alt="Download" title="Download" />
								</a>
								<a href="'.$_SERVER['PHP_SELF'].'?action=userpanel&amp;status=delete&amp;file='.$_GET['dir'].'/'.urlencode($file).'">
									<img src="./css/img/delete.gif" alt="Delete" title="Delete" />
								</a>
								<a href="'.$_SERVER['PHP_SELF'].'?action=userpanel&amp;status=rename&amp;file='.$_GET['dir'].'/'.urlencode($file).'">
									<img src="./css/img/rename.gif" alt="Rename" title="Rename" />
								</a>
							</td>';
						}
						echo '<td>
							'.strtoupper(end(explode('.', $file))).' file
						</td>
					</tr>';
				}
			}
		}
		echo '</table>';
	}
	else if($status == 'rename')
	{
		$s->secure();
		
		$file = urldecode($_GET['file']);
		if(!$s->dir($file))
		{
			$pre = explode('.', end(explode('/', $file)));
			$ext = array_pop($pre);
			$pre = implode('.', $pre);
		}
		else
		{
			$pre = $file;
		}
		
		if(!isset($_POST['name']))
		{
			echo '<form action="'.$_SERVER['REQUEST_URI'].'" method="post"><div>
				Rename:<br />
				<input type="text" name="name" value="'.$pre.'" />';
				if(!$s->dir($file))
				{
					echo '.'.$ext;
				}
				echo '<br />
				<input type="submit" />
			</div></form>';
		}
		else if(isset($_POST['name']))
		{
			$nfile = $s->fname_check($_POST['name']);
			if(!$s->dir($file))
			{
				$nfile .= '.'.$ext;
			}
			if($nfile == null)
			{
				header('Location: '.$_SERVER['PHP_SELF'].'?action=userpanel');
				die();
			}
			$nfile2 = explode('/', $file);
			array_pop($nfile2);
			$nfile = implode($nfile2).'/'.$nfile;
			rename($s->dir.'/'.$file, $s->dir.'/'.$nfile) or trigger_error('Error renaming file.', E_USER_ERROR);
			header('Location: '.$_SERVER['PHP_SELF'].'?action=userpanel');
		}
	}
	else if($status == 'delete')
	{
		$s->secure();
		$file = urldecode($_GET['file']);
		
		if($s->dir($file))
		{
			$fh = opendir($s->dir) or trigger_error('Error deleting directory.', E_USER_ERROR);
			while($file = readdir($fh))
			{
				if($file != '.' && $file != '..')
				{
					if(!$s->dir($s->dir.'/'.$file))
					{
						unlink($s->dir.'/'.$file) or trigger_error('Error deleting directory.', E_USER_ERROR);
					}
					else if($s->dir($s->dir.'/'.$file))
					{
						rmdir($s->dir.'/'.$file) or trigger_error('Error deleting directory.', E_USER_ERROR);
					}
				}
			}
			closedir($fh) or trigger_error('Error deleting directory.', E_USER_ERROR);
			rmdir($s->dir) or trigger_error('Error deleting directory.', E_USER_ERROR);
		}
		else
		{
			unlink($s->dir.'/'.$file);
		}
		header('Location: '.$_SERVER['HTTP_REFERER']);
	}
	else if($status == 'login')
	{
		$_SERVER['REQUEST_URI'] = explode('&login=', $_SERVER['REQUEST_URI']);
		$_SERVER['REQUEST_URI'] = $_SERVER['REQUEST_URI'][0];
		if(!isset($_SESSION['Alias']) && !isset($_POST['Alias']) && !isset($_POST['password']))
		{
			echo '<form action="'.$_SERVER['REQUEST_URI'].'&amp;login=1" method="post"><div>
				<input type="text" name="alias" maxlength="15" /><br />
				<input type="password" name="password" maxlength="30" /><br />
				<input type="submit" />
			</div></form><br />';
			if(isset($_GET['login']) && $_GET['login'] == 0)
			{
				echo '<span style="color: red;">Bad credentials</span><br />';
			}
			echo '<a href="'.$_SERVER['PHP_SELF'].'?action=userpanel&amp;status=register">Register</a>';
		}
		else
		{
			if($_GET['login'] == 1)
			{
				$alias = mysql_real_escape_string($_POST['alias']);
				$password = mysql_real_escape_string($_POST['password']);
				
				$sql = mysql_query('SELECT * FROM `'.tb.'` WHERE `Alias`=\''.$alias.'\' AND `Password`=\''.$password.'\'') or trigger_error(mysql_error(), E_USER_ERROR);
				if(mysql_num_rows($sql) == 1)
				{
					$_SESSION['Alias'] = $_POST['alias'];
					header('Location: '.$_SERVER['REQUEST_URI'].'&login=2');
				}
				else
				{
					session_unset('Alias');
					session_destroy();
					setcookie(session_name(), null, -1000, '/', false);
					header('Location: '.$_SERVER['REQUEST_URI'].'&login=0');
				}
			}
			if(!isset($_SESSION['Alias']))
			{
				header('Location: '.$_SERVER['REQUEST_URI'].'&login=0');
			}
			echo 'Login successful<br /><a href="'.$_SERVER['PHP_SELF'].'?action=userpanel">Userpanel</a>';
		}
	}
	else if($status == 'logout')
	{
		session_unset('Alias');
		session_destroy();
		setcookie(session_name(), null, -1000, '/', $_SERVER['SERVER_NAME']);
		header('Location: '.$_SERVER['HTTP_REFERER']);
	}
	else if($status == 'register')
	{
		if(!isset($_POST['alias']) && !isset($_POST['password']))
		{
			echo '<form action="'.$_SERVER['REQUEST_URI'].'" method="post"><div>
				<input type="text" name="alias" maxlength="15" /><br />
				<input type="password" name="password" maxlength="30" /><br />
				<input type="submit" />
			</div></form>';
		}
		else if(isset($_POST['alias']) || isset($_POST['password']))
		{
			$alias = mysql_real_escape_string($s->aval($_POST['alias']));
			$password = mysql_real_escape_string($_POST['password']);
			
			$sql = mysql_query('SELECT * FROM `'.tb.'` WHERE `Alias`=\''.$alias.'\'') or trigger_error(mysql_error(), E_USER_ERROR);
			if(mysql_num_rows($sql) > 0)
			{
				die('This alias is unavailable.');
			}
			
			mysql_query('INSERT INTO '.tb.' (`ID`, `Alias`, `Password`) VALUES (null, \''.$alias.'\', \''.$password.'\')') or trigger_error(mysql_error(), E_USER_ERROR);
			mkdir('files/'.$alias) or trigger_error('Error creating directory.', E_USER_ERROR);
			$fh = fopen('files/'.$alias.'/.htaccess', 'w') or trigger_error('Error creating directory.', E_USER_ERROR);
			fputs($fh, "order deny,allow\ndeny from all");
			echo 'Alias created<br /><a href="'.$_SERVER['PHP_SELF'].'?action=userpanel&amp;status=login">Login</a>';
		}
	}
	mysql_close($mysql);
?>