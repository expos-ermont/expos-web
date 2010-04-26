<?php
/**
 * Change the user information
 * 
 * @author Florent Captier <florent@captier.org>
 * @filesource
 */

require_once('../../lib/config.inc.php');
require_once($_CONF['libRoot'].'Page.class.php');
require_once($_CONF['libRoot'].'Control.class.php');
require_once($_CONF['libRoot'].'Password.class.php');

Control::accessToPage(__FILE__);

$error = '';
$street         = $_POST['street'];
$postalcode     = $_POST['postalcode'];
$city           = $_POST['city'];
$mail           = $_POST['mail'];
$phone          = $_POST['phone'];
$password       = $_POST['password'];
$check_password = $_POST['check_password'];
$public_terms   = (isset($_POST['public_terms'])) ? 1 : 0;
$int_terms      = (isset($_POST['int_terms'])) ? 1 : 0;

$columns = array('street' , 'postalcode' , 'city' , 'mail' , 'phone' , 'public_terms' , 'int_terms');
$values = array($street , $postalcode , $city , $mail , $phone , $public_terms , $int_terms);

if(!empty($password) && !empty($check_password) && $password === $check_password) {
	if($status += Password::checkStrength($password) !== 0) {
		$error .= 'Password non conforme.<br />';
	} else {
		$columns[] = 'password';
		$values[] = Password::hash($password);
	}
}

if(!User::update($_SESSION['user']->id , $columns , $values)) {
	$error .= 'Erreur lors de la mise à jour, veuillez rééssayer ou contacter le webmaster';
}

$message = (empty($error)) ? 'Vos informations ont bien été enregistrées' : $error;

$content = '
	<div class="'.((empty($error)) ? 'success' : 'error').'">'.$message.'</div>
	Cliquez <a href="infos_edit.php" title="Liste">ici</a> si vous n\'ètes pas redirigé automatiquement.
';

$_SESSION['user']->reload();
header('Refresh: 10;url=infos_edit.php');

$page = new Page();
$page->add('content' , $content);
$page->send();
?>