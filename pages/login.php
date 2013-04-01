<?php
/**
 * Login a user
 *
 * @var string ref GET var which indicate the page to redirect the user to
 *
 * @author Florent Captier <florent@captier.org>
 * @filesource
 */

require_once('../lib/config.inc.php');
require_once($_CONF['libRoot'].'Page.class.php');

if(isset($_SESSION['user'])) {header('Location: '.$_CONF['wwwRoot']); exit;}

$referer = (isset($_GET['ref'])) ? $_GET['ref'] : $_CONF['wwwRoot'];

if(isset($_POST['user'])) {
	try{
		$_SESSION['user'] = User::authenticate($_POST['user'] , $_POST['pass']);
		if(DEBUG >= 1) {trigger_error('[DEBUG1] Authentication succeed for '.$_POST['user'] , E_USER_NOTICE);}
		header('Location: '.$referer);
	} catch(Exception $e) {
		if(DEBUG >= 1) {trigger_error('[DEBUG1] Authentication failed for '.$_POST['user'].' : '.$e->getMessage() , E_USER_NOTICE);}
		$error = 'Authentification échouée, vérifiez votre nom d\'utilisateur ou votre mot de passe.';
	}
}

$login_form = '
	<form id="loginForm" method="post" action="'.$_SERVER['REQUEST_URI'].'" class="loginForm">
		<fieldset>
			<legend>Compte existant</legend>
			<label for="user">Nom d\'utilisateur</label><input type="text" id="user" name="user" tabindex="1" placeholder="prenom.nom" required /><br />
			<label for="pass">Mot de passe</label><input type="password" id="pass" name="pass" tabindex="2" required /><br />
			<a href="forgotten_password.php" title="Mot de passe oublié">J\'ai oublié mon mot de passe</a><br />
			<button type="submit" tabindex="3">Valider</button>
			'.((isset($error)) ? '<div class="error">'.$error.'</div>' : '').'
		</fieldset>
	</form>
';

// Subscription form not enabled for the moment
/*
	<form id="subscriptionForm" method="post" action="'.$_SERVER['REQUEST_URI'].'" class="loginForm">
		<fieldset>
			<legend>Demander un compte</legend>
			<input type="text" id="firstname" name="firstname" value="Prénom" /><br />
			<input type="text" id="lastname" name="lastname" value="Nom" /><br />
			<input type="text" id="mail" name="mail" value="Mail" /><br />
			<button type="submit">Valider</button>
		</fieldset>
	</form>
';
*/

$page = new Page();
$page->title = 'Authentification';
$page->add('content' , $login_form);
$page->send();
?>