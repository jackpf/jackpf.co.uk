<?php /* Smarty version 2.6.20, created on 2010-08-18 16:15:41
         compiled from page_dashboard.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'escape', 'page_dashboard.tpl', 47, false),)), $this); ?>
<div class="front-module-intro">
	<table width="100%" cellpadding="1">
		<tr>
			<td colspan="3">
				<h3><?php echo $this->_tpl_vars['dashboard_title']; ?>
</h3>		
			</td>
		</tr>
		<tr>
			<td>
				<?php echo $this->_tpl_vars['dashboard_text']; ?>

			</td>
		</tr>
	</table>
</div>


<div id="front-servers">
	<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => 'page_servers.tpl', 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
</div>


<div class="front-module" style="float:right">
	<table width="100%" cellpadding="1" class="listtable">
		<tr>
			<td colspan="3">
				<table width="100%" cellpadding="0" cellspacing="0" class="front-module-header">
					<tr>
						<td align="left">
							Latest Players Blocked
						</td>
						<td align="right">
							Total Stopped: <?php echo $this->_tpl_vars['total_blocked']; ?>

						</td>
					</tr>
				</table>
			</td>
		</tr>				
		<tr>
			<td width="16px" height="16" class="listtable_top">&nbsp;</td>
			<td height="25%" class="listtable_top" align="center"><b>Date/Time</b></td>
			<td height="16" class="listtable_top"><b>Name</b></td>	  
		</tr>
		<?php $_from = $this->_tpl_vars['players_blocked']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['player']):
?>
		<tr<?php if ($this->_tpl_vars['dashboard_lognopopup']): ?> onclick="<?php echo $this->_tpl_vars['player']['link_url']; ?>
"<?php else: ?> onclick="<?php echo $this->_tpl_vars['player']['popup']; ?>
"<?php endif; ?> onmouseout="this.className='tbl_out'" onmouseover="this.className='tbl_hover'" style="cursor: pointer;" id="<?php echo $this->_tpl_vars['player']['server']; ?>
" title="Querying Server Data...">
      <td width="16" height="16" align="center" class="listtable_1"><img src="images/forbidden.gif" width="16" height="16" alt="Blocked Player" /></td>
      <td width="25%" height="16" class="listtable_1"><?php echo $this->_tpl_vars['player']['date']; ?>
</td>
      <td height="16" class="listtable_1"><?php echo ((is_array($_tmp=$this->_tpl_vars['player']['short_name'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
</td>
		</tr>
		<?php endforeach; endif; unset($_from); ?>
	</table>
</div>


<div class="front-module" style="float:left">
	<table width="100%" cellpadding="1" class="listtable">
		<tr>
			<td colspan="4">
				<table width="100%" cellpadding="0" cellspacing="0" class="front-module-header">
					<tr>
						<td align="left">
							Latest Added Bans
						</td>
						<td align="right">
							Total bans: <?php echo $this->_tpl_vars['total_bans']; ?>

						</td>
					</tr>
				</table>
			</td>
		</tr>
		<tr height="16">
			<td width="16" class="listtable_top">MOD</td>
			<td width="24%" class="listtable_top" align="center"><strong>Date/Time</strong></td>
			<td class="listtable_top" align="center"><strong>Name</strong></td>
			<td width="23%" class="listtable_top"><strong>Length</strong></td>
		</tr>
		<?php $_from = $this->_tpl_vars['players_banned']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['player']):
?>
		<tr onclick="<?php echo $this->_tpl_vars['player']['link_url']; ?>
" onmouseout="this.className='tbl_out'" onmouseover="this.className='tbl_hover'" style="cursor:pointer;" height="16">
      <td class="listtable_1" align="center"><img src="images/games/<?php echo $this->_tpl_vars['player']['icon']; ?>
" width="16" alt="MOD" title="MOD" /></td>
      <td class="listtable_1"><?php echo $this->_tpl_vars['player']['created']; ?>
</td>
      <td class="listtable_1">
        <?php if (empty ( $this->_tpl_vars['player']['short_name'] )): ?>
          <i><font color="#677882">no nickname present</font></i>
        <?php else: ?>
          <?php echo ((is_array($_tmp=$this->_tpl_vars['player']['short_name'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>

        <?php endif; ?>
      </td>
      <td class="listtable_1<?php if ($this->_tpl_vars['player']['unbanned']): ?>_unbanned<?php endif; ?>"><?php echo $this->_tpl_vars['player']['length']; ?>
<?php if ($this->_tpl_vars['player']['unbanned']): ?> (<?php echo $this->_tpl_vars['player']['ub_reason']; ?>
)<?php endif; ?></td>
		</tr>
		<?php endforeach; endif; unset($_from); ?>
	</table>
</div>