<?php /* Smarty version 2.6.20, created on 2010-08-16 12:43:58
         compiled from page_admin_mods_add.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'help_icon', 'page_admin_mods_add.tpl', 11, false),array('function', 'sb_button', 'page_admin_mods_add.tpl', 43, false),)), $this); ?>
<?php if (! $this->_tpl_vars['permission_add']): ?>
	Access Denied!
<?php else: ?>
	<div id="add-group1">
		<h3>Add Mod</h3>
		For more information or help regarding a certain subject move your mouse over the question mark.<br /><br />
		<table width="90%" style="border-collapse:collapse;" id="group.details" cellpadding="3">
			<tr>
		    	<td valign="top" width="35%">
		    		<div class="rowdesc">
		    			<?php echo smarty_function_help_icon(array('title' => 'Mod Name','message' => "Type the name of the mod you are adding."), $this);?>
Mod Name 
		    		</div>
		    	</td>
		    	<td>
		    		<div align="left">
		    			<input type="hidden" id="fromsub" value="" />
		      			<input type="text" TABINDEX=1 class="submit-fields" id="name" name="name" />
		    		</div>
		    		<div id="name.msg" class="badentry"></div>
		    	</td>
			</tr>
		  	<tr>
		    	<td valign="top">
		    		<div class="rowdesc">
		    			<?php echo smarty_function_help_icon(array('title' => 'Folder Name','message' => "Type the name of this mods folder. For example, Counter-Strike: Source's mod folder is 'cstrike'"), $this);?>
Mod Folder 
		    		</div>
		    	</td>
		    	<td>
			    	<div align="left">
			      		<input type="text" TABINDEX=2 class="submit-fields" id="folder" name="folder" />
			    	</div>
			    	<div id="folder.msg" class="badentry"></div>
				</td>
			</tr>
		  	<tr>
		    	<td valign="top" width="35%">
		    		<div class="rowdesc">
		    			<?php echo smarty_function_help_icon(array('title' => 'Upload Icon','message' => "Click here to upload an icon to associate with this mod."), $this);?>
Upload Icon
		    		</div>
		    	</td>
		    	<td>
		    		<div align="left">
		      			<?php echo smarty_function_sb_button(array('text' => 'Upload Mod Icon','onclick' => "childWindow=open('pages/admin.uploadicon.php','upload','resizable=yes,width=300,height=130');",'class' => 'save','id' => 'upload'), $this);?>

		    		</div>
		    		<div id="icon.msg" style="color:#CC0000;"></div>
		    	</td>
		  	</tr>
		 	<tr>
		    	<td>&nbsp;</td>
		    	<td>		
			     	<?php echo smarty_function_sb_button(array('text' => 'Add Mod','onclick' => "ProcessMod();",'class' => 'ok','id' => 'amod'), $this);?>
&nbsp;
			     	<?php echo smarty_function_sb_button(array('text' => 'Back','onclick' => "history.go(-1)",'class' => 'cancel','id' => 'aback'), $this);?>
      
		      	</td>
		  	</tr>
		</table>
	</div>
<?php endif; ?>