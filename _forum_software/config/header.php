<?php
include '_config.lib.php';
$_config_init = _config::get_instance();

$_SERVER['DOCUMENT_ROOT'] = $_config_init->get_config('document_root');
?>