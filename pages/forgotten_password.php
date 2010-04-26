<?php
/**
 * Generate and send a new password if the user forgot it
 *
 * @author Florent Captier <florent@captier.org>
 * @filesource
 */

require_once('../lib/config.inc.php');
require_once($_CONF['libRoot'].'Page.class.php');
require_once($_CONF['libRoot'].'Notification.class.php');

if(isset($_SESSION['user'])) {header('Location: '.$_CONF['wwwRoot']); exit;}

if(isset($_POST['user'])) {
	if($id_user = User::checkData(array('login' , 'mail') , array($_POST['user'] , $_POST['mail']))) {
		if($new_pass = Password::generateRandom()) {
			if(User::changePassword($id_user , $new_pass)) {
				if(Notification::sendPassword($id_user , $new_pass)) {
					$success = 'Un nouveau mot de passe a été généré et vous a été envoyé par email.';
				} else {
					$error = '
						Erreur d\envoi de l\'email de notification, le nouveau mot de passe est <b>'.$new_pass.'</b><br />
						IMPORTANT : Veillez à changer immédiatement votre mot de passe via le sous-menu "Mes infos" du menu utilisateur qui apparaitra une fois identifié.
					';
				}
			} else {
				$error = 'Erreur de changement de mot de passe, veuillez rééssayer plus tard.';
			}	
		} else {
			$error = 'Erreur de génération du nouveau mot de passe, veuillez rééssayer plus tard.';
		}
	} else {
		$error = 'Les informations saisies n\'ont pas pu être validées.';
	}
}

$content = '
	<form id="formVerif" method="post" class="loginForm">
		<fieldset>
			<legend>Informations du compte</legend>
			<label for="user">Nom d\'utilisateur</label><input type="text" id="user" name="user" tabindex="1" /><br />
			<label for="mail">Mail</label><input type="text" id="mail" name="mail" tabindex="2" /><br />
			<button type="submit" tabindex="3">Valider</button>
			'.((isset($error)) ? '<div class="error">'.$error.'</div>' : '').'
			'.((isset($success)) ? '<div class="success">'.$success.'</div>' : '').'
		</fieldset>
	</form>
';

$page = new Page();
$page->title = "Mot de passe oublié";
$page->add('content' , $content);
$page->send();
?>