<?php
define(DOCUMENT_ROOT, $_SERVER['DOCUMENT_ROOT']);

function __autoload($class)
{
	include DOCUMENT_ROOT
}
?>