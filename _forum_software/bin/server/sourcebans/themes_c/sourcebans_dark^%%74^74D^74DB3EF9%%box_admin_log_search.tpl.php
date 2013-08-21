<?php /* Smarty version 2.6.20, created on 2010-08-18 16:15:35
         compiled from box_admin_log_search.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'sb_button', 'box_admin_log_search.tpl', 49, false),)), $this); ?>
<div align="center">
	<table width="80%" cellpadding="0" class="listtable" cellspacing="0">
		<tr class="sea_open">
			<td width="2%" height="16" class="listtable_top" colspan="3"><b>Advanced Search<b> (Click)</td>
	  	</tr>
	  	<tr>
	  		<td>
	  		<div class="panel">
	  			<table width="100%" cellpadding="0" class="listtable" cellspacing="0">
			    <tr>
					<td class="listtable_1" width="8%" align="center"><input id="admin_" name="search_type" type="radio" value="radiobutton"></td>
			        <td class="listtable_1" width="26%">Admin</td>
			        <td class="listtable_1" width="66%">
						<select id="admin" onmouseup="$('admin_').checked = true" style="border: 1px solid #000000; font-size: 12px; background-color: rgb(215, 215, 215);width: 250px;">
							<?php $_from = ($this->_tpl_vars['admin_list']); if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['admin']):
?>
								<option label="<?php echo $this->_tpl_vars['admin']['user']; ?>
" value="<?php echo $this->_tpl_vars['admin']['aid']; ?>
"><?php echo $this->_tpl_vars['admin']['user']; ?>
</option>
							<?php endforeach; endif; unset($_from); ?>
						</select>    
					</td>
				</tr>
				 <tr>
					<td class="listtable_1" align="center"><input id="message_" name="search_type" type="radio" value="radiobutton"></td>
			        <td class="listtable_1">Message</td>
			        <td class="listtable_1"><input type="text" id="message" value="" onmouseup="$('message_').checked = true" style="border: 1px solid #000000; font-size: 12px; background-color: rgb(215, 215, 215);width: 250px;"></td>
				</tr>
			    <tr>
			        <td align="center" class="listtable_1" ><input id="date_" type="radio" name="search_type" value="radiobutton"></td>
			        <td class="listtable_1" >Date</td>
				    <td class="listtable_1" >
			        	<input type="text" id="day" value="DD" onmouseup="$('date_').checked = true" style="border: 1px solid #000000; font-size: 12px; background-color: rgb(215, 215, 215);width: 25px;">.<input type="text" id="month" value="MM" onmouseup="$('date_').checked = true" style="border: 1px solid #000000; font-size: 12px; background-color: rgb(215, 215, 215);width: 25px;">.<input type="text" id="year" value="YYYY" onmouseup="$('date_').checked = true" style="border: 1px solid #000000; font-size: 12px; background-color: rgb(215, 215, 215);width: 40px;">
						&nbsp;<input type="text" id="fhour" value="00" onmouseup="$('date_').checked = true" style="border: 1px solid #000000; font-size: 12px; background-color: rgb(215, 215, 215);width: 25px;">:<input type="text" id="fminute" value="00" onmouseup="$('date_').checked = true" style="border: 1px solid #000000; font-size: 12px; background-color: rgb(215, 215, 215);width: 25px;">
						-&nbsp;<input type="text" id="thour" value="23" onmouseup="$('date_').checked = true" style="border: 1px solid #000000; font-size: 12px; background-color: rgb(215, 215, 215);width: 25px;">:<input type="text" id="tminute" value="59" onmouseup="$('date_').checked = true" style="border: 1px solid #000000; font-size: 12px; background-color: rgb(215, 215, 215);width: 25px;">
			        </td>
			    </tr>
			    <tr>
			        <td align="center" class="listtable_1" ><input id="type_" type="radio" name="search_type" value="radiobutton"></td>
			        <td class="listtable_1" >Type</td>
			        <td class="listtable_1" >
						<select id="type" onmouseup="$('type_').checked = true" style="border: 1px solid #000000; font-size: 12px; background-color: rgb(215, 215, 215);width: 250px;">
							<option label="Message" value="m">Message</option>
							<option label="Warning" value="w">Warning</option>
							<option label="Error" value="e">Error</option>
						</select>
					</td>
			    </tr>
			    <tr>
				    <td> </td>
				    <td> </td>
			        <td><?php echo smarty_function_sb_button(array('text' => 'Search','onclick' => "search_log();",'class' => 'ok','id' => 'searchbtn','submit' => false), $this);?>
</td>
			    </tr>
			   </table>
			   </div>
		  </td>
		</tr>
	</table>
</div>
<script>InitAccordion('tr.sea_open', 'div.panel', 'mainwrapper');</script>