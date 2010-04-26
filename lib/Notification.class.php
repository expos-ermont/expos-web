<?php
/**
 * Give a set of functions to send various notifications
 * 
 * @filesource
 * @author Florent Captier <florent@captier.org>
 */

require_once($_CONF['libRoot'].'Db.class.php');
require_once($_CONF['libRoot'].'Mail.class.php');

class Notification {
	
	/**
	 * Send an email to the user to notify him for the creation of his account
	 * 
	 * @param int $id_user ID of the new user
	 * @return bool
	 */
	public static function accountCreation($id_user) {
		global $_CONF;
		$db = new Db();
		
		$query = 'SELECT CONCAT(prenom) AS name, login , mail FROM users WHERE id_user = "'.$id_user.'"';
		$result = $db->query($query);
		$data = $result->fetch_array();
		
		$subject = 'Création de votre compte expos-ermont.com';
		$content = '
			Bonjour '.$data['name'].',<br />
			<br />
			Votre compte sur le site des <a href="'.$_CONF['wwwRoot'].'" title="'.$_CONF['title'].'">Expos d\'Ermont</a> a bien été créé.<br />
			<br />
			Voici votre nom d\'utilisateur : <strong>'.$data['login'].'</strong><br />
			<br />
			N\'oubliez pas de remplir/modifier vos informations personnelles dans le menu qui apparaitra en passant votre souris sur "Bienvenue '.$data['name'].'" une fois authentifié.<br />
			<br />
			Les Expos d\'Ermont
		';
		Mail::quickSend($data['mail'] , $subject , $content);
		return true;
	}
	
	/**
	 * Send an email to a user with his new password
	 * @param int $id_user ID user
	 * @param string $password New password in clear text format
	 * @return bool
	 */
	public static function sendPassword($id_user , $password = null) {
		global $_CONF;
		$db = new Db();
		
		$query = 'SELECT CONCAT(prenom , " " , nom) AS name, login , mail FROM users WHERE id_user = "'.$id_user.'"';
		$result = $db->query($query);
		$data = $result->fetch_array();
		
		$subject = '(Ré)initialisation de votre mot de passe sur expos-ermont.com';
		$content = '
			Bonjour '.$data['name'].',<br />
			<br />
			Votre mot de passe sur le site des <a href="'.$_CONF['wwwRoot'].'" title="'.$_CONF['title'].'">Expos d\'Ermont</a> a été (ré)initialiser.<br />
			<br />
			Votre nouveau mot de passe est : <strong>'.$password.'</strong><br />
			Nous vous rappelons votre nom d\'utilisateur : '.$data['login'].'
			<br />
			<b>IMPORTANT</b> : Veillez à changer votre mot de passe à votre première connection.<br />
			<br />
			Les Expos d\'Ermont
		';
		Mail::quickSend($data['mail'] , $subject , $content);
		return true;
	}
}
?>