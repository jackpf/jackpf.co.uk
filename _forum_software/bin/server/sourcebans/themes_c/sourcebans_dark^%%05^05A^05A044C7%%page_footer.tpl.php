<?php /* Smarty version 2.6.20, created on 2010-08-18 16:15:35
         compiled from page_footer.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'debug', 'page_footer.tpl', 17, false),)), $this); ?>
	</div></div>
	<div id="footer">
		<div id="gc">
		By <a href="http://www.interwavestudios.com" target="_blank" class="footer_link">InterWave Studios</a>		</div>
		<div id="sb"><br/>
		<a href="http://www.sourcebans.net" target="_blank"><img src="images/sb.png" alt="SourceBans" border="0" /></a><br/>
		<div id="footqversion">Version <?php echo $this->_tpl_vars['SB_VERSION']; ?>
 <?php echo $this->_tpl_vars['SB_REV']; ?>
</div>
		<div id="footquote"><?php echo $this->_tpl_vars['SB_QUOTE']; ?>
</div>
		
		
		</div>
		<div id="sm">
		Powered by <a class="footer_link" href="http://www.sourcemod.net" target="_blank">SourceMod</a>
		</div>
	</div>
	<?php if ($this->_tpl_vars['debugmode']): ?>
		<?php echo smarty_function_debug(array(), $this);?>

	<?php endif; ?>
	