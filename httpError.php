<?php
/**
 * Handle the http return codes
 * 
 * @filesource
 * @author Florent Captier <florent@captier.org>
 */
 
require_once('lib/config.inc.php');
require_once($_CONF['libRoot'].'Page.class.php');
 
$code = $_GET['code'];

$codeToText = array(
	'403' => 'Accès interdit'
);

$page = new Page();
$page->add('content' , $codeToText[$code]);
$page->send();
?>