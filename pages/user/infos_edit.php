<?php
/**
 * Allow the user to change his own informations
 * 
 * @author Florent Captier <florent@captier.org>
 * @filesource
 */

require_once('../../lib/config.inc.php');
require_once($_CONF['libRoot'].'Page.class.php');
require_once($_CONF['libRoot'].'Control.class.php');
require_once($_CONF['libRoot'].'Password.class.php');

Control::accessToPage(__FILE__);

$firstname = $_SESSION['user']->firstname;
$lastname = $_SESSION['user']->lastname;
$birthdate = $_SESSION['user']->birthdate;
$street = $_SESSION['user']->street;
$postalcode = $_SESSION['user']->postalcode;
$city = $_SESSION['user']->city;
$mail = $_SESSION['user']->mail;
$phone = $_SESSION['user']->phone;
$public_terms = (int) $_SESSION['user']->public_terms;
$int_terms = (int) $_SESSION['user']->int_terms;

$content = '
	<h2>Editer mes informations</h2>
	<form method="post" action="pages/user/infos_action.php">
		<fieldset>
			<legend>Personnelles</legend>
			<label for="firstname">Prénom</label><input type="text" id="firstname" name="firstname" disabled="disabled" value="'.$firstname.'" required /><br />
			<label for="lastname">Nom</label><input type="text" id="lastname" name="lastname" disabled="disabled" value="'.$lastname.'" required /><br />
			<label for="birthdate">Date de naissance</label><input type="date" id="birthdate" name="birthdate" disabled="disabled" value="'.$birthdate.'" required /><br />
			<label for="street">Rue et n° de rue</label><input type="text" id="street" name="street" value="'.$street.'" required /><br />			
			<label for="postalcode">Code Postal</label><input type="text" id="postalcode" name="postalcode" value="'.$postalcode.'" pattern="[0-9]{5}" required /><br />			
			<label for="city">Ville</label><input type="text" id="city" name="city" value="'.$city.'" required /><br />			
			<label for="mail">Email</label><input type="email" id="mail" name="mail" value="'.$mail.'" required /><br />			
			<label for="phone">Téléphone</label><input type="text" id="phone" name="phone" value="'.$phone.'" placeholder="ex: 0678092617" pattern="[0-9]{10}" required /><br />			
		</fieldset>
		<fieldset>
			<legend>Sécurité & confidentialité</legend>
			<div class="info">Le mot de passe doit être de '.Password::MIN_LENGTH.' caractères minimum, contenir au moins une minuscule, une majuscule, un chiffre et un des signes suivants : '.Password::NON_ALPHA_CHAR.'</div>
			<label for="password">Nouveau mot de passe</label><input type="password" id="password" name="password" /><br />			
			<label for="check_password">Vérification</label><input type="password" id="check_password" name="check_password" /><br />			
			<input type="checkbox" id="public_terms" name="public_terms" '.(($public_terms === 1) ? 'checked="checked"' : '').' /> En cochant cette case j\'accepte que certaines de mes informations soient publiées sur le site et disponible à <b>tous les internautes</b> : Nom, Prénom, Age<br />			
			<input type="checkbox" id="int_terms" name="int_terms" '.(($int_terms === 1) ? 'checked="checked"' : '').' /> En cochant cette case j\'accepte que certaines de mes informations soient publiées sur le site et disponible aux <b>membres du club uniquement</b> : Email, Téléphone, Adresse<br />
		</fieldset>
		<button type="submit">Valider</button>
	</form>
';

$page = new Page();
$page->add('content' , $content);
$page->send();
?>