<?php /* Smarty version 2.6.20, created on 2010-08-16 12:11:06
         compiled from item_admin_tabs.tpl */ ?>
<div id="admin-page-menu">
	<ul>
	<?php $_from = $this->_tpl_vars['tabs']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['tab']):
?>
		<?php echo $this->_tpl_vars['tab']['tab']; ?>

	<?php endforeach; endif; unset($_from); ?>
	</ul>
<br />
<div align="center">
	<?php echo $this->_tpl_vars['pane_image']; ?>

</div>