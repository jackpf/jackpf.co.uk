<?php /* Smarty version 2.6.20, created on 2010-08-17 10:08:58
         compiled from page_uploadfile.tpl */ ?>
<html>
<head>
<title>Upload File : SourceBans</title>
<link rel="Shortcut Icon" href="../images/favicon.ico" />
</head>
<body bgcolor="e9e9e9" style="
	background-repeat: repeat-x;
	color: #444;
	font-family: Verdana, Arial, Tahoma, Trebuchet MS, Sans-Serif, Georgia, Courier, Times New Roman, Serif;
	font-size: 11px;
	line-height: 135%;
	margin: 5px;
	padding: 0px;
   ">
<h3><?php echo $this->_tpl_vars['title']; ?>
</h3>


Plese select the file to upload. The file must either be <?php echo $this->_tpl_vars['formats']; ?>
 file format.<br>
<b><?php echo $this->_tpl_vars['message']; ?>
</b>
<form action="" method="POST" id="<?php echo $this->_tpl_vars['form_name']; ?>
" enctype="multipart/form-data">
<input name="upload" value="1" type="hidden">
<input name="<?php echo $this->_tpl_vars['input_name']; ?>
" size="25" class="submit-fields" type="file"> <br />
<button style="background-color: #e9e9e9;
	background-repeat: no-repeat;
	background-position: 2px 50%;
	padding:1px 1px 1px 20px;
	font-weight: bold;
	margin: 0 0.5em;
	background-image: url(../images/save.gif);" type="submit">Save</button>

</form>
</body>
</html>