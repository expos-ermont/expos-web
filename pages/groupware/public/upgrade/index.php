<?php

define('ROOT', '../..');
define('PRODUCT_NAME', 'OpenGoo');
define('PRODUCT_URL', 'http://www.opengoo.org');


require_once dirname(__FILE__) . '/include.php';


$upgrader = new ScriptUpgrader(new Output_Html(), lang('upgrade opengoo'), lang('upgrade your opengoo installation'));
$form_data = array_var($_POST, 'form_data');
$upgrade_to = array_var($_GET, 'upgrade_to');
if (isset($upgrade_to)) {
	$form_data = array(
		'upgrade_from' => installed_version(),
		'upgrade_to' => $upgrade_to
	);
}

tpl_assign('upgrader', $upgrader);
tpl_assign('form_data', $form_data);
if(is_array($form_data)) {
	ob_start();
	$upgrader->upgrade(trim(array_var($form_data, 'upgrade_from')), trim(array_var($form_data, 'upgrade_to')));
	$status_messages = explode("\n", trim(ob_get_clean()));

	tpl_assign('status_messages', $status_messages);
} // if

tpl_display(get_template_path('layout'));

?>