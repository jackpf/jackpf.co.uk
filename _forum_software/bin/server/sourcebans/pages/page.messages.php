<?php 

/**

 * =============================================================================

 * Login page

 * 

 * @author SteamFriends Development Team

 * @version 1.0.0

 * @copyright SourceBans (C)2007 SteamFriends.com.  All rights reserved.

 * @package SourceBans

 * @link http://www.sourcebans.net

 * 

 * @version $Id: page.login.php 219 2009-02-24 21:09:11Z peace-maker $

 * =============================================================================

 */



if(!defined("IN_SB")){echo "You should not be here. Only follow links!";die();}

RewritePageTitle("Messages");



global $theme;


$messages = $GLOBALS['db']->GetAll("SELECT * FROM `" . DB_PREFIX . "_Messages`");

$theme->assign('messages', $messages);

$theme->display('page_messages.tpl');

?>