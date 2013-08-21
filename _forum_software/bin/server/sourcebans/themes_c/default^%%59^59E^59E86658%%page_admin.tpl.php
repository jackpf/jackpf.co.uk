<?php /* Smarty version 2.6.20, created on 2010-08-16 12:11:02
         compiled from page_admin.tpl */ ?>
<h3>Please select an option to administer.</h3>
<div id="cpanel">
	<ul>
		<?php if ($this->_tpl_vars['access_admins']): ?>
			<li>
				<a href="index.php?p=admin&amp;c=admins">
				<img src="themes/default/images/admin/admins.png" alt="Admin Settings" border="0" /><br />
				Admin Settings
		  		</a>
			</li>
		<?php endif; ?>
		<?php if ($this->_tpl_vars['access_servers']): ?>
			<li>
				<a href="index.php?p=admin&amp;c=servers">
				<img src="themes/default/images/admin/servers.png" alt="Server Admin" border="0" /><br />
				Server Settings
		  		</a>
			</li>
		<?php endif; ?>
		<?php if ($this->_tpl_vars['access_bans']): ?>
			<li>
				<a href="index.php?p=admin&amp;c=bans">
				<img src="themes/default/images/admin/bans.png" alt="Edit Bans" border="0" /><br />
				Bans
		  		</a>
			</li>
		<?php endif; ?>
		<?php if ($this->_tpl_vars['access_groups']): ?>
			<li>
				<a href="index.php?p=admin&amp;c=groups">
				<img src="themes/default/images/admin/groups.png" alt="Edit Groups" border="0" /><br />
				Group Settings
		  		</a>
			</li>
		<?php endif; ?>
		<?php if ($this->_tpl_vars['access_settings']): ?>
			<li>
				<a href="index.php?p=admin&amp;c=settings">
				<img src="themes/default/images/admin/settings.png" alt="SourceBans Settings" border="0" /><br />
				Webpanel Settings
		  		</a> 
			</li>
		<?php endif; ?>
		<?php if ($this->_tpl_vars['access_mods']): ?>
			<li>
				<a href="index.php?p=admin&amp;c=mods">
				<img src="themes/default/images/admin/mods.png" alt="Mods" border="0" /><br />
				Manage Mods
		  		</a>
			</li>
		<?php endif; ?>
	</ul>
</div>	
<br />

<table width="100%" border="0" cellpadding="3" cellspacing="0">
	<tr>
		<td width="33%" align="center"><h3>Version Information</h3></td>
		<td width="33%" align="center"><h3>Admin Information</h3></td>
		<td width="33%" align="center"><h3>Ban Information</h3></td>
	</tr>
	<tr>
		<td>Latest release: <strong id='relver'>Please Wait...</strong></td>
		<td>Total admins: <strong><?php echo $this->_tpl_vars['total_admins']; ?>
</strong></td>
		<td>Total bans: <strong><?php echo $this->_tpl_vars['total_bans']; ?>
</strong></td>
	</tr>
	<tr>
		<td>
			<?php if ($this->_tpl_vars['sb_svn']): ?>
				Latest SVN: <strong id='svnrev'>Please Wait...</strong>
			<?php endif; ?>		
		</td>
		<td>&nbsp;</td>
		<td>Connection blocks: <strong><?php echo $this->_tpl_vars['total_blocks']; ?>
</strong></td>
	</tr>
	<tr>
		<td id='versionmsg'>Please Wait...</td>
		<td> <strong> </strong></td>
		<td>Total demo size: <strong><?php echo $this->_tpl_vars['demosize']; ?>
</td>
	</tr>
	<tr>
		<td width="33%" align="center"><h3>Server Information</h3></td>
		<td width="33%" align="center"><h3>Protest Information</h3></td>
		<td width="33%" align="center"><h3>Submission Information</h3></td>
	</tr>
	<tr>
		<td>Total Servers: <strong><?php echo $this->_tpl_vars['total_servers']; ?>
</strong></td>
		<td>Total protests: <strong><?php echo $this->_tpl_vars['total_protests']; ?>
</strong></td>
		<td>Total submissions: <strong><?php echo $this->_tpl_vars['total_submissions']; ?>
</strong></td>
	</tr>
	<tr>
		<td>&nbsp;</td>
		<td>Archived protests: <strong><?php echo $this->_tpl_vars['archived_protests']; ?>
</strong></td>
		<td>Archived submissions: <strong><?php echo $this->_tpl_vars['archived_submissions']; ?>
</strong></td>
	</tr>
	<tr>
		<td>&nbsp;</td>
		<td>&nbsp;</td>
		<td>&nbsp;</td>
	</tr>
</table>
<script type="text/javascript">xajax_CheckVersion();</script>