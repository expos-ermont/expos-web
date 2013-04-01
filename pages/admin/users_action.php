<?php
/**
 * Edit rights
 *
 * @author Florent Captier <florent@captier.org>
 * @filesource
 */

// TODO Rewrite update & add functions with the User static class function 'update'

require_once('../../lib/config.inc.php');
require_once($_CONF['libRoot'].'Page.class.php');
require_once($_CONF['libRoot'].'Db.class.php');
require_once($_CONF['libRoot'].'Control.class.php');
require_once($_CONF['libRoot'].'Password.class.php');
require_once($_CONF['libRoot'].'Notification.class.php');

Control::accessToPage(__FILE__);

$firstname = (isset($_POST['firstname'])) ? $_POST['firstname'] : null;
$lastname = (isset($_POST['lastname'])) ? $_POST['lastname'] : null;
$mail = (isset($_POST['mail'])) ? $_POST['mail'] : null;
$login = (isset($_POST['login'])) ? $_POST['login'] : null;
$id_rights = (isset($_POST['id_rights'])) ? $_POST['id_rights'] : array();
$genPassword = (isset($_POST['genPassword'])) ? $_POST['genPassword'] : false;

function add () {
	global $firstname , $lastname , $mail , $login , $id_rights;
	$db = new Db();
	
	try {
		// Insert the user into database
		$query = 'INSERT INTO users (nom,prenom,mail,login) VALUES("'.$db->real_escape_string($lastname).'","'.$db->real_escape_string($firstname).'","'.$db->real_escape_string($mail).'","'.$db->real_escape_string($login).'")';
		$db->query($query);
		if($db->affected_rows === 0) {throw new Exception('No row were created.' , 1);}
		// Set the user password
		$password = Password::generateRandom();
		User::changePassword($db->insert_id , $password);
		// Set the granted rights
		Control::addRightsForUser($db->insert_id , $id_rights);
		// Send the notification mail
		Notification::accountCreation($db->insert_id);
		// Send the password
		Notification::sendPassword($db->insert_id , $password);
	} catch(Exception $e) {
		trigger_error('Error while creating a new user : '.$e->getMessage() , E_USER_NOTICE);
		return 'Error while creating the new user';
	}
	
	$db->close();
	return 'User "'.$firstname.' '.$lastname.'" successfully created.';
}

function mod () {
	global $firstname , $lastname , $mail , $login , $id_rights , $genPassword;
	$db = new Db();
	$query = 'SELECT id_user FROM users WHERE id_user = "'.$db->real_escape_string($_GET['id']).'"';
	$result = $db->query($query);
	if($result->num_rows === 0) {return 'There is no user with id#'.$_GET['id'];}
	
	// Set the name of the page
	$query = '
		UPDATE users SET 
			prenom = "'.$db->real_escape_string($firstname).'",
			nom = "'.$db->real_escape_string($lastname).'",
			mail = "'.$db->real_escape_string($mail).'",
			login = "'.$db->real_escape_string($login).'"
		WHERE id_user = "'.$db->real_escape_string($_GET['id']).'"';
	$db->query($query);
	// Set the user password
	if($genPassword) {
			$password = Password::generateRandom();
			User::changePassword($_GET['id'] , $password);
			Notification::sendPassword($_GET['id'] , $password);
	}
	// Set the granted rights
	Control::removeRightsForUser($_GET['id']);
	Control::addRightsForUser($_GET['id'] , $id_rights);
	
	$db->close();
	return 'User "'.$firstname.' '.$lastname.'" successfully updated.';
}

function del () {
	$db = new Db();
	$query = 'DELETE FROM users WHERE id_user = "'.$db->real_escape_string($_GET['id']).'"';
	$db->query($query);
	$db->close();
	return 'User id#'.$_GET['id'].' successfully deleted.';
}

switch($_GET['action']) {
	
	case 'add': 
		$message = add();
		break;
		
	case 'mod': 
		$message = mod();
		break;
		
	case 'del': 
		$message = del();
		break;
	
	default:
		$message = 'Unknown action "'.$_GET['action'].'"';
		break;
}

$content = '
	'.$message.'<br /><br />
	Cliquez <a href="pages/admin/users_list.php" title="Liste">ici</a> si vous n\'êtes pas redirigé automatiquement.
';

header('Refresh: 3;url=users_list.php');

$page = new Page();
$page->add('content' , $content);
$page->send();
?>
