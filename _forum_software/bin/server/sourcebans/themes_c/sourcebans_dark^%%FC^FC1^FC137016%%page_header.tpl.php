<?php /* Smarty version 2.6.20, created on 2010-08-18 16:15:35
         compiled from page_header.tpl */ ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title><?php if ($this->_tpl_vars['header_title'] != ""): ?><?php echo $this->_tpl_vars['header_title']; ?>
<?php else: ?>SourceBans<?php endif; ?></title>
<link rel="Shortcut Icon" href="./images/favicon.ico" />
<script type="text/javascript" src="./scripts/sourcebans.js"></script>
<link href="themes/<?php echo $this->_tpl_vars['theme_name']; ?>
/css/css.php" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="./scripts/mootools.js"></script>
<script type="text/javascript" src="./scripts/contextMenoo.js"></script>


<?php echo $this->_tpl_vars['tiny_mce_js']; ?>

<?php echo $this->_tpl_vars['xajax_functions']; ?>



</head>
<body>


<div id="mainwrapper">
	<div id="header">
		<div id="head-logo">
    		<a href="index.php">
    			<img src="images/<?php echo $this->_tpl_vars['header_logo']; ?>
" border="0" alt="SourceBans Logo" />
    		</a>
		</div>
		<div id="head-userbox">
	         Welcome <?php echo $this->_tpl_vars['username']; ?>

	         <?php if ($this->_tpl_vars['logged_in']): ?>
	         	(<a href='index.php?p=logout'>Logout</a>)<br /><a href='index.php?p=account'>Your account</a>
	         <?php else: ?>
	          	(<a href='index.php?p=login'>Login</a>)
	         <?php endif; ?>
		</div>
	</div>     
	<div id="tabsWrapper">
        <div id="tabs">
          <ul>
         