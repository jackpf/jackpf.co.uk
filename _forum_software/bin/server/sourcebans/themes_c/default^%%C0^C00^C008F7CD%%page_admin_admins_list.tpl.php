<?php /* Smarty version 2.6.20, created on 2010-08-16 12:11:06
         compiled from page_admin_admins_list.tpl */ ?>
<?php if (! $this->_tpl_vars['permission_listadmin']): ?>
	Access Denied
<?php else: ?>

<h3>Admins (<span id="admincount"><?php echo $this->_tpl_vars['admin_count']; ?>
</span>)</h3>
Click on an admin to see more detailed information and actions to perform on them.<br /><br />

<?php  require (TEMPLATES_PATH . "/admin.admins.search.php"); ?>

<div id="banlist-nav"> 
<?php echo $this->_tpl_vars['admin_nav']; ?>

</div>
<div id="banlist">
<table width="99%" cellspacing="0" cellpadding="0" align="center">
	<tr>
		<td width="34%" class="listtable_top"><b>Name</b></td>
		<td width="33%" class="listtable_top"><b>Server Admin Group </b></td>
		<td width="33%" class="listtable_top"><b>Web Admin Group</b></td>
	</tr>
	<?php $_from = ($this->_tpl_vars['admins']); if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['admin']):
?>
		<tr onmouseout="this.className='tbl_out'" onmouseover="this.className='tbl_hover'" class="tbl_out opener">
			<td class="admin-row" style="padding:3px;"><?php echo $this->_tpl_vars['admin']['user']; ?>
 (<a href="./index.php?p=banlist&advSearch=<?php echo $this->_tpl_vars['admin']['aid']; ?>
&advType=admin" title="Show bans"><?php echo $this->_tpl_vars['admin']['bancount']; ?>
 bans</a> | <a href="./index.php?p=banlist&advSearch=<?php echo $this->_tpl_vars['admin']['aid']; ?>
&advType=nodemo" title="Show bans without demo"><?php echo $this->_tpl_vars['admin']['nodemocount']; ?>
 w.d.</a>)</td>
			<td class="admin-row" style="padding:3px;"><?php echo $this->_tpl_vars['admin']['server_group']; ?>
</td>
			<td class="admin-row" style="padding:3px;"><?php echo $this->_tpl_vars['admin']['web_group']; ?>
</td>
 		</tr>
		<tr>
			<td colspan="3">
				<div class="opener" align="center" border="1">
					<table width="100%" cellspacing="0" cellpadding="3" bgcolor="#eaebeb">
						<tr>
			            	<td align="left" colspan="3" class="front-module-header">
								<b>Admin Details of <?php echo $this->_tpl_vars['admin']['user']; ?>
</b>
							</td>
			          	</tr>
			          	<tr align="left">
				            <td width="35%" class="front-module-line"><b>Server Admin Permissions</b></td>
				            <td width="35%" class="front-module-line"><b>Web Admin Permissions</b></td>
				            <td width="30%" valign="top" class="front-module-line"><b>Action</b></td>
			          	</tr>
			          	<tr align="left">
				            <td valign="top"><?php echo $this->_tpl_vars['admin']['server_flag_string']; ?>
</td>
				            <td valign="top"><?php echo $this->_tpl_vars['admin']['web_flag_string']; ?>
</td>
				            <td width="30%" valign="top">
								<div class="ban-edit">
						        	<ul>
						        	<?php if ($this->_tpl_vars['permission_editadmin']): ?>
							        	<li>
							        		<a href="index.php?p=admin&c=admins&o=editdetails&id=<?php echo $this->_tpl_vars['admin']['aid']; ?>
"><img src="images/details.png" border="0" alt="" style="vertical-align:middle"/> Edit Details</a>
							        	</li>
							        	<li>
							        		<a href="index.php?p=admin&c=admins&o=editpermissions&id=<?php echo $this->_tpl_vars['admin']['aid']; ?>
"><img src="images/permissions.png" border="0" alt="" style="vertical-align:middle" /> Edit Permissions</a>
							        	</li>
							        	<li>
							        		<a href="index.php?p=admin&c=admins&o=editservers&id=<?php echo $this->_tpl_vars['admin']['aid']; ?>
"><img src="images/server_small.png" border="0" alt="" style="vertical-align:middle" /> Edit Server Access</a>
							        	</li>
							        	<li>
							        		<a href="index.php?p=admin&c=admins&o=editgroup&id=<?php echo $this->_tpl_vars['admin']['aid']; ?>
"><img src="images/groups.png" border="0" alt="" style="vertical-align:middle" /> Edit Groups</a>
							        	</li>
						        	<?php endif; ?>
						        	<?php if ($this->_tpl_vars['permission_deleteadmin']): ?>
						            	<li>
							        		<a href="#" onclick="RemoveAdmin(<?php echo $this->_tpl_vars['admin']['aid']; ?>
, '<?php echo $this->_tpl_vars['admin']['user']; ?>
');"><img src="images/delete.png" border="0" alt="" style="vertical-align:middle" /> Delete Admin</a>
							        	</li>
						            <?php endif; ?>
						          	</ul>
								</div>
							   	<div class="front-module-line" style="padding:3px;">Immunity Level: <b><?php echo $this->_tpl_vars['admin']['immunity']; ?>
</b></div>
							   	<div class="front-module-line" style="padding:3px;">Last Visited: <b><small><?php echo $this->_tpl_vars['admin']['lastvisit']; ?>
</small></b></div>
							</td>
						</tr>
					</table>
				</div>
			</td>
		</tr>
	<?php endforeach; endif; unset($_from); ?>
</table>
</div>
<script type="text/javascript">InitAccordion('tr.opener', 'div.opener', 'mainwrapper');</script>
<?php endif; ?>