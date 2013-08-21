<?php /* Smarty version 2.6.20, created on 2010-08-18 19:54:14
         compiled from page_login.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'sb_button', 'page_login.tpl', 20, false),)), $this); ?>
<div id="login"> 
	<div id="login-content">
	  	<div id="loginUsernameDiv">
	    	<label for="loginUsername">Username:</label><br />
	    	<input id="loginUsername" class="loginmedium" type="text" name="username"value="" />
		</div>
		<div id="loginUsername.msg" class="badentry"></div>
  		
		<div id="loginPasswordDiv">
	    	<label for="loginPassword">Password:</label><br />
	   		<input id="loginPassword" class="loginmedium" type="password" name="password" value="" />
		</div>
		<div id="loginPassword.msg" class="badentry"></div>
	  	
		<div id="loginRememberMeDiv">
	    	<input id="loginRememberMe" type="checkbox" class="checkbox" name="remember" value="checked" vspace="5px" />    <span class="checkbox" style="cursor:pointer;" onclick="($('loginRememberMe').checked?$('loginRememberMe').checked=false:$('loginRememberMe').checked=true)">Remember me</span>
  		</div>
		
  		<div id="loginSubmit">	
			<?php echo smarty_function_sb_button(array('text' => 'Login','onclick' => $this->_tpl_vars['redir'],'class' => 'ok','id' => 'alogin','submit' => false), $this);?>

		</div>
		
		<div id="loginOtherlinks">
			<a href="?">Back to the Homepage</a> - <a href="index.php?p=lostpassword">Lost your password?</a>
		</div>
	</div>
</div>
	
<script>
	$E('html').onkeydown = function(event){
	    var event = new Event(event);
	    if (event.key == 'enter' ) <?php echo $this->_tpl_vars['redir']; ?>

	};$('loginRememberMeDiv').onkeydown = function(event){
	    var event = new Event(event);
	    if (event.key == 'space' ) $('loginRememberMeDiv').checked = true;
	};$('button').onkeydown = function(event){
	    var event = new Event(event);
	    if (event.key == 'space' ) <?php echo $this->_tpl_vars['redir']; ?>

	};
</script>