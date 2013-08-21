<?function register_main(stdclass $vars, stdclass $globals)
{?>
<div class="title">Register</div>

<?=$vars->register_main?>
<?}?>
<?
//...
?>
<?function register_index(stdclass $vars, stdclass $globals)
{?>
<script type="text/javascript">
	/*<![CDATA[*/
		var register_password = {
			security: function()
			{
				var input = document.forms['register'].password;
				var password = input.value;
				var security = 0;
				
				//char check
				if(password.match(/[a-z]/))
				{
					security += 1;
				}
				//upper case char check
				if(password.match(/[A-Z]/))
				{
					security += 1;
				}
				//number check
				if(password.match(/[0-9]/))
				{
					security += 1;
				}
				//non word char check
				if(password.match(/[^0-9A-Za-z]/))
				{
					security += 1;
				}
				
				if(password.length == 0)
				{
					input.style.background = 'white';
				}
				else
				{
					if(security <= 1)
					{
						input.style.background = '#FF6A6A';
					}
					else if(security > 1 && security <= 2)
					{
						input.style.background = '#EEEE00';
					}
					else if(security > 2)//else if(security > 2 && security <= 3)
					{
						input.style.background = '#90EE90';
					}
				}
			},
			generate: function()
			{
				var len = Math.floor(Math.random() * 5 + 5);
				var password = '';
				
				for(var i = 0; i <= len; i++)
				{
					var rand = Math.round(Math.random() * 3);
					//random int
					if(rand == 0)
					{
						var int = Math.floor(Math.random() * 10);
						var crypt = int;
					}
					//random upper case
					else if(rand == 1)
					{
						var int = Math.floor(Math.random() * 26 + 65);
						var crypt = String.fromCharCode(int);
					}
					//random lower case
					else
					{
						var int = Math.floor(Math.random() * 26 + 97);
						var crypt = String.fromCharCode(int);
					}
					//generate password
					password += crypt;
				}
				
				alert('Generated password : '+password);
				document.forms['register'].password.value = password;
				//check password security
				this.security();
			}
		}
		function enable()
		{
			var enable = document.getElementById('submit');
			enable.disabled = (enable.disabled === true) ? false : true;
		}
		function register(e)
		{
			var register = new Array();
			
			for(var i = 0; i < e.length; i++)
			{
				if(((e.elements[i].type == 'text' || e.elements[i].type == 'password') && e.elements[i].value == '') || e.elements[i].type == 'checkbox' && e.elements[i].checked == false)
				{
					register[register.length] = e.elements[i].name;
				}
			}
			
			if(register.length > 0)
			{
				try
				{
					document.getElementById('register_failure').parentNode.removeChild(document.getElementById('register_failure'));
				}
				catch(err){}
				
				var element = document.createElement('div');
				
				with(element)
				{
					id = 'register_failure';
					className = 'box';
					
					innerHTML = '<span style="color: red;">Register failed: </span>';
					for(var i = 0; i < register.length; i++)
					{
						innerHTML += register[i]+' was not completed'+((i < register.length - 1) ? ', ' : '.');
					}
					
					style.width = '250px';
					style.border = '1px solid red';
					style.display = 'none';
				}
				document.body.getElementsByClassName('main')[0].appendChild(element);
				
				fade('register_failure');
				
				return false;
			}
			else
			{
				return true;
			}
		}
	/*]]>*/
</script>

<div id="terms" class="box" style="position: absolute; width: 100%; left: 0; height: 350px; display: none; text-align: center; background-color: black; color: white;">
	<div style="position: absolute; top: 0; left: 0;">
		<a style="color: white;" href="#terms" onclick="window.print();">Print</a>
	</div>
	<div style="position: absolute; top: 0; right: 0;">
		<a style="color: red;" href="#terms" onclick="fade('terms');">Close</a>
	</div>
	<h1>Terms</h1>
	<?=$vars->terms?>
	<div style="position: absolute; left: 0; bottom: 0; width: 100%;">
		<?=$vars->terms_footer?>
	</div>
	<div style="position: absolute; bottom: 0; right: 0; cursor: move;" onmousedown="return drag.init(this.parentNode, event);">//</div>
</div>

<form id="register" action="<?=$globals->_SERVER['REQUEST_URI']?>" method="post" enctype="application/x-www-form-urlencoded" onsubmit="return register(this);"><div>
	Name:<br /><input type="text" name="name" maxlength="<?=$vars->register_data['clm_Name']?>" />
	<br /><br /><br />
	
	Email Address:<br /><input type="text" name="email" maxlength="<?=$vars->register_data['clm_Email']?>" />
	<br />
	<a style="font-size: 0.8em;" href="#ajax_email" onclick="AJAX('register', 'ajax_email', '/main/misc.php?status=ajax&validate=email', false);">Validate</a> <span id="ajax_email"></span>
	<br /><br />
	
	Alias:<br /><input type="text" name="alias" maxlength="<?=$vars->register_data['clm_Alias']?>" />
	<br />
	<a style="font-size: 0.8em;" href="#ajax_alias" onclick="AJAX('register', 'ajax_alias', '/main/misc.php?status=ajax&validate=alias', false);">Validate</a> <span id="ajax_alias"></span>
	<br /><br />
	
	Password:<br /><input type="password" name="password" maxlength="<?=$vars->register_data['clm_Password']?>" onkeyup="register_password.security();" />
	<br />
	<a style="font-size: 0.8em;" href="#" onclick="register_password.generate();">Generate</a>
	<br /><br />
	
	<div id="captcha"><?=$vars->captcha?></div>
	Captcha: <input type="text" name="captcha" /> (<a style="font-size: 0.8em;" href="#captcha" onclick="AJAX(null, document.getElementById('captcha'), '<?=$vars->captcha_filename?>?captcha=1&state=1', false); document.forms['register'].captcha.value = '';">Refresh</a>)
	<br /><br />
	
	<dl class="justify">
		<dt>Agree To <a style="color: purple" href="#terms" onclick="fade('terms');">Terms</a></dt><dd><input type="checkbox" name="terms" value="1" onclick="enable();" /></dd>
		<br />
	</dl><br />
	
	<input type="submit" id="submit" class="post" value="Register" disabled="true" />
</div></form>
<?}?>
<?
//...
?>
<?function register_success(stdclass $vars, stdclass $globals)
{?>
<span style="color: #00AA00;">Registration successful.</span><br />
<a class="control" href="http://<?=$vars->email?>" target="_blank">Check your email</a>
<?}?>
<?
//...
?>
<?function register_authenticate(stdclass $vars, stdclass $globals)
{?>
This profile is now authentic. <br />
<a class="control" href="<?=$globals->_SERVER['PHP_SELF']?>?action=login">Login</a>
<?}?>
<?
//...
?>
<?function register_help_details(stdclass $vars, stdclass $globals)
{?>
<?if(!isset($vars->email)):?>
	<form id="email" action="<?=$globals->_SERVER['REQUEST_URI']?>" method="post" enctype="application/x-www-form-urlencoded"><div>
		Email Address<br />
		<input type="text" style="width: 150px;" name="email" /><br />
		<a class="control" href="#" onclick="document.forms['email'].submit();">Send</a>
	</div></form>
<?else:?>
	Email sent.<br />
	<a class="control" href="http://<?=$vars->email?>" target="_blank">Check your email</a>
<?endif?>
<?}?>
<?
//...
?>
<?function register_help_authentication(stdclass $vars, stdclass $globals)
{?>
<?if(!isset($vars->email)):?>
	<form id="email" action="<?=$globals->_SERVER['REQUEST_URI']?>" method="post" enctype="application/x-www-form-urlencoded"><div>
		Email Address<br />
		<input type="text" style="width: 150px;" name="email" /><br />
		<a class="control" href="#" onclick="document.forms['email'].submit();">Send</a>
	</div></form>
<?else:?>
	Email sent.<br />
	<a class="control" href="http://<?=$vars->email?>" target="_blank">Check your email</a>
<?endif?>
<?}?>
<?
//...
?>
<?function register_unregister(stdclass $vars, stdclass $globals)
{?>
You are about to disassociate yourself with this site.<br />
Hence, all of your messages will be deleted, and forum posts negated.<br />
<a class="control" href="#" onclick="form_submit('<?=$globals->_SERVER['REQUEST_URI']?>');">Continue</a>, <a class="control" href="<?=$globals->_SERVER['HTTP_REFERER']?>">Back</a>.
<?}?>