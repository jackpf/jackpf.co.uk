<?php /* Smarty version 2.6.20, created on 2010-08-18 12:48:53
         compiled from page_protestban.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'sb_button', 'page_protestban.tpl', 60, false),)), $this); ?>
<div id="submit-introduction">
<h3>Protest a ban</h3>
Before you proceed make sure you first check our banlist and search it by clicking <a href="index.php?p=banlist">here</a> if you are listed and for what reason.

If you do find yourself listed on the banlist and find the reason for this to be untrue you can write a protest. 
</div>
<div id="submit-main">
<form action="index.php?p=protest" method="post">
<input type="hidden" name="subprotest" value="1">
<table cellspacing='10' width='100%' align='center'>
<tr>
	<td colspan="3">
		Your Details:	</td>
</tr>
<tr>
	<td width="20%">Ban Type:</td>
	<td>
		<select id="Type" name="Type" class="submit-fields" onChange="changeType(this[this.selectedIndex].value);">
			<option value="0">Steam ID</option>
			<option value="1">IP Address</option>
		</select>
	</td>
</tr>
<tr id="steam.row">
	<td width="20%">
		Your SteamID<span class="mandatory">*</span>:</td>
	<td>
		<input type="text" name="SteamID" size="40" maxlength="64" value="<?php echo $this->_tpl_vars['steam_id']; ?>
" class="submit-fields" />
	</td>
		<td rowspan="7" align="center" valign="top" width="200px"><img src="images/protest-ban.jpg" alt="Protest Ban" /></td>
</tr>
<tr id="ip.row" style="display: none;">
	<td width="20%">
		Your IP<span class="mandatory">*</span>:</td>
	<td>
		<input type="text" name="IP" size="40" maxlength="64" value="<?php echo $this->_tpl_vars['ip']; ?>
" class="submit-fields" />
		<td rowspan="7" align="center" valign="top" width="200px"><img src="images/protest-ban.jpg" alt="Protest Ban" /></td>
	</td>
</tr>
<tr>
	<td width="20%">
        Name<span class="mandatory">*</span>:</td>
	<td>
        <input type="text" size="40" maxlength="70" name="PlayerName" value="<?php echo $this->_tpl_vars['player_name']; ?>
" class="submit-fields" /></td>
    </tr>
<tr>
	<td width="20%" valign="top">
		Reason why you should be unbanned <span class="mandatory">*</span>: (Be as descriptive as possible) </td>
	<td><textarea name="BanReason" cols="30" rows="5" class="submit-fields"><?php echo $this->_tpl_vars['reason']; ?>
</textarea></td>
    </tr>
<tr>
	<td width="20%">
		Your Email<span class="mandatory">*</span>:	</td>
	<td>
		<input type="text" size="40" maxlength="70" name="EmailAddr" value="<?php echo $this->_tpl_vars['player_email']; ?>
" class="submit-fields" />	</td>
    </tr>
<tr>
	<td width="20%"><span class="mandatory">*</span> = Mandatory Field</td>
	<td>
		<?php echo smarty_function_sb_button(array('text' => 'Submit','class' => 'ok','id' => 'alogin','submit' => true), $this);?>

	</td>
    <td>&nbsp;</td>
</tr>
</table>
</form>
<b>What happens after you posted your protest?</b><br />
  The admins will get notified of your protest. They will then review if the ban is conclusive. After reviewing you will get a reply, which usally means within 24 hours.<br /><br />
  <b>Note:</b> Sending emails with threats to our admins, scolding or shouting will not get you unbanned and in fact we will delete your protest right away! 
</div>