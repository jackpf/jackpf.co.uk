<?php /* Smarty version 2.6.20, created on 2010-08-18 16:15:42
         compiled from page_messages.tpl */ ?>
<h3 align="left">Messages Left</i></h3>
<?php $_from = $this->_tpl_vars['messages']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['msg']):
?>
	<span title="<?php echo $this->_tpl_vars['msg']['Client_ID']; ?>
"><?php echo $this->_tpl_vars['msg']['Client_Name']; ?>
</span>: <?php echo $this->_tpl_vars['msg']['Message']; ?>
<br />
<?php endforeach; endif; unset($_from); ?>